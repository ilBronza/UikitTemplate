<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body class="{{ UikitTemplate::getBodyClass() }}">

@if(empty($iframed)&& Auth::id())
    @include('navbar.navbar')
@endif

	@includeIf('layouts.projectSpecificHeader')

    @yield('layout.offcanvas')

    <div class="uk-padding-small">

        @yield('content')

    </div>

	@include('uikittemplate::utilities.__extraViews', ['position' => 'bottom'])

	@includeIf('layouts.projectSpecificFooter')
    @include('uikittemplate::footer')

</body>
</html>
