<?php

namespace IlBronza\UikitTemplate;

use IlBronza\Menu\Menu;
use IlBronza\Menu\Navbar;
use IlBronza\UikitTemplate\Fetcher;
use IlBronza\UikitTemplate\Http\Middleware\OnlyAdmin;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use function base_path;

class UikitTemplateServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'uikittemplate');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'uikittemplate');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/uikittemplate.php');

        $this->callAfterResolving(Kernel::class, function () {
            $router = $this->app['router'];
            $router->pushMiddlewareToGroup('web', OnlyAdmin::class);

            // Configure the router directly: older kernels have no priority setters.
            $priority = $router->middlewarePriority;

            if (in_array(OnlyAdmin::class, $priority, true)) {
                return;
            }

            // Resolve the session before this check, but stop before auth and bindings.
            $position = array_search(ShareErrorsFromSession::class, $priority, true);

            if ($position === false) {
                $position = array_search(StartSession::class, $priority, true);
            }

            array_splice($priority, $position === false ? 0 : $position + 1, 0, [OnlyAdmin::class]);
            $router->middlewarePriority = $priority;
        });

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        require_once __DIR__ . '/Helpers/uikittemplateHelper.php';

        $this->mergeConfigFrom(__DIR__.'/../config/uikittemplate.php', 'uikittemplate');

        // Register the service the package provides.
        $this->app->singleton('uikittemplate', function ($app) {
            return new UikitTemplate;
        });

        $this->app->singleton('menu', function ($app) {
            return new Menu();
        });

        $this->app->bind('navbar', function ($app) {
            return new Navbar();
        });

        $this->app->bind('fetcher', function ($app) {
            return new Fetcher();
        });

        // $this->app->singleton(Menu::class, function ($app) {
        //     return new Menu([config('laravel-menu.settings'), config('laravel-menu.views')] );
        // });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['uikittemplate'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/uikittemplate.php' => config_path('uikittemplate.php'),
        ], 'uikittemplate.config');

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views'),
        ], 'uikittemplate.views');

        // Publishing assets.
        $this->publishes([
            __DIR__.'/../public' => public_path('uikittemplate/'),
            __DIR__.'/../resources/assets/js' => base_path('resources/js'),
            __DIR__.'/../resources/assets/css' => base_path('resources/css'),
            __DIR__.'/../resources/assets/less' => base_path('resources/less'),
        ], 'uikittemplate.assets');

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ilbronza'),
        ], 'uikittemplate.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
