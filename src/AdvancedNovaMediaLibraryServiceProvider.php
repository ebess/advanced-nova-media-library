<?php

namespace Ebess\AdvancedNovaMediaLibrary;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class AdvancedNovaMediaLibraryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('media-lib-images-field', __DIR__.'/../dist/js/field.js');
        });
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/ebess/advanced-nova-media-library')
            ->group(__DIR__.'/../routes/api.php');
    }
}
