<?php

namespace IlBronza\UikitTemplate\Tests\Feature;

use IlBronza\UikitTemplate\Http\Middleware\OnlyAdmin;
use IlBronza\UikitTemplate\UikitTemplateServiceProvider;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\Permission\Traits\HasRoles;

class OnlyAdminUser extends User
{
    use HasRoles;
}

class ExistingWebMiddleware
{
    public function handle($request, $next)
    {
        $response = $next($request);
        $response->headers->set('X-Existing-Web-Middleware', 'present');

        return $response;
    }
}

class ExistingWebMiddlewareProvider extends ServiceProvider
{
    public function boot(): void
    {
        // HTTP applications resolve the kernel before booting package providers.
        $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $this->app['router']->pushMiddlewareToGroup('web', ExistingWebMiddleware::class);
    }
}

class OnlyAdminTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            PermissionServiceProvider::class,
            ExistingWebMiddlewareProvider::class,
            UikitTemplateServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('x', 32)));
    }

    protected function defineRoutes($router)
    {
        $router->middleware('web')->group(function ($router) {
            $router->get('/public', fn () => 'Site content');
            $router->get('/private', fn () => 'Private content')->middleware('auth');
            $router->post('/write', function () {
                $this->app->instance('maintenance.writeExecuted', true);

                return 'Saved';
            });
            $router->get('/records/{maintenanceRecord}', fn () => 'Record content');

            $router->get('/login', fn () => 'Login')->name('login');
            $router->post('/login', fn () => 'Authenticated');
            $router->match(['GET', 'POST'], '/logout', fn () => 'Logged out');
            $router->match(['GET', 'POST'], '/login/ldap', fn () => 'LDAP login');
            $router->match(['GET', 'POST'], '/two-factor-challenge', fn () => 'Two factor');
            $router->get('/auth/redirect', fn () => 'OAuth redirect');
            $router->get('/auth/callback', fn () => 'OAuth callback');
            $router->get('/staff/sign-in', fn () => 'Custom login')->name('custom.login');
        });

        $router->bind('maintenanceRecord', fn () => abort(404));
    }

    protected function userWithRoles(array $roles): OnlyAdminUser
    {
        $user = new OnlyAdminUser;
        $user->id = 7;
        $user->setRelation('roles', new Collection(array_map(
            fn ($name) => new Role(['name' => $name, 'guard_name' => 'web']),
            $roles
        )));

        return $user;
    }

    public function test_access_is_unrestricted_by_default(): void
    {
        $this->assertFalse(config('uikittemplate.onlyAdmin'));
        $this->get('/public')->assertOk()->assertSee('Site content');
        $this->actingAs($this->userWithRoles(['editor']))
            ->get('/private')->assertOk()->assertSee('Private content');
    }

    public function test_middleware_registered_by_other_packages_is_preserved(): void
    {
        $this->get('/public')->assertOk()->assertHeader('X-Existing-Web-Middleware', 'present');
    }

    public function test_registration_works_without_modern_kernel_methods(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $router = $this->app['router'];
        $router->middlewareGroup('web', array_values(array_filter(
            $router->getMiddlewareGroups()['web'],
            fn ($middleware) => $middleware !== OnlyAdmin::class
        )));
        $router->middlewarePriority = array_values(array_filter(
            $router->middlewarePriority,
            fn ($middleware) => $middleware !== OnlyAdmin::class
        ));

        // This contract has no setMiddlewareGroups or addToMiddlewarePriorityAfter.
        $this->app->instance(Kernel::class, Mockery::mock(Kernel::class));

        try {
            (new UikitTemplateServiceProvider($this->app))->boot();
            (new UikitTemplateServiceProvider($this->app))->boot();
        } finally {
            $this->app->instance(Kernel::class, $kernel);
        }

        $this->assertSame(1, count(array_keys($router->middlewarePriority, OnlyAdmin::class, true)));
        $this->assertSame(1, count(array_keys($router->getMiddlewareGroups()['web'], OnlyAdmin::class, true)));

        $this->get('/public')->assertOk()->assertHeader('X-Existing-Web-Middleware', 'present');

        config(['uikittemplate.onlyAdmin' => true]);
        $this->get('/private')->assertStatus(503);
        $this->get('/records/123')->assertStatus(503);

        $provider = Mockery::mock(UserProvider::class);
        $provider->shouldReceive('retrieveById')->once()->with(7)
            ->andReturn($this->userWithRoles(['administrator']));
        $guard = Auth::guard('web');
        $guard->setProvider($provider);

        $this->withSession([$guard->getName() => 7])
            ->get('/private')->assertOk()->assertSee('Private content');
    }

    public function test_guests_see_maintenance_on_public_and_authenticated_pages(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        foreach (['/public', '/private'] as $path) {
            $response = $this->get($path)
                ->assertStatus(503)
                ->assertViewIs('uikittemplate::maintenance')
                ->assertSee('Site under maintenance')
                ->assertDontSee('Site content')
                ->assertHeader('Retry-After', '60');

            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
    }

    public static function privilegedRoles(): array
    {
        return [
            'administrator' => [['administrator']],
            'superadmin' => [['superadmin']],
            'multiple roles' => [['editor', 'administrator']],
        ];
    }

    #[DataProvider('privilegedRoles')]
    public function test_administrators_can_visit_the_site(array $roles): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->actingAs($this->userWithRoles($roles))
            ->get('/private')->assertOk()->assertSee('Private content');
        $this->post('/write')->assertOk()->assertSee('Saved');
    }

    public static function unprivilegedRoles(): array
    {
        return [
            'editor' => [['editor']],
            'no roles' => [[]],
            'similar role name' => [['administrator-assistant']],
        ];
    }

    #[DataProvider('unprivilegedRoles')]
    public function test_other_users_see_maintenance(array $roles): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->actingAs($this->userWithRoles($roles))
            ->get('/private')->assertStatus(503)->assertViewIs('uikittemplate::maintenance');
    }

    public function test_users_without_role_support_see_maintenance(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->actingAs(new GenericUser(['id' => 7]))->get('/public')->assertStatus(503);
    }

    public function test_administrators_are_resolved_from_the_web_session(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $provider = Mockery::mock(UserProvider::class);
        $provider->shouldReceive('retrieveById')->once()->with(7)
            ->andReturn($this->userWithRoles(['superadmin']));
        $guard = Auth::guard('web');
        $guard->setProvider($provider);

        $this->withSession([$guard->getName() => 7])
            ->get('/private')->assertOk()->assertSee('Private content');
    }

    public function test_blocked_writes_never_reach_the_controller(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->actingAs($this->userWithRoles(['editor']))->post('/write')->assertStatus(503);

        $this->assertFalse($this->app->bound('maintenance.writeExecuted'));
    }

    public function test_maintenance_runs_before_route_model_binding(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->get('/records/123')->assertStatus(503)->assertViewIs('uikittemplate::maintenance');
    }

    public function test_json_requests_receive_a_503_without_html_or_redirects(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->getJson('/private')->assertStatus(503)->assertExactJson([
            'message' => 'We are making some updates. We will be back online soon.',
        ])->assertHeader('Retry-After', '60');
        $this->postJson('/write')->assertStatus(503);
        $this->assertFalse($this->app->bound('maintenance.writeExecuted'));
    }

    public function test_login_logout_and_authentication_callbacks_remain_reachable(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        foreach (['/login', '/logout', '/login/ldap', '/two-factor-challenge'] as $path) {
            $this->get($path)->assertOk();
            $this->post($path)->assertOk();
        }

        $this->get('/auth/redirect')->assertOk();
        $this->get('/auth/callback')->assertOk();
    }

    public function test_an_unprivileged_user_can_still_log_out(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->actingAs($this->userWithRoles(['editor']))
            ->post('/logout')->assertOk()->assertSee('Logged out');
    }

    public function test_custom_authentication_exceptions_match_paths_and_route_names(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);

        $this->get('/staff/sign-in')->assertStatus(503);

        config(['uikittemplate.onlyAdminExcept' => ['custom.login']]);
        $this->get('/staff/sign-in')->assertOk();
        $this->get('/public')->assertStatus(503);

        config(['uikittemplate.onlyAdminExcept' => ['staff/*']]);
        $this->get('/staff/sign-in')->assertOk();
    }

    public function test_maintenance_is_translated_and_escapes_the_application_name(): void
    {
        config(['uikittemplate.onlyAdmin' => true, 'app.name' => '<script>alert(1)</script>']);
        $this->app->setLocale('it');

        $this->get('/public')->assertStatus(503)
            ->assertSee('Sito in manutenzione')
            ->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>', false);
    }

    public function test_disabling_maintenance_restores_normal_access(): void
    {
        config(['uikittemplate.onlyAdmin' => true]);
        $this->get('/public')->assertStatus(503);

        config(['uikittemplate.onlyAdmin' => false]);
        $this->get('/public')->assertOk()->assertSee('Site content');
    }
}
