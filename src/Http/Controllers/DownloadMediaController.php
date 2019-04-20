<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\Models\Media;

class DownloadMediaController extends Controller
{
    public function show(Media $media)
    {
        return $media;
    }
}
