<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('uikittemplate-head::metadata', ['headTitle' => $frontendContext->title])
    @stack('frontend.styles')
    @stack('frontend.head')
</head>
