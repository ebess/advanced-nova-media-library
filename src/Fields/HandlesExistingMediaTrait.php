<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\Support\TemporaryDirectory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
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

    private function addExistingMedia(NovaRequest $request, $data, HasMedia $model, string $collection, Collection $medias): Collection
    {
        $addedMediaIds = $medias->pluck('id')->toArray();

        return collect($data)
            ->filter(function ($value) use ($addedMediaIds) {
                return (! ($value instanceof UploadedFile)) && ! (in_array((int) $value, $addedMediaIds));
            })->map(function ($model_id, int $index) use ($request, $model, $collection) {
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
                $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection);

                // Delete our temp collection
                $temporaryDirectory->delete();

                return $media->getKey();
            });
    }
}
