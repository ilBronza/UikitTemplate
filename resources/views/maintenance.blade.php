<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('uikittemplate::uikittemplate.maintenanceTitle') }} · {{ config('app.name') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f5f6f8; color: #243144; font: 1rem/1.6 system-ui, sans-serif; }
        main { max-width: 36rem; padding: 3rem 1.5rem; text-align: center; }
        .project-logo { display: block; width: auto; height: auto; max-width: min(100%, 16rem); max-height: 8rem; margin: 0 auto 2rem; object-fit: contain; }
        h1 { font-size: clamp(1.75rem, 5vw, 2.5rem); line-height: 1.2; }
        p { color: #526078; }
        .login-link { display: inline-block; margin-top: 1rem; padding: .65rem 1.5rem; border-radius: .4rem; background: #243144; color: #fff; font-weight: 600; text-decoration: none; }
        .login-link:hover, .login-link:focus { background: #35465f; text-decoration: underline; }
    </style>
</head>
<body>
    <main>
        @if($logo = config('uikittemplate.logo') ?: config('menu.logo.path') ?: config('mail.logo.path'))
            <img class="project-logo" src="{{ asset($logo) }}" alt="{{ config('app.name') }}">
        @endif
        <h1>{{ __('uikittemplate::uikittemplate.maintenanceTitle') }}</h1>
        <p>{{ __('uikittemplate::uikittemplate.maintenanceMessage') }}</p>
        <a class="login-link" href="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login') }}">
            {{ __('uikittemplate::uikittemplate.maintenanceLogin') }}
        </a>
    </main>
</body>
</html>
