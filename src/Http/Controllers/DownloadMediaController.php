<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadMediaController extends Controller
{
    public function show(int $mediaId)
    {
        $model = config('media-library.media_model') ?: Media;
        return $model::find($mediaId);
    }
}
