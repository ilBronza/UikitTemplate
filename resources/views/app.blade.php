<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body>

	    <h1>{{ Route::currentRouteName() }}</h1>


@if(empty($iframed)&& Auth::id())
    @include('navbar.navbar')
@endif

    @yield('layout.offcanvas')

    <div class="uk-padding-small">

        @yield('content')

    </div>

    @include('uikittemplate::footer')

</body>
</html>
