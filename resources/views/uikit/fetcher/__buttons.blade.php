<div class="ibfetcherbuttons {{ $fetcher->getId() }}" data-id="{{ $fetcher->getId() }}">
	@foreach($fetcher->getButtons() as $button)
	{!! $button->render() !!}
	@endforeach

	<a class="refresh" href="javascript:void(0)" uk-icon="icon: refresh"></a>
	<div class="spinner" uk-spinner="{ratio: 1}"></div>

	@if($fetcher->canExpand())
	<a class="expand" href="javascript:void(0)"><i class="fa-solid fa-expand"></i></a>
	@endif
</div>