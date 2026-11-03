<?php

namespace IlBronza\UikitTemplate\Tests\Feature;

use DOMDocument;
use DOMXPath;
use IlBronza\UikitTemplate\Frontend\FrontendContext;
use IlBronza\UikitTemplate\Frontend\FrontendServiceProvider;
use IlBronza\UikitTemplate\Helpers\HeadMetadata;
use IlBronza\UikitTemplate\UikitTemplateServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class HeadMetadataTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [UikitTemplateServiceProvider::class, FrontendServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('h', 32)));
        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.base_locale', 'it');
        $app['config']->set('uikittemplate.availableLanguages', ['it', 'en', 'fr']);
    }

    protected function defineRoutes($router)
    {
        $router->get('/{path?}', function () {
            if ($this->app->bound('head.test.configure')) {
                ($this->app->make('head.test.configure'))(app('uikittemplate')->head());
            }

            return view('uikit::frontend.head', [
                'frontendContext' => $this->app->bound('head.test.context')
                    ? $this->app->make('head.test.context') : new FrontendContext('en', 'Page'),
            ]);
        })->where('path', '.*');
    }

    private function document(string $html): DOMXPath
    {
        $document = new DOMDocument;
        @$document->loadHTML($html);

        return new DOMXPath($document);
    }

    private function canonical(DOMXPath $document): string
    {
        $links = $document->query('//link[@rel="canonical"]');
        $this->assertCount(1, $links);

        return $links->item(0)->getAttribute('href');
    }

    private function alternates(DOMXPath $document): array
    {
        $links = [];

        foreach ($document->query('//link[@rel="alternate"]') as $link) {
            $links[$link->getAttribute('hreflang')] = $link->getAttribute('href');
        }

        return $links;
    }

    public function test_canonical_uses_current_absolute_path_without_query_and_preserves_ids_scheme_and_port(): void
    {
        $response = $this->get('https://example.test:8443/en/products/42?utm_source=mail&page=2');
        $response->assertOk();
        $document = $this->document($response->getContent());

        $this->assertSame('https://example.test:8443/en/products/42', $this->canonical($document));
        $this->assertSame([
            'it' => 'https://example.test:8443/it/products/42',
            'en' => 'https://example.test:8443/en/products/42',
            'fr' => 'https://example.test:8443/fr/products/42',
            'x-default' => 'https://example.test:8443/it/products/42',
        ], $this->alternates($document));
    }

    #[DataProvider('paths')]
    public function test_language_prefix_is_replaced_only_as_a_complete_first_path_segment(string $path, string $italian): void
    {
        // Laravel's test URL generator trims trailing slashes on relative URLs.
        $response = $this->get('http://localhost'.$path.'?filter=1');
        $response->assertOk();
        $document = $this->document($response->getContent());
        $this->assertSame('http://localhost'.$path, $this->canonical($document));
        $this->assertSame('http://localhost'.$italian, $this->alternates($document)['it']);
    }

    public static function paths(): array
    {
        return [
            ['/', '/it/'],
            ['/catalogo', '/it/catalogo'],
            ['/en', '/it'],
            ['/en/', '/it/'],
            ['/en/catalogo/', '/it/catalogo/'],
            ['/english/story', '/it/english/story'],
            ['/news/en/story', '/it/news/en/story'],
        ];
    }

    public function test_explicit_canonical_drives_automatic_links_and_its_query_is_preserved(): void
    {
        config(['uikittemplate.head.canonical' => 'https://public.test/en/catalogue?edition=2026']);
        $response = $this->get('/internal/page?tracking=1');
        $response->assertOk();
        $document = $this->document($response->getContent());

        $this->assertSame('https://public.test/en/catalogue?edition=2026', $this->canonical($document));
        $this->assertSame('https://public.test/it/catalogue?edition=2026', $this->alternates($document)['it']);
    }

    public function test_controller_values_override_context_and_configuration_without_duplicate_tags(): void
    {
        config([
            'uikittemplate.head.canonical' => 'https://config.test/page',
            'uikittemplate.head.hrefLinks' => ['de' => 'https://config.test/de/page'],
            'uikittemplate.head.themeColor' => '#000000',
            'uikittemplate.head.favicon' => '/config.ico',
            'uikittemplate.head.touchIcon' => ['path' => '/touch.png', 'sizes' => '180x180'],
        ]);
        $this->app->instance('head.test.context', new FrontendContext('en', 'Title', [
            'canonical' => 'https://context.test/page', 'description' => 'Context description', 'og:title' => 'Context title',
        ], languageUrls: ['it' => '/context-page', 'en' => '/en/context-page']));
        $this->app->instance('head.test.configure', static function (HeadMetadata $head) {
            $head->setCanonical('https://controller.test/article')
                ->setHrefLinks(['it' => 'https://controller.test/articolo', 'en' => 'https://controller.test/article'])
                ->setFavicon('/controller.svg')->setThemeColor('#ffffff')
                ->setOpenGraph(['title' => 'Controller <title>']);
        });

        $response = $this->get('/page');
        $response->assertOk();
        $document = $this->document($response->getContent());
        $this->assertSame('https://controller.test/article', $this->canonical($document));
        $this->assertSame([
            'it' => 'https://controller.test/articolo',
            'en' => 'https://controller.test/article',
            'x-default' => 'https://controller.test/articolo',
        ], $this->alternates($document));
        $this->assertCount(1, $document->query('//link[@rel="icon"]'));
        $this->assertSame('image/svg+xml', $document->query('//link[@rel="icon"]')->item(0)->getAttribute('type'));
        $this->assertSame('/controller.svg', $document->query('//link[@rel="icon"]')->item(0)->getAttribute('href'));
        $this->assertSame('180x180', $document->query('//link[@rel="apple-touch-icon"]')->item(0)->getAttribute('sizes'));
        $this->assertSame('#ffffff', $document->query('//meta[@name="theme-color"]')->item(0)->getAttribute('content'));
        $this->assertCount(1, $document->query('//meta[@property="og:title"]'));
        $this->assertSame('Controller <title>', $document->query('//meta[@property="og:title"]')->item(0)->getAttribute('content'));
        $this->assertSame('Context description', $document->query('//meta[@name="description"]')->item(0)->getAttribute('content'));
    }

    public function test_context_urls_override_config_and_an_empty_explicit_list_disables_alternates(): void
    {
        config(['uikittemplate.head.canonical' => 'https://config.test/page']);
        $context = new FrontendContext('en', meta: ['canonical' => 'https://context.test/page'], languageUrls: [
            'it' => 'https://context.test/articolo', 'en' => 'https://context.test/article',
        ]);
        $head = app(HeadMetadata::class)->forContext($context);
        $this->assertSame('https://context.test/page', $head->getCanonical());
        $this->assertSame($context->languageUrls + [
            'x-default' => $context->languageUrls['it'],
        ], $head->getHrefLinks());
        $this->assertSame('https://config.test/page', app(HeadMetadata::class)->getCanonical());

        $this->app->instance('head.test.context', $context);
        $this->app->instance('head.test.configure', static fn (HeadMetadata $head) => $head->setHrefLinks([]));
        $response = $this->get('/page');
        $response->assertOk();
        $this->assertSame([], $this->alternates($this->document($response->getContent())));
    }

    public function test_configured_icons_theme_and_alternate_urls_are_used_and_legacy_favicon_keeps_its_type(): void
    {
        config([
            'uikittemplate.favicon.path' => '/legacy.png',
            'uikittemplate.head.touchIcon' => '/touch.png',
            'uikittemplate.head.themeColor' => '#123456',
            'uikittemplate.head.hrefLinks' => ['en_US' => '/en-us/page', 'x-default' => '/'],
        ]);
        $response = $this->get('/page');
        $response->assertOk();
        $document = $this->document($response->getContent());
        $this->assertSame('/legacy.png', $document->query('//link[@rel="icon"]')->item(0)->getAttribute('href'));
        $this->assertSame('image/png', $document->query('//link[@rel="icon"]')->item(0)->getAttribute('type'));
        $this->assertSame('/touch.png', $document->query('//link[@rel="apple-touch-icon"]')->item(0)->getAttribute('href'));
        $this->assertSame('#123456', $document->query('//meta[@name="theme-color"]')->item(0)->getAttribute('content'));
        $this->assertSame(['en-US' => 'http://localhost/en-us/page', 'x-default' => 'http://localhost'], $this->alternates($document));
    }

    public function test_single_language_overrides_keep_automatic_links_in_sync_with_later_canonical_changes(): void
    {
        $this->app->instance('head.test.configure', static function (HeadMetadata $head) {
            $head->setHrefLink('en', 'https://public.test/en/custom-slug')
                ->setHrefLink('fr', null)
                ->setHrefLink('x-default', '/language-selector')
                ->setCanonical('https://public.test/en/article');
        });

        $response = $this->get('/internal/path?tracking=1');
        $response->assertOk();
        $this->assertSame([
            'it' => 'https://public.test/it/article',
            'en' => 'https://public.test/en/custom-slug',
            'x-default' => 'http://localhost/language-selector',
        ], $this->alternates($this->document($response->getContent())));
    }

    public function test_single_language_overrides_preserve_later_context_urls_and_bulk_setter_resets_them(): void
    {
        $head = app(HeadMetadata::class)->setHrefLink('en_US', '/special')
            ->setHrefLink('fr', null)->forContext(new FrontendContext('en', languageUrls: [
                'it' => '/articolo', 'en-US' => '/en-us/article', 'fr' => '/fr/article',
            ]));

        $this->assertSame([
            'it' => 'http://localhost/articolo',
            'en-US' => 'http://localhost/special',
            'x-default' => 'http://localhost/articolo',
        ], $head->getHrefLinks());
        $this->assertSame('http://localhost/special', $head->getHrefLink('en_US'));
        $this->assertNull($head->getHrefLink('fr'));

        $head->setHrefLinks(['fr' => '/nouvel-article']);
        $this->assertSame([
            'fr' => 'http://localhost/nouvel-article',
            'x-default' => 'http://localhost/nouvel-article',
        ], $head->getHrefLinks());
        $head->setHrefLinks([]);
        $this->assertSame([], $head->getHrefLinks());
    }

    public function test_automatic_links_require_multiple_distinct_languages(): void
    {
        foreach ([[], ['it'], ['it', 'it']] as $languages) {
            config(['uikittemplate.availableLanguages' => $languages]);
            $this->assertSame([], app(HeadMetadata::class)->getHrefLinks());
        }
    }

    public function test_x_default_uses_base_locale_and_can_be_removed_or_overridden(): void
    {
        $response = $this->get('https://example.test/en/page');
        $response->assertOk();
        $this->assertSame('https://example.test/it/page', $this->alternates($this->document($response->getContent()))['x-default']);

        config(['uikittemplate.head.xDefaultLocale' => 'en']);
        $this->assertSame('https://example.test/en/page', app(HeadMetadata::class)->getHrefLinks()['x-default']);

        $head = app(HeadMetadata::class)->setHrefLink('x-default', null);
        $this->assertArrayNotHasKey('x-default', $head->getHrefLinks());

        $head->setHrefLink('x-default', 'https://example.test/choose-language');
        $this->assertSame('https://example.test/choose-language', $head->getHrefLinks()['x-default']);
    }

    public function test_language_prefix_is_added_after_an_application_base_path(): void
    {
        $request = Request::create('https://example.test/shop/index.php/en/product?tracking=1', 'GET', server: [
            'SCRIPT_NAME' => '/shop/index.php', 'SCRIPT_FILENAME' => '/var/www/shop/index.php',
            'PHP_SELF' => '/shop/index.php/en/product',
        ]);
        $this->app->instance('request', $request);
        $head = new HeadMetadata;
        $this->assertSame('/shop/index.php', $request->getBaseUrl());
        $this->assertSame('https://example.test/shop/index.php/en/product', $head->getCanonical());
        $this->assertSame('https://example.test/shop/index.php/it/product', $head->getHrefLinks()['it']);
    }

    public function test_open_graph_supports_repeated_properties_and_groups_image_attributes(): void
    {
        $this->app->instance('head.test.configure', static function (HeadMetadata $head) {
            $head->setOpenGraph([
                'og:title' => 'Title', 'og:type' => 'article',
                'og:image' => [
                    ['url' => 'https://example.test/first.jpg', 'width' => 1200, 'alt' => 'First image'],
                    ['url' => 'https://example.test/second.jpg', 'alt' => 'Second image'],
                ],
            ])->setOpenGraphProperty('article:tag', ['Design', 'Architecture']);
        });
        $response = $this->get('/page');
        $response->assertOk();
        $document = $this->document($response->getContent());
        $this->assertCount(2, $document->query('//meta[@property="article:tag"]'));
        $this->assertSame('Title', $document->query('//meta[@property="og:title"]')->item(0)->getAttribute('content'));
        $tags = $document->query('//meta[starts-with(@property, "og:image")]');
        $this->assertSame([
            ['og:image', 'https://example.test/first.jpg'], ['og:image:width', '1200'], ['og:image:alt', 'First image'],
            ['og:image', 'https://example.test/second.jpg'], ['og:image:alt', 'Second image'],
        ], array_map(static fn ($tag) => [$tag->getAttribute('property'), $tag->getAttribute('content')], iterator_to_array($tags)));
    }

    public function test_flat_crud_meta_and_json_ld_are_rendered_safely_without_configured_open_graph_defaults(): void
    {
        config(['uikittemplate.head.openGraph' => ['og:image' => '/ignored.jpg'], 'uikittemplate.head.jsonLd' => [['ignored' => true]]]);
        $text = '</script><script>alert("injection")</script> & example';
        $this->app->instance('head.test.configure', static function (HeadMetadata $head) use ($text) {
            $head->setMeta([
                'canonical' => 'https://example.test/article', 'description' => $text,
                'og:title' => $text, 'twitter:card' => 'summary', 'robots' => 'index, follow',
            ])->addJsonLd(['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $text])
                ->addJsonLd(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []]);
        });
        $response = $this->get('/page');
        $response->assertOk();
        $document = $this->document($response->getContent());
        $this->assertSame('https://example.test/article', $this->canonical($document));
        $this->assertSame($text, $document->query('//meta[@name="description"]')->item(0)->getAttribute('content'));
        $this->assertSame('summary', $document->query('//meta[@name="twitter:card"]')->item(0)->getAttribute('content'));
        $this->assertCount(0, $document->query('//meta[@property="og:image"]'));
        $scripts = $document->query('//script');
        $this->assertCount(2, $scripts);
        $this->assertSame($text, json_decode($scripts->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR)['headline']);
        $this->assertSame('BreadcrumbList', json_decode($scripts->item(1)->textContent, true, flags: JSON_THROW_ON_ERROR)['@type']);
    }

    public function test_head_state_is_scoped_and_not_retained_by_the_template_singleton(): void
    {
        $template = app('uikittemplate');
        $first = $template->head()->setCanonical('https://example.test/first')
            ->setOpenGraph(['title' => 'First'])->addJsonLd(['@type' => 'Article'])
            ->setDescription('First description')->setTwitterTitle('First tweet')
            ->setHrefLink('en', '/first-custom');
        $this->app->forgetScopedInstances();
        $second = $template->head();

        $this->assertNotSame($first, $second);
        $this->assertSame($template, app('uikittemplate'));
        $this->assertNotSame('https://example.test/first', $second->getCanonical());
        $this->assertSame([], $second->getOpenGraph());
        $this->assertSame([], $second->getJsonLd());
        $this->assertNull($second->getDescription());
        $this->assertNull($second->getTwitterProperty('title'));
        $this->assertNotSame('http://localhost/first-custom', $second->getHrefLink('en'));
    }

    public function test_json_ld_property_override_preserves_other_fields_and_objects_and_is_escaped(): void
    {
        $headline = '</script><script>alert("override")</script>';
        $this->app->instance('head.test.configure', static function (HeadMetadata $head) use ($headline) {
            $head->setJsonLdProperty('@context', 'https://schema.org')
                ->setJsonLdProperty('@type', 'Article')
                ->setJsonLdProperty('headline', 'Original headline')
                ->setJsonLdProperty('description', 'To remove')
                ->addJsonLd(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []])
                ->setJsonLdProperty('headline', $headline)
                ->setJsonLdProperty('description', null)
                ->setJsonLdProperty('name', 'Navigation', 1);
        });

        $response = $this->get('/article');
        $response->assertOk();
        $scripts = $this->document($response->getContent())->query('//script');
        $this->assertCount(2, $scripts);
        $this->assertSame([
            '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $headline,
        ], json_decode($scripts->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame([
            '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [], 'name' => 'Navigation',
        ], json_decode($scripts->item(1)->textContent, true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame($headline, app(HeadMetadata::class)->getJsonLdProperty('headline'));
    }

    public function test_standard_head_uses_the_same_partial_without_duplicating_favicon_or_canonical(): void
    {
        // Isolate unrelated script packages while rendering the actual standard head.
        $directory = sys_get_temp_dir().'/uikit-head-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($directory.'/scripts');
        File::put($directory.'/scripts.blade.php', '');
        File::put($directory.'/scripts/scripts.blade.php', '');
        view()->addNamespace('formfield', $directory);
        view()->addNamespace('ukn', $directory);

        try {
            $document = $this->document(view('uikittemplate::head')->render());
            $this->assertNotSame('', $this->canonical($document));
            $this->assertCount(1, $document->query('//link[@rel="icon"]'));
            $this->assertCount(1, $document->query('//title'));
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
