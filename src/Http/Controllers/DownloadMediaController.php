<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Spatie\MediaLibrary\Models\Media;

class DownloadMediaController extends Controller
{
    public function show(Media $media)
    {
        return $media;
    }
}
