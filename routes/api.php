<?php

use Ebess\AdvancedNovaMediaLibrary\Http\Controllers\DownloadMediaController;
use Ebess\AdvancedNovaMediaLibrary\Http\Controllers\MediaController;

Route::get('/download/{media}', [DownloadMediaController::class, 'show']);

Route::get('/media', [MediaController::class, 'index']);
