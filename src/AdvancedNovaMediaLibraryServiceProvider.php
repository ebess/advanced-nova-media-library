<?php

namespace Ebess\AdvancedNovaMediaLibrary;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class AdvancedNovaMediaLibraryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('media-lib-images-field', __DIR__.'/../dist/js/field.js');
        });
    }
}
