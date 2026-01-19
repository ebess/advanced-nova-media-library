<?php

use Ebess\AdvancedNovaMediaLibrary\Http\Controllers\DownloadMediaController;
use Ebess\AdvancedNovaMediaLibrary\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/download/{media}', [DownloadMediaController::class, 'show'])->name('advanced-nova-media-library.download');

Route::get('/media', [MediaController::class, 'index'])->name('advanced-nova-media-library');
