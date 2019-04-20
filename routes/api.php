<?php

use Ebess\AdvancedNovaMediaLibrary\Http\Controllers\DownloadMediaController;

Route::get('/download/{media}', [DownloadMediaController::class, 'show']);
