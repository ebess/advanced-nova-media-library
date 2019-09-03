<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Helpers\TemporaryDirectory;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @mixin Media
 */
trait HandlesExistingMediaTrait
{
    public function enableExistingMedia(): self
    {
        return $this->withMeta(['existingMedia' => true]);
    }

    private function addExistingMedia(NovaRequest $request, $data, HasMedia $model, string $collection, Collection $medias): Collection
    {
        $addedMediaIds = $medias->pluck('id')->toArray();

        return collect($data)
            ->filter(function ($value) use ($addedMediaIds) {
                return (!($value instanceof UploadedFile)) && !(in_array((int) $value, $addedMediaIds));
            })->map(function ($model_id, int $index) use ($request, $model, $collection) {
                $mediaClass = config('medialibrary.media_model');
                $existingMedia = $mediaClass::find($model_id);

                // Mimic copy behaviour
                // See Spatie\MediaLibrary\Models\Media->copy()
                $temporaryDirectory = TemporaryDirectory::create();
                $temporaryFile = $temporaryDirectory->path($existingMedia->file_name);
                app(Filesystem::class)->copyFromMediaLibrary($existingMedia, $temporaryFile);
                $media = $model->addMedia($temporaryFile)->withCustomProperties($this->customProperties);

                if($this->responsive) {
                    $media->withResponsiveImages();
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
