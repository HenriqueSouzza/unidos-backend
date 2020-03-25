<?php

namespace Modules\AtividadesComplementares\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class AtividadesComplementaresServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('AtividadesComplementares', 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('AtividadesComplementares', 'Config/config.php') => config_path('atividadescomplementares.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('AtividadesComplementares', 'Config/config.php'), 'atividadescomplementares'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/atividadescomplementares');

        $sourcePath = module_path('AtividadesComplementares', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/atividadescomplementares';
        }, \Config::get('view.paths')), [$sourcePath]), 'atividadescomplementares');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/atividadescomplementares');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'atividadescomplementares');
        } else {
            $this->loadTranslationsFrom(module_path('AtividadesComplementares', 'Resources/lang'), 'atividadescomplementares');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('AtividadesComplementares', 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
