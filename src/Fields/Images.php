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

    protected $serializeMediaCallback;

    protected $customPropertiesFields = [];

    private $singleImageRules = [];

    public function thumbnail(string $thumbnail): self
    {
        return $this->withMeta(compact('thumbnail'));
    }

    public function conversion(string $conversion): self
    {
        return $this->withMeta(compact('conversion'));
    }

    public function customPropertiesFields(array $customPropertiesFields): self
    {
        $this->customPropertiesFields = collect($customPropertiesFields);

        return $this->withMeta(compact('customPropertiesFields'));
    }

    public function serializeMediaUsing(callable $serializeMediaUsing): self
    {
        $this->serializeMediaCallback = $serializeMediaUsing;

        return $this;
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
                    $requestAttribute => array_merge(['image'], (array)$this->singleImageRules),
                ])->validate();
            });

        Validator::make($data, $this->rules)->validate();

        $class = get_class($model);
        $class::saved(function ($model) use ($request, $data, $attribute) {
            $this->handleImages($request, $model, $attribute, $data);

            // fill custom properties for existing media
            $this->fillCustomPropertiesFromRequest($request, $model, $attribute);
        });
    }

    protected function handleImages(NovaRequest $request, $model, $attribute, $data)
    {
        $remainingIds = $this->removeDeletedImages($data, $model->getMedia($attribute));
        $newIds = $this->addNewImages($request, $data, $model, $attribute);
        $this->setOrder($remainingIds->union($newIds)->sortKeys()->all());
    }

    private function setOrder($ids)
    {
        $mediaClass = config('medialibrary.media_model');
        $mediaClass::setNewOrder($ids);
    }

    private function addNewImages(NovaRequest $request, $data, HasMedia $model, string $collection): Collection
    {
        return collect($data)
            ->filter(function ($value) {
                return $value instanceof UploadedFile;
            })->map(function (UploadedFile $file, int $index) use ($request, $model, $collection) {
                $media = $model->addMedia($file);

                if(is_callable($this->setFileNameCallback)) {
                    $media->setFileName(
                        call_user_func($this->setFileNameCallback, $file->getClientOriginalName(), $file->getClientOriginalExtension(), $model)
                    );
                }

                $media = $media->toMediaCollection($collection);

                // fill custom properties for recently created media
                $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection);

                return $media->getKey();
            });
    }

    private function fillCustomPropertiesFromRequest(NovaRequest $request, HasMedia $model, string $collection)
    {
        $mediaItems = $model->getMedia($collection);

        collect($request->{$collection})->reject(function ($value) {
            return $value instanceof UploadedFile;
        })->each(function (int $id, int $index) use ($request, $mediaItems, $collection) {
            if (! $media = $mediaItems->where('id', $id)->first()) {
                return;
            }

            $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection);
        });
    }

    private function fillMediaCustomPropertiesFromRequest(NovaRequest $request, $media, int $index, string $collection)
    {
        foreach ($this->customPropertiesFields as $field) {
            $field->fillInto(
                $request,
                $media,
                "custom_properties->{$field->attribute}",
                "{$collection}-custom-properties.{$index}.{$field->attribute}"
            );
        }

        $media->save();
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

                return array_merge($this->serializeMedia($media), ['full_urls' => $urls]);
            });

        if ($data = $this->value->first()) {
            $thumbnailUrl = $data['full_urls'][$this->meta['thumbnail'] ?? 'default'];
            $this->withMeta(compact('thumbnailUrl'));
        }
    }

    public function serializeMedia(\Spatie\MediaLibrary\Models\Media $media): array
    {
        if ($this->serializeMediaCallback) {
            return call_user_func($this->serializeMediaCallback, $media);
        }

        return $media->toArray();
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
}
