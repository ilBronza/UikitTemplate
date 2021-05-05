<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body>

@if(empty($iframed))
    @include('navbar.navbar')
@endif

    @yield('layout.offcanvas')

    <div class="uk-padding-large">

    	@include('uikittemplate::models._parentModel')

        @yield('content')

    </div>

    @include('uikittemplate::footer')

</body>
</html>
