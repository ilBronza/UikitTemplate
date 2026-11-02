<?php

namespace IlBronza\UikitTemplate\Providers;

use IlBronza\UikitTemplate\Helpers\HeadMetadata;
use Illuminate\Support\ServiceProvider;

/** Available to both the standalone public frontend and the standard template. */
class HeadMetadataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(HeadMetadata::class);
        $this->app->alias(HeadMetadata::class, 'uikittemplate.head');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views/head', 'uikittemplate-head');
    }
}
