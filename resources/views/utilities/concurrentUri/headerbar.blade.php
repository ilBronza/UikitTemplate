@if(config('crud.useConcurrentRequestsAlert') && request()->attributes->has('crud.concurrentUri'))
@include('crud::utilities.concurrentUri.headerbar')
@endif
