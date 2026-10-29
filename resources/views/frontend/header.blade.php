@php
    $frontendMenu = app('menu.frontend')->build($frontendContext);
@endphp
<header>
    <nav aria-label="{{ config('menu.frontend.label', 'Menu') }}" lang="{{ str_replace('_', '-', $frontendMenu->locale()) }}">
        @include('uikit::frontend.menu.items', ['items' => $frontendMenu->items()])
        @include('uikit::frontend.menu.language-switcher', ['frontendMenu' => $frontendMenu])
    </nav>
    @foreach($frontendContext->links as $link)
        <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
    @endforeach
    @yield('frontend.header')
</header>
