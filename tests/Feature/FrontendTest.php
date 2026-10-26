<?php

namespace IlBronza\UikitTemplate\Tests\Feature;

use IlBronza\Menu\Frontend\FrontendMenu;
use IlBronza\Menu\Frontend\FrontendMenuProvider;
use IlBronza\UikitTemplate\Frontend\FrontendContext;
use IlBronza\UikitTemplate\Frontend\FrontendServiceProvider;
use Orchestra\Testbench\TestCase;

class PublicItems implements FrontendMenuProvider
{
    public function build(FrontendMenu $menu, object $context): void
    {
        $menu->add('Home '.$context->locale, '/'.$context->locale);
    }
}

class FrontendTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [FrontendServiceProvider::class];
    }

    public function test_public_layout_is_isolated_and_escapes_content(): void
    {
        $legacy = new \stdClass;
        $this->app->instance('menu', $legacy);
        config(['menu.frontend.providers' => [PublicItems::class]]);
        $context = new FrontendContext('en', '<Title>');
        $html = view('uikit::frontend.layout', ['frontendContext' => $context])->render();
        $this->assertStringContainsString('lang="en"', $html);
        $this->assertStringContainsString('&lt;Title&gt;', $html);
        $this->assertStringContainsString('Home en', $html);
        $this->assertStringNotContainsString('fetch-menu', $html);
        $this->assertSame($legacy, app('menu'));
    }

    public function test_menu_is_idempotent_and_rebuilds_for_another_context(): void
    {
        $menu = new FrontendMenu(['providers' => [PublicItems::class]]);
        $english = new FrontendContext('en');
        $menu->build($english)->build($english);
        $this->assertCount(1, $menu->items());
        $menu->build(new FrontendContext('it'));
        $this->assertSame('/it', $menu->items()[0]['url']);
    }
}
