<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body class="{{ UikitTemplate::getBodyClass() }}">

<style type="text/css">
input[type=datetime-local]::-webkit-calendar-picker-indicator
{
  margin-left: 0px;
  background-color: transparent!important;
}	
</style>
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
