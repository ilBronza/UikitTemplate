<?php

namespace IlBronza\UikitTemplate\Tests\Feature;

use IlBronza\Menu\Frontend\FrontendMenu;
use IlBronza\Menu\Frontend\FrontendMenuProvider;
use IlBronza\UikitTemplate\Frontend\FrontendContext;
use IlBronza\UikitTemplate\Frontend\FrontendServiceProvider;
use IlBronza\UikitTemplate\Frontend\MenuLocale;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FrontendCatalogPage
{
    public function getDefaultSeoUrl(?string $locale = null): ?string
    {
        return match ($locale ?? config('app.locale')) {
            'it' => route('test.catalog.it'),
            'en' => route('test.catalog.en'),
            'es' => route('test.catalog.es'),
            default => null,
        };
    }
}

class TranslatedPublicMenuItems implements FrontendMenuProvider
{
    public function build(FrontendMenu $menu, object $context): void
    {
        $menu->addTranslated('menu_test.products', $context->page->getDefaultSeoUrl($menu->locale()), [
            ['label' => $menu->translate('menu_test.contacts'), 'url' => '/contatti'],
        ]);
        $menu->addTranslated('menu_test.fallback', '/fallback');
        $menu->addTranslated('menu_test.unsafe', '/escaped');
        $menu->add('Acme', '/brand');
    }
}

class MenuLocaleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [FrontendServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('l', 32)));
        $app['config']->set('app.locale', 'it');
        $app['config']->set('app.fallback_locale', 'it');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('menu.frontend', [
            'providers' => [TranslatedPublicMenuItems::class],
            'projectProvider' => null,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['translator']->addLines([
            'menu_test.products' => 'Prodotti',
            'menu_test.contacts' => 'Contatti',
            'menu_test.fallback' => 'Voce di ripiego',
            'menu_test.unsafe' => '<strong>Etichetta</strong>',
            'menu_test.page' => 'Contenuto italiano',
        ], 'it');
        $this->app['translator']->addLines([
            'menu_test.products' => 'Products',
            'menu_test.contacts' => 'Contacts',
            'menu_test.unsafe' => '<strong>Label</strong>',
            'menu_test.page' => 'English content',
        ], 'en');
    }

    protected function defineRoutes($router)
    {
        $router->middleware('web')->group(function ($router) {
            foreach (['it' => '/catalogo', 'en' => '/en/catalogo', 'es' => '/es/catalogo'] as $locale => $path) {
                $router->get($path, function () use ($locale) {
                    $page = new FrontendCatalogPage;
                    $urls = [];
                    foreach (config('uikittemplate.availableLanguages') as $language) {
                        if ($url = $page->getDefaultSeoUrl($language)) {
                            $urls[$language] = $url.(request()->getQueryString() ? '?'.request()->getQueryString() : '');
                        }
                    }

                    return Blade::render(<<<'BLADE'
                        @extends('uikit::frontend.layout')
                        @section('content')
                            <p>{{ __('menu_test.page') }}</p>
                        @endsection
                        BLADE, [
                        'frontendContext' => new FrontendContext($locale, 'Catalogo', page: $page, languageUrls: $urls),
                    ]);
                })->name('test.catalog.'.$locale);
            }
            $router->get('/data', fn () => response()->json([
                'locale' => app()->getLocale(),
                'text' => __('menu_test.page'),
            ]));
        });
    }

    public function test_default_menu_language_uses_laravel_configuration(): void
    {
        $response = $this->get('/catalogo?category=lights');

        $response->assertOk()->assertSee('Prodotti')->assertSee('Contatti')
            ->assertSee('data-menu-language-switcher', false)
            ->assertSee('name="_token"', false)
            ->assertSee('value="http://localhost/catalogo?category=lights"', false)
            ->assertSee('aria-label="Lingua del menu"', false);
        $this->assertSelectedLanguage($response->getContent(), 'it');
        $this->assertSame('it', app()->getLocale());
    }

    public function test_preference_changes_menu_labels_and_model_urls_without_changing_application_locale(): void
    {
        $this->post(route('uikittemplate.frontend.menu-locale'), [
            'locale' => 'en',
            'return_to' => '/catalogo?category=lights&page=2',
        ])->assertStatus(303)->assertRedirect('/catalogo?category=lights&page=2')
            ->assertSessionHas(MenuLocale::SESSION_KEY, 'en');

        $response = $this->get('/catalogo?category=lights&page=2');
        $response->assertOk()->assertSee('Products')->assertSee('Contacts')
            ->assertSee('Voce di ripiego')->assertSee('Acme')
            ->assertSee('&lt;strong&gt;Label&lt;/strong&gt;', false)
            ->assertDontSee('<strong>Label</strong>', false)
            ->assertSee('Contenuto italiano')->assertDontSee('English content')
            ->assertSee('<html lang="it">', false)
            ->assertSee('aria-label="Menu" lang="en"', false)
            ->assertSee('href="http://localhost/en/catalogo"', false)
            ->assertSee('aria-label="Menu language"', false);
        $this->assertSelectedLanguage($response->getContent(), 'en');
        $this->assertSame('it', app()->getLocale());
        $this->assertSame('it', app('translator')->getLocale());
    }

    public function test_authenticated_users_can_change_language_too(): void
    {
        $this->actingAs(new GenericUser(['id' => 17]));

        $this->post(route('uikittemplate.frontend.menu-locale'), ['locale' => 'en'])
            ->assertStatus(303)->assertRedirect('/')
            ->assertSessionHas(MenuLocale::SESSION_KEY, 'en');
    }

    public function test_menu_preference_does_not_change_json_responses(): void
    {
        $this->withSession([MenuLocale::SESSION_KEY => 'en'])
            ->getJson('/data')
            ->assertExactJson(['locale' => 'it', 'text' => 'Contenuto italiano']);
    }

    #[DataProvider('invalidLocales')]
    public function test_unavailable_or_invalid_language_does_not_replace_preference(mixed $locale): void
    {
        $this->withSession([MenuLocale::SESSION_KEY => 'it'])
            ->postJson(route('uikittemplate.frontend.menu-locale'), ['locale' => $locale])
            ->assertUnprocessable()->assertJsonValidationErrors('locale')
            ->assertSessionHas(MenuLocale::SESSION_KEY, 'it');
    }

    public static function invalidLocales(): array
    {
        return [['fr'], ['../en'], [['en']], [null], ['']];
    }

    public function test_removed_language_in_session_falls_back_to_app_locale(): void
    {
        config(['uikittemplate.availableLanguages' => ['it', 'es']]);

        $response = $this->withSession([MenuLocale::SESSION_KEY => 'en'])->get('/catalogo');
        $response->assertOk()->assertSee('Prodotti')->assertDontSee('Products');
        $this->assertSelectedLanguage($response->getContent(), 'it');
    }

    public function test_switcher_uses_model_urls_and_redirects_to_the_selected_language(): void
    {
        config(['uikittemplate.availableLanguages' => ['it', 'en', 'es', 'fr']]);
        $response = $this->get('/catalogo?category=lights');
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $form = $xpath->query('//*[@data-menu-language-switcher]//form[button[@value="es"]]')->item(0);
        $target = $xpath->query('.//input[@name="return_to"]', $form)->item(0)->getAttribute('value');

        $this->assertSame('http://localhost/es/catalogo?category=lights', $target);
        $this->assertCount(0, $xpath->query('//*[@data-menu-language-switcher]//button[@value="fr"]'));

        $this->post($form->getAttribute('action'), ['locale' => 'es', 'return_to' => $target])
            ->assertStatus(303)->assertRedirect('/es/catalogo?category=lights')
            ->assertSessionHas(MenuLocale::SESSION_KEY, 'es');

        // Emulate the request lifecycle for the scoped menu in this multi-request test.
        $this->app->forgetScopedInstances();
        $this->get('/es/catalogo?category=lights')->assertOk()
            ->assertSee('href="http://localhost/es/catalogo"', false)
            ->assertSee('<html lang="es">', false);
        $this->assertSame('it', app()->getLocale());
    }

    public function test_default_language_uses_the_unprefixed_url_provided_by_the_model(): void
    {
        $target = (new FrontendCatalogPage)->getDefaultSeoUrl('it');

        $this->withSession([MenuLocale::SESSION_KEY => 'en'])
            ->post(route('uikittemplate.frontend.menu-locale'), ['locale' => 'it', 'return_to' => $target])
            ->assertStatus(303)->assertRedirect('/catalogo')
            ->assertSessionHas(MenuLocale::SESSION_KEY, 'it');
    }

    public function test_same_origin_model_urls_preserve_query_and_fragment(): void
    {
        $this->post(route('uikittemplate.frontend.menu-locale'), [
            'locale' => 'en', 'return_to' => 'http://localhost/en/catalogo?category=lights#details',
        ])->assertStatus(303)->assertRedirect('/en/catalogo?category=lights#details');
    }

    #[DataProvider('singleLanguageConfigurations')]
    public function test_selector_is_hidden_without_multiple_languages(array $languages): void
    {
        config(['uikittemplate.availableLanguages' => $languages]);

        $this->get('/catalogo')->assertOk()->assertSee('Prodotti')
            ->assertDontSee('data-menu-language-switcher', false);
    }

    public static function singleLanguageConfigurations(): array
    {
        return [[['it']], [[]], [['it', 'it']]];
    }

    #[DataProvider('unsafeReturnPaths')]
    public function test_redirect_stays_on_the_current_site(string $target): void
    {
        $this->post(route('uikittemplate.frontend.menu-locale'), [
            'locale' => 'en', 'return_to' => $target,
        ])->assertStatus(303)->assertRedirect('/')
            ->assertSessionHas(MenuLocale::SESSION_KEY, 'en');
    }

    public static function unsafeReturnPaths(): array
    {
        return [
            ['https://example.org'], ['//example.org'], ['/\\example.org'],
            ["/\texample.org"], ['javascript:alert(1)'],
            ['http://localhost:8080/en/catalogo'], ['https://localhost/en/catalogo'],
            ['http://localhost@example.org/en/catalogo'], ['http://user@localhost/en/catalogo'],
            ['http://localhost//example.org'], ['http://localhost.example.org/en/catalogo'],
        ];
    }

    public function test_language_change_requires_post_and_csrf_token(): void
    {
        $this->get(route('uikittemplate.frontend.menu-locale'))->assertStatus(405);

        // Laravel bypasses CSRF during tests; exercise the actual web middleware here.
        $this->app['env'] = 'production';
        try {
            $this->post(route('uikittemplate.frontend.menu-locale'), ['locale' => 'en'])
                ->assertStatus(419)->assertSessionMissing(MenuLocale::SESSION_KEY);

            $token = str_repeat('t', 40);
            $this->withSession(['_token' => $token])
                ->post(route('uikittemplate.frontend.menu-locale'), ['locale' => 'en', '_token' => $token])
                ->assertStatus(303)->assertSessionHas(MenuLocale::SESSION_KEY, 'en');
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    private function assertSelectedLanguage(string $html, string $locale): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $selected = $xpath->query('//*[@data-menu-language-switcher]//button[@aria-pressed="true"]');
        $this->assertCount(1, $selected);
        $this->assertSame($locale, $selected->item(0)->getAttribute('value'));
        $this->assertTrue($selected->item(0)->hasAttribute('disabled'));
    }
}
