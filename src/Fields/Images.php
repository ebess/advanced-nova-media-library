<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Validator;

class Images extends Field
{
    public $component = 'advanced-media-library-field';

    protected $setFileNameCallback;

    protected $setNameCallback;

    private $singleImageRules = [];

    protected $defaultValidatorRules = ['image'];

    public $meta = ['type' => 'image'];

    public function thumbnail(string $thumbnail): self
    {
        return $this->withMeta(compact('thumbnail'));
    }

    public function conversion(string $conversion): self
    {
        return $this->withMeta(compact('conversion'));
    }

    public function conversionOnView(string $conversionOnView): self
    {
        return $this->withMeta(compact('conversionOnView'));
    }

    public function multiple(): self
    {
        return $this->withMeta(['multiple' => true]);
    }

    public function fullSize(): self
    {
        return $this->withMeta(['fullSize' => true]);
    }

    public function singleImageRules($singleImageRules): self
    {
        $this->singleImageRules = $singleImageRules;

        return $this;
    }

    /**
     * @param HasMedia $model
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $data = request($requestAttribute, []);

        collect($data)
            ->filter(function ($value) {
                return $value instanceof UploadedFile;
            })
            ->each(function ($image) use ($requestAttribute) {
                Validator::make([$requestAttribute => $image], [
                    $requestAttribute => array_merge($this->defaultValidatorRules, (array)$this->singleImageRules),
                ])->validate();
            });

        Validator::make($data, $this->rules)->validate();

        $class = get_class($model);
        $class::saved(function ($model) use ($data, $attribute) {
            $this->handleImages($model, $attribute, $data);
        });
    }

    protected function handleImages($model, $attribute, $data)
    {
        $remainingIds = $this->removeDeletedImages($data, $model->getMedia($attribute));
        $newIds = $this->addNewImages($data, $model, $attribute);
        $this->setOrder($remainingIds->union($newIds)->sortKeys()->all());
    }

    private function setOrder($ids)
    {
        $mediaClass = config('medialibrary.media_model');
        $mediaClass::setNewOrder($ids);
    }

    private function addNewImages($data, HasMedia $model, string $collection): Collection
    {
        return collect($data)
            ->filter(function ($value) {
                return $value instanceof UploadedFile;
            })->map(function (UploadedFile $file) use ($model, $collection) {
                $media = $model->addMedia($file);

                if(is_callable($this->setFileNameCallback)) {
                    $media->setFileName(
                        call_user_func($this->setFileNameCallback, $file->getClientOriginalName(), $file->getClientOriginalExtension(), $model)
                    );
                }

                if(is_callable($this->setNameCallback)) {
                    $media->setName(
                        call_user_func($this->setNameCallback, $file->getClientOriginalName(), $model)
                    );
                }

                return $media
                    ->toMediaCollection($collection)
                    ->getKey();
            });
    }

    private function removeDeletedImages($data, Collection $medias): Collection
    {
        $remainingIds = collect($data)->filter(function ($value) {
            return !$value instanceof UploadedFile;
        })->map(function ($value) {
            return (int)$value;
        });

        $medias->pluck('id')->diff($remainingIds)->each(function ($id) use ($medias) {
            /** @var Media $media */
            if ($media = $medias->where('id', $id)->first()) {
                $media->delete();
            }
        });

        return $remainingIds;
    }

    /**
     * @param HasMedia $resource
     * @param null $attribute
     */
    public function resolve($resource, $attribute = null)
    {
        $this->value = $resource->getMedia($attribute ?? $this->attribute)
            ->map(function (\Spatie\MediaLibrary\Models\Media $media) {
                $conversion = $this->meta['conversion'] ?? null;
                $urls = [
                    'default' => $media->getFullUrl($this->meta['conversion'] ?? ''),
                ];

                if ($thumbnail = $this->meta['thumbnail'] ?? null) {
                    $urls[$thumbnail] = $media->getFullUrl($thumbnail);
                }

                if ($conversionOnView = $this->meta['conversionOnView'] ?? null) {
                    $urls[$conversionOnView] = $media->getFullUrl($conversionOnView);
                }

                return array_merge($media->toArray(), ['full_urls' => $urls]);
            });

        if ($data = $this->value->first()) {
            $thumbnailUrl = $data['full_urls'][$this->meta['thumbnail'] ?? 'default'];
            $this->withMeta(compact('thumbnailUrl'));
        }
    }

    /**
     * Set a filename callable callback
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setFileName($callback) {
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
    public function setName($callback) {
        $this->setNameCallback = $callback;

        return $this;
    }
}
