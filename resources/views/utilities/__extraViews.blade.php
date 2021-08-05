@isset($extraViews[$position])
    @foreach($extraViews[$position] as $extraView => $parameters)
    	@include($extraView, $parameters)
    @endforeach
@endisset