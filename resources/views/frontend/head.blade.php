<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $frontendContext->title }}</title>
    @foreach($frontendContext->meta as $name => $value)
        @if(in_array($name, ['description', 'robots'], true))
            <meta name="{{ $name }}" content="{{ $value }}">
        @elseif(str_starts_with($name, 'og:'))
            <meta property="{{ $name }}" content="{{ $value }}">
        @elseif(str_starts_with($name, 'twitter:'))
            <meta name="{{ $name }}" content="{{ $value }}">
        @elseif($name === 'canonical')
            <link rel="canonical" href="{{ $value }}">
        @endif
    @endforeach
    @stack('frontend.styles')
    @stack('frontend.head')
</head>
