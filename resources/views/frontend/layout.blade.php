<!DOCTYPE html>
<html lang="{{ $frontendContext->locale }}">
@include('uikit::frontend.head')
<body>
@include('uikit::frontend.header')
<main id="main-content" class="uk-container">
    @yield('content')
</main>
@include('uikit::frontend.footer')
@include('uikit::frontend.scripts')
</body>
</html>
