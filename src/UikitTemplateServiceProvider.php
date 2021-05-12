<?php

namespace IlBronza\UikitTemplate;

use Illuminate\Support\ServiceProvider;
use Lavary\Menu\Menu;

class UikitTemplateServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ilbronza');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'uikittemplate');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

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
        $this->mergeConfigFrom(__DIR__.'/../config/uikittemplate.php', 'uikittemplate');

        // Register the service the package provides.
        $this->app->singleton('uikittemplate', function ($app) {
            return new UikitTemplate;
        });

        $this->app->singleton('uikitmenu', function ($app) {
            return new Menu();
        });

        // $this->app->singleton(Menu::class, function ($app) {
        //     return new Menu([config('laravel-menu.settings'), config('laravel-menu.views')] );
        // });

        // $this->app->make('MyNavBar', function ($menu) {
        //     $menu->add('Home');
        //     $menu->add('About', 'about');
        //     $menu->add('Services', 'services');
        //     $menu->add('Contact', 'contact');
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
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/ilbronza'),
        ], 'uikittemplate.views');*/

        // Publishing assets.
        $this->publishes([
            __DIR__.'/../public' => public_path('uikittemplate/'),
            __DIR__.'/../resources/js' => base_path('resources/js'),
            __DIR__.'/../resources/sass' => base_path('resources/sass')
        ], 'uikittemplate.assets');

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ilbronza'),
        ], 'uikittemplate.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
