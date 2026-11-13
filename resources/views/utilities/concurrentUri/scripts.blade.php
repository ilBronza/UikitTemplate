@if(config('crud.useConcurrentRequestsAlert') && request()->attributes->has('crud.concurrentUri'))
@if(config('uikittemplate.concurrentUriAppearance.enabled', true))
@once('uikittemplate-head.client')
@include('uikittemplate-head::client')
@endonce
@once('uikittemplate.concurrent-uri-appearance')
@php
    $concurrentUriAppearance = [
        'warningFavicon' => config('uikittemplate.concurrentUriAppearance.warningFavicon'),
        'warningThemeColor' => config('uikittemplate.concurrentUriAppearance.warningThemeColor', '#facc15'),
    ];
@endphp
<script>
window.ibConcurrentUriAppearanceConfig = @json($concurrentUriAppearance);
</script>
@include('uikittemplate::utilities.concurrentUri.appearance')
@endonce
@endif
@include('crud::utilities.concurrentUri.scripts')
@endif
