<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>

    <title>{{ app('uikittemplate')->getPageTitle() }}</title>

    <link rel="stylesheet" type="text/css" href="{{ public_path('/uikittemplate/uikit/templates/' . config('uikittemplate.pdf.theme') . '.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ public_path('/css/ownpdf.css') }}"/>

</head>
<body class="{{ app('uikittemplate')->getBodyClass() }}">

  @yield('content')

</body>
</html>
