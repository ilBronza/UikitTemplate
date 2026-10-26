<?php

namespace IlBronza\UikitTemplate\Frontend;

use Illuminate\Support\ServiceProvider;
use IlBronza\Menu\Frontend\FrontendMenu;

/** Register explicitly in applications which use the public frontend. */
class FrontendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(FrontendMenu::class, fn () => new FrontendMenu(
            config('menu.frontend', [])
        ));
        $this->app->alias(FrontendMenu::class, 'menu.frontend');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'uikit');
    }
}
