<div id="fetchercontainer{{ $fetcher->getId() }}" class="ibfetchercontainer uk-card uk-card-small">
	<div class="uk-card-header">

		@if($title = $fetcher->getTitle())
		<div class="uk-display-inline-block uk-float-left fetchertitle">
			{!! $title !!}
		</div>
		@endif
		<div class="uk-align-right">
			@include('uikittemplate::uikit.fetcher.__buttons')			
		</div>


	</div>
	<div class="uk-card-body">

		@include('uikittemplate::uikit.fetcher.__fetcher')
		@include('uikittemplate::uikit.fetcher.__container')

	</div>

	@if($fetcher->hasFields())
	<div class="uk-card-footer ibfetcherfields {{ $fetcher->getId() }}">

		@foreach($fetcher->getFields() as $field)
			{!! $field->render() !!}
		@endforeach

		@include('uikittemplate::uikit.fetcher.__buttons')

	</div>
	@endif
</div>