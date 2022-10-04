<div
	class="ibfetcher"
	data-url="{{ $fetcher->getUrl() }}"

	@if($title = $fetcher->getTitle())
	data-title="{{ $title }}"
	@endisset

	id="{{ $fetcher->getId() }}"
>
</div>
