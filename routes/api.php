<?php

use Workup\AdvancedNovaMediaLibrary\Http\Controllers\DownloadMediaController;
use Workup\AdvancedNovaMediaLibrary\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/download/{media}', [DownloadMediaController::class, 'show']);

Route::get('/media', [MediaController::class, 'index']);
