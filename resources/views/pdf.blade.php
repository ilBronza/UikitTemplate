<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>{{ app('uikittemplate')->getPageTitle() }}</title>

    @include('uikittemplate::css.uikitCss')
    @include('uikittemplate::css.ownPdfCss')

</head>
<body class="{{ app('uikittemplate')->getBodyClass() }}">

  @yield('content')

</body>
</html>
