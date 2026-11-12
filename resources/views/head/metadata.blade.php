@once('uikittemplate-head.document-metadata')
@php($headMetadata = $headMetadata ?? app(\IlBronza\UikitTemplate\Helpers\HeadMetadata::class)->forContext($frontendContext ?? null))
@php($documentTitle = $headMetadata->getTitle($headTitle ?? null))
@if($documentTitle !== null)
    <title>{{ $documentTitle }}</title>
@endif
@foreach($headMetadata->getFavicons() as $favicon)
    <link rel="icon" href="{{ $favicon['path'] }}" @if(!empty($favicon['type'])) type="{{ $favicon['type'] }}" @endif @isset($favicon['sizes']) sizes="{{ $favicon['sizes'] }}" @endisset>
@endforeach
@if($touchIcon = $headMetadata->getTouchIcon())
    <link rel="apple-touch-icon" href="{{ $touchIcon['path'] }}" @isset($touchIcon['sizes']) sizes="{{ $touchIcon['sizes'] }}" @endisset>
@endif
@if($themeColor = $headMetadata->getThemeColor())
    <meta name="theme-color" content="{{ $themeColor }}">
@endif
<link rel="canonical" href="{{ $headMetadata->getCanonical() }}">
@foreach($headMetadata->getHrefLinks() as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
@endforeach
@foreach($headMetadata->getMeta() as $name => $value)
    <meta name="{{ $name }}" content="{{ $value }}">
@endforeach
@foreach($headMetadata->getOpenGraphTags() as $tag)
    <meta property="{{ $tag['property'] }}" content="{{ $tag['content'] }}">
@endforeach
@foreach($headMetadata->getJsonLdScripts() as $json)
    <script type="application/ld+json">{!! $json !!}</script>
@endforeach
@endonce
