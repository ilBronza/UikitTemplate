<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>

    <title>{{ app('uikittemplate')->getPageTitle() }}</title>

    <style type="text/css">
      @include('uikittemplate::css.pdf.base')
    </style>


</head>
<body class="{{ app('uikittemplate')->getBodyClass() }}">

  @yield('content')

</body>
</html>
