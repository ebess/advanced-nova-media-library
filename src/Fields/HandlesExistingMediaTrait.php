<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\Support\TemporaryDirectory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @mixin Media
 */
trait HandlesExistingMediaTrait
{
    public function enableExistingMedia(): self
    {
        return $this->withMeta(['existingMedia' => (bool) config('nova-media-library.enable-existing-media')]);
    }

    private function addExistingMedia(NovaRequest $request, $data, HasMedia $model, string $collection, Collection $medias, string $requestAttribute): Collection
    {
        $addedMediaIds = $medias->pluck('id')->toArray();

        return collect($data)
            ->filter(function ($value) use ($addedMediaIds) {
                // New files will come in as UploadedFile objects,
                // whereas Vapor-uploaded files will come in as arrays.
                return (! ($value instanceof UploadedFile)) && (! (is_array($value))) && ! (in_array($value, $addedMediaIds));
            })->map(function ($model_id, int $index) use ($request, $model, $collection, $requestAttribute) {
                $mediaClass = config('media-library.media_model');
                $existingMedia = $mediaClass::find($model_id);

                // Mimic copy behaviour
                // See Spatie\MediaLibrary\Models\Media->copy()
                $temporaryDirectory = TemporaryDirectory::create();
                $temporaryFile = $temporaryDirectory->path($existingMedia->file_name);
                app(Filesystem::class)->copyFromMediaLibrary($existingMedia, $temporaryFile);
                $media = $model->addMedia($temporaryFile)->withCustomProperties($this->customProperties);

                if ($this->responsive) {
                    $media->withResponsiveImages();
                }

                if (! empty($this->customHeaders)) {
                    $media->addCustomHeaders($this->customHeaders);
                }

                $media = $media->toMediaCollection($collection);

                // fill custom properties for recently created media
                $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection, $requestAttribute);

                // Delete our temp collection
                $temporaryDirectory->delete();

                return $media->getKey();
            });
    }
}
