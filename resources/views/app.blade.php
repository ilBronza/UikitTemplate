<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body>

    @include('navbars.navbar')
    @yield('layout.offcanvas')

    <div class="uk-padding-large">

    	@include('uikittemplate::models._parentModel')

        @yield('content')

    </div>

    @include('uikittemplate::footer')

</body>
</html>
