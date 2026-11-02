<?php

namespace IlBronza\UikitTemplate\Frontend;

use Illuminate\Support\ServiceProvider;
use IlBronza\Menu\Frontend\FrontendMenu;
use IlBronza\UikitTemplate\Providers\HeadMetadataServiceProvider;

/** Register explicitly in applications which use the public frontend. */
class FrontendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(HeadMetadataServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../../config/uikittemplate.php', 'uikittemplate');

        $this->app->scoped(FrontendMenu::class, function ($app) {
            $locale = $app->make(MenuLocale::class);

            return (new FrontendMenu(config('menu.frontend', [])))->withLanguages(
                $locale->availableLanguages(),
                $locale->current(),
                route('uikittemplate.frontend.menu-locale')
            );
        });
        $this->app->alias(FrontendMenu::class, 'menu.frontend');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'uikit');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'uikit');
        $this->loadRoutesFrom(__DIR__.'/../../routes/frontend.php');
    }
}
