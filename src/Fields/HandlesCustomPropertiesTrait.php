<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * @mixin Media
 */
trait HandlesCustomPropertiesTrait
{
    protected $customPropertiesFields = [];
    protected $customProperties = [];

    public function customPropertiesFields(array $customPropertiesFields): self
    {
        $this->customPropertiesFields = collect($customPropertiesFields);

        return $this->withMeta(compact('customPropertiesFields'));
    }

    public function customProperties(array $customProperties): self
    {
        $this->customProperties = $customProperties;

        return $this;
    }

    /**
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute  The form attribute of the media field.
     * @param  \Spatie\MediaLibrary\HasMedia  $model  The model which has associated media.
     * @param  string  $collection  The selected media collection.
     */
    private function fillCustomPropertiesFromRequest(NovaRequest $request, string $requestAttribute, HasMedia $model, string $collection): void
    {
        // If we are dealing with nested resources or multiple panels, media fields are prefixed.
        $key = str_replace($collection, '__media__.'.$collection, $requestAttribute);

        $mediaItems = $model->getMedia($collection);
        $items = $request->input($key, []);

        // do not handle files as custom properties on files are not supported yet
        if ($items instanceof FileBag) {
            return;
        }

        collect($items)
            ->reject(function ($value) {
                return $value instanceof UploadedFile || $value instanceof FileBag;
            })
            ->each(function ($id, int $index) use ($request, $mediaItems, $collection, $requestAttribute) {
                if (! $media = $mediaItems->where('id', $id)->first()) {
                    return;
                }

                $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection, $requestAttribute);
            });
    }

    /**
     * Fills custom properties for a given Media model from the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Spatie\MediaLibrary\MediaCollections\Models\Media  $media  The Media model with custom properties.
     * @param  int  $index  The file's index in the corresponding Media collection, to retrieve its custom properties from the request.
     * @param  string  $collection  The selected media collection.
     * @param  string  $requestAttribute  The form attribute of the media field.
     */
    private function fillMediaCustomPropertiesFromRequest(NovaRequest $request, Media $media, int $index, string $collection, string $requestAttribute): void
    {
        // prevent overriding the custom properties set by other processes like generating conversions
        $media->refresh();

        /** @var Field $field */
        foreach ($this->customPropertiesFields as $field) {
            // If we are dealing with nested resources or multiple panels, custom property fields are prefixed.
            $key = str_replace($collection, '__media-custom-properties__.'.$collection, $requestAttribute);
            $targetAttribute = "custom_properties->{$field->attribute}";
            $requestAttribute = "{$key}.{$index}.{$field->attribute}";

            $field->fillInto($request, $media, $targetAttribute, $requestAttribute);
        }

        $media->save();
    }
}
