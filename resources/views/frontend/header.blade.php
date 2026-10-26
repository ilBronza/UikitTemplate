@php
    $frontendMenu = app('menu.frontend')->build($frontendContext);
@endphp
<header>
    <nav aria-label="{{ config('menu.frontend.label', 'Menu') }}">
        @include('uikit::frontend.menu.items', ['items' => $frontendMenu->items()])
    </nav>
    @foreach($frontendContext->links as $link)
        <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
    @endforeach
    @yield('frontend.header')
</header>
