<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

// @TODO Rule contract is deprecated since laravel/framework v10.0, replace with ValidationRule once min version is 10.
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media extends Field
{
    use HandlesCustomPropertiesTrait, HandlesConversionsTrait, HandlesExistingMediaTrait;

    public $component = 'advanced-media-library-field';

    protected $setFileNameCallback;
    protected $setNameCallback;
    protected $serializeMediaCallback;
    protected $responsive = false;

    protected $collectionMediaRules = [];
    protected $singleMediaRules = [];

    protected $customHeaders = [];

    protected $secureUntil;

    protected $defaultValidatorRules = [];

    public $meta = ['type' => 'media'];

    public function serializeMediaUsing(callable $serializeMediaUsing): self
    {
        $this->serializeMediaCallback = $serializeMediaUsing;

        return $this;
    }

    public function fullSize(): self
    {
        return $this->withMeta(['fullSize' => true]);
    }

    public function rules($rules): self
    {
        $this->collectionMediaRules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules;

        return $this;
    }

    public function singleMediaRules($rules): self
    {
        $this->singleMediaRules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules;

        return $this;
    }

    public function customHeaders(array $headers): self
    {
        $this->customHeaders = $headers;

        return $this;
    }

    /**
     * Set the responsive mode, which enables the creation of responsive images on upload
     *
     * @param boolean $responsive
     *
     * @return $this
     */
    public function withResponsiveImages($responsive = true)
    {
        $this->responsive = $responsive;

        return $this;
    }

    /**
     * Set a filename callable callback
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setFileName($callback)
    {
        $this->setFileNameCallback = $callback;

        return $this;
    }

    /**
     * Set a name callable callback
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setName($callback)
    {
        $this->setNameCallback = $callback;

        return $this;
    }

    /**
     * Set the maximum accepted file size for the frontend in kBs
     *
     * @param int $maxSize
     *
     * @return $this
     */
    public function setMaxFileSize(int $maxSize)
    {
        return $this->withMeta(['maxFileSize' => $maxSize]);
    }

    /**
     * Validate the file's type on the frontend side
     * Example values for the array: 'image', 'video', 'image/jpeg'
     *
     * @param array $types
     *
     * @return $this
     */
    public function setAllowedFileTypes(array $types)
    {
        return $this->withMeta(['allowedFileTypes' => $types]);
    }

    /**
     * Set the expiry time for temporary urls.
     *
     * @param Carbon $until
     *
     * @return $this
     */
    public function temporary(Carbon $until)
    {
        $this->secureUntil = $until;

        return $this;
    }

    public function uploadsToVapor(bool $uploadsToVapor = true): self
    {
        return $this->withMeta(['uploadsToVapor' => $uploadsToVapor]);
    }

    /**
     * @param HasMedia $model
     * @param mixed $requestAttribute
     * @param mixed $attribute
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $key = str_replace($attribute, '__media__.'.$attribute, $requestAttribute);
        $data = $request[$key] ?? [];

        if ($attribute === 'ComputedField') {
            $attribute = call_user_func($this->computedCallback, $model);
        }

        collect($data)
            ->filter(function ($value) {
                return $value instanceof UploadedFile;
            })
            ->each(function ($media) use ($request, $requestAttribute) {
                $requestToValidateSingleMedia = array_merge($request->toArray(), [
                    $requestAttribute => $media,
                ]);

                Validator::make($requestToValidateSingleMedia, [
                    $requestAttribute => array_merge($this->defaultValidatorRules, (array)$this->singleMediaRules),
                ])->validate();
            });

        $requestToValidateCollectionMedia = array_merge($request->toArray(), [
            $requestAttribute => $data,
        ]);

        Validator::make($requestToValidateCollectionMedia, [$requestAttribute => $this->collectionMediaRules])
            ->validate();

        return function () use ($request, $data, $attribute, $model, $requestAttribute) {
            $this->handleMedia($request, $model, $attribute, $data, $requestAttribute);

            // fill custom properties for existing media
            $this->fillCustomPropertiesFromRequest($request, $requestAttribute, $model, $attribute);
        };
    }

    protected function handleMedia(NovaRequest $request, $model, $attribute, $data, $requestAttribute)
    {
        $remainingIds = $this->removeDeletedMedia($data, $model->getMedia($attribute));
        $newIds = $this->addNewMedia($request, $data, $model, $attribute, $requestAttribute);
        $existingIds = $this->addExistingMedia($request, $data, $model, $attribute, $model->getMedia($attribute), $requestAttribute);
        $this->setOrder($remainingIds->union($newIds)->union($existingIds)->sortKeys()->all());
    }

    private function setOrder($ids)
    {
        $mediaClass = config('media-library.media_model');
        $mediaClass::setNewOrder($ids);
    }

    private function addNewMedia(NovaRequest $request, $data, HasMedia $model, string $collection, string $requestAttribute): Collection
    {

        return collect($data)
            ->filter(function ($value) {
                // New files will come in as UploadedFile objects,
                // whereas Vapor-uploaded files will come in as arrays.
                return $value instanceof UploadedFile || is_array($value);
            })->map(function ($file, int $index) use ($request, $model, $collection, $requestAttribute) {
                if ($file instanceof UploadedFile) {
                    $media = $model->addMedia($file)->withCustomProperties($this->customProperties);

                    $fileName = $file->getClientOriginalName();
                    $fileExtension = $file->getClientOriginalExtension();

                } else {
                    $media = $this->makeMediaFromVaporUpload($file, $model);

                    $fileName = $file['file_name'];
                    $fileExtension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                }

                if ($this->responsive) {
                    $media->withResponsiveImages();
                }

                if (! empty($this->customHeaders)) {
                    $media->addCustomHeaders($this->customHeaders);
                }

                if (is_callable($this->setFileNameCallback)) {
                    $media->setFileName(
                        call_user_func($this->setFileNameCallback, $fileName, $fileExtension, $model)
                    );
                }

                if (is_callable($this->setNameCallback)) {
                    $media->setName(
                        call_user_func($this->setNameCallback, $fileName, $model)
                    );
                }

                $media = $media->toMediaCollection($collection);

                // fill custom properties for recently created media
                $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection, $requestAttribute);

                return $media->getKey();
            });
    }

    private function removeDeletedMedia($data, Collection $medias): Collection
    {
        $remainingIds = collect($data)->filter(function ($value) {
            // New files will come in as UploadedFile objects,
            // whereas Vapor-uploaded files will come in as arrays.
            return !$value instanceof UploadedFile
                && !is_array($value);
        });

        $medias->pluck('id')->diff($remainingIds)->each(function ($id) use ($medias) {
            /** @var Media $media */
            if ($media = $medias->where('id', $id)->first()) {
                $media->delete();
            }
        });

        return $remainingIds->intersect($medias->pluck('id'));
    }

    /**
     * @param HasMedia|InteractsWithMedia $resource
     * @param null $attribute
     */
    public function resolve($resource, $attribute = null)
    {
        $collectionName = $attribute ?? $this->attribute;

        if ($collectionName === 'ComputedField') {
            $collectionName = call_user_func($this->computedCallback, $resource);
        }

        $this->value = $resource->getMedia($collectionName)
            ->map(function (\Spatie\MediaLibrary\MediaCollections\Models\Media $media) {
                return array_merge($this->serializeMedia($media), ['__media_urls__' => $this->getMediaUrls($media)]);
            })->values();

        if ($collectionName) {
            $this->checkCollectionIsMultiple($resource, $collectionName);
        }
    }

    /**
     * Get the urls for the given media.
     *
     * @return array
     */
    public function getMediaUrls($media)
    {
        if (isset($this->secureUntil) && $this->secureUntil instanceof Carbon) {
            return $this->getTemporaryConversionUrls($media);
        }

        return $this->getConversionUrls($media);
    }

    /**
     * @param HasMedia|InteractsWithMedia $resource
     */
    protected function checkCollectionIsMultiple(HasMedia $resource, string $collectionName)
    {
        $resource->registerMediaCollections();
        $isSingle = collect($resource->mediaCollections)
            ->where('name', $collectionName)
            ->first()
            ->singleFile ?? false;

        $this->withMeta(['multiple' => ! $isSingle]);
    }

    public function serializeMedia(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): array
    {
        if ($this->serializeMediaCallback) {
            return call_user_func($this->serializeMediaCallback, $media);
        }

        return $media->toArray();
    }

    /**
     * @deprecated not needed, field recognizes single/multi file media by itself
     */
    public function multiple(): self
    {
        return $this;
    }

    /**
     * @deprecated
     * @see conversionOnIndexView
     */
    public function thumbnail(string $conversionOnIndexView): self
    {
        return $this->withMeta(compact('conversionOnIndexView'));
    }

    /**
     * @deprecated
     * @see conversionOnPreview
     */
    public function conversion(string $conversionOnPreview): self
    {
        return $this->withMeta(compact('conversionOnPreview'));
    }

    /**
     * @deprecated
     * @see conversionOnDetailView
     */
    public function conversionOnView(string $conversionOnDetailView): self
    {
        return $this->withMeta(compact('conversionOnDetailView'));
    }

    /**
     * This creates a Media object from a previously, client-side, uploaded file.
     * The file is uploaded using a pre-signed S3 URL, via Vapor.store.
     * This method will use addMediaFromUrl(), passing it the
     * temporary location of the file.
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
     */
    private function makeMediaFromVaporUpload(array $file, HasMedia $model): FileAdder
    {
        $disk = config('filesystems.default');

        $disk = config('filesystems.disks.' . $disk . 'driver') === 's3' ? $disk : 's3';

        $url = Storage::disk($disk)->temporaryUrl($file['key'], Carbon::now()->addHour());

        return $model->addMediaFromUrl($url)
            ->usingFilename($file['file_name'])
            ->withCustomProperties($this->customProperties);
    }
}
