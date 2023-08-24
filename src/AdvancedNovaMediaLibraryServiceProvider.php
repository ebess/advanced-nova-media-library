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
        $this->publishes([
            __DIR__ . '/config/nova-media-library.php' => config_path('nova-media-library.php'),
        ], 'nova-media-library');

        $this->app->booted(function () {
            $this->routes();
        });

        $this->loadTranslations();

        Nova::serving(function (ServingNova $event) {
            $this->bootTranslations();
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

    /**
     * Load package translation resources.
     *
     * @return void
     */
    protected function loadTranslations()
    {
        $this->loadJSONTranslationsFrom(__DIR__ . '/../resources/lang');
        $this->loadJSONTranslationsFrom(lang_path('vendor/advanced-nova-media-library'));
    }

    /**
     * Bootstraps current application locale translations to Nova.
     *
     * @return void
     */
    protected function bootTranslations()
    {
        $locale = $this->app->getLocale();

        Nova::translations(__DIR__ . "/../resources/lang/{$locale}.json");
        Nova::translations(lang_path("vendor/advanced-nova-media-library/$locale.json"));
    }
}
