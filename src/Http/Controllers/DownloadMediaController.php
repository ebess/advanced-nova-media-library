<?php

namespace Workup\AdvancedNovaMediaLibrary\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadMediaController extends Controller
{
    public function show($mediaId)
    {
        $model = config('media-library.media_model') ?: Media;
        return $model::find($mediaId);
    }
}
