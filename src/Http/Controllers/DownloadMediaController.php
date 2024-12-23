<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadMediaController extends Controller
{
    public function show($mediaId)
    {
        $model = config('media-library.media_model') ?: Media;
        $file = $model::find($mediaId);

        abort_unless($file->uuid === request()->get('uuid'), 403);

        return $file;
    }
}
