<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Validator;

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
     * @param HasMedia $model
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $attr = $request['__media__'] ?? [];
        $data = $attr[$requestAttribute] ?? [];

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

        return function () use ($request, $data, $attribute, $model) {
            $this->handleMedia($request, $model, $attribute, $data);

            // fill custom properties for existing media
            $this->fillCustomPropertiesFromRequest($request, $model, $attribute);
        };
    }

    protected function handleMedia(NovaRequest $request, $model, $attribute, $data)
    {
        $remainingIds = $this->removeDeletedMedia($data, $model->getMedia($attribute));
        $newIds = $this->addNewMedia($request, $data, $model, $attribute);
        $existingIds = $this->addExistingMedia($request, $data, $model, $attribute, $model->getMedia($attribute));
        $this->setOrder($remainingIds->union($newIds)->union($existingIds)->sortKeys()->all());
    }

    private function setOrder($ids)
    {
        $mediaClass = config('medialibrary.media_model');
        $mediaClass::setNewOrder($ids);
    }

    private function addNewMedia(NovaRequest $request, $data, HasMedia $model, string $collection): Collection
    {
        return collect($data)
            ->filter(function ($value) {
                return $value instanceof UploadedFile;
            })->map(function (UploadedFile $file, int $index) use ($request, $model, $collection) {
                $media = $model->addMedia($file)->withCustomProperties($this->customProperties);

                if($this->responsive) {
                    $media->withResponsiveImages();
                }

                if(!empty($this->customHeaders)) {
                    $media->addCustomHeaders($this->customHeaders);
                }

                if (is_callable($this->setFileNameCallback)) {
                    $media->setFileName(
                        call_user_func($this->setFileNameCallback, $file->getClientOriginalName(), $file->getClientOriginalExtension(), $model)
                    );
                }

                if (is_callable($this->setNameCallback)) {
                    $media->setName(
                        call_user_func($this->setNameCallback, $file->getClientOriginalName(), $model)
                    );
                }

                $media = $media->toMediaCollection($collection);

                // fill custom properties for recently created media
                $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection);

                return $media->getKey();
            });
    }

    private function removeDeletedMedia($data, Collection $medias): Collection
    {
        $remainingIds = collect($data)->filter(function ($value) {
            return !$value instanceof UploadedFile;
        })->map(function ($value) {
            return $value;
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
     * @param HasMedia|HasMediaTrait $resource
     * @param null $attribute
     */
    public function resolve($resource, $attribute = null)
    {
        $collectionName = $attribute ?? $this->attribute;

        if ($collectionName === 'ComputedField') {
            $collectionName = call_user_func($this->computedCallback, $resource);
        }

		$this->value = $resource->getMedia($collectionName)
            ->map(function (\Spatie\MediaLibrary\Models\Media $media) {
                return array_merge($this->serializeMedia($media), ['__media_urls__' => $this->getConversionUrls($media)]);
            });

        if ($collectionName) {
            $this->checkCollectionIsMultiple($resource, $collectionName);
        }
    }

    /**
     * @param HasMedia|HasMediaTrait $resource
     */
    protected function checkCollectionIsMultiple(HasMedia $resource, string $collectionName)
    {
        $resource->registerMediaCollections();
        $isSingle = collect($resource->mediaCollections)
                ->where('name', $collectionName)
                ->first()
                ->singleFile ?? false;

        $this->withMeta(['multiple' => !$isSingle]);
    }

    public function serializeMedia(\Spatie\MediaLibrary\Models\Media $media): array
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
}
