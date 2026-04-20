<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class DownloadMediaController extends Controller
{
    public function show($mediaId)
    {
        /** @var class-string<Media> $model */
        $model = config('media-library.media_model') ?: Media::class;

        $file = $model::find($mediaId);

        abort_if($file === null, Response::HTTP_NOT_FOUND, 'Media not found.');
        abort_unless($file->uuid === request()->get('uuid'), Response::HTTP_FORBIDDEN);

        return $file;
    }
}
