<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')



<script type="text/javascript" src="/js/ilbronza.crud.min.js"></script>

<body>

@if(empty($iframed)&& Auth::id())
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
