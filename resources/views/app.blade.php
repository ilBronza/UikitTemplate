<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body>

@if(empty($iframed)&&isset($currentUser))
    @include('navbar.navbar')
@endif

    @yield('layout.offcanvas')

    <div class="uk-padding-small">

    	@include('uikittemplate::models._parentModel')

        @yield('content')

    </div>

    @include('uikittemplate::footer')

</body>
</html>
