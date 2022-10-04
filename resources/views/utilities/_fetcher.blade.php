<div
	class="ibfetcher"
	data-url="{{ $url }}"

	@isset($title)
	data-title="{{ $title }}"
	@endisset

	id="{{ $fetcherId ?? Str::slug($url) }}"
	>
	<div class="buttons">
		<a class="refresh" href="javascript:void(0)" uk-icon="icon: refresh"></a>
		<div class="spinner" uk-spinner="{ratio: 1}"></div>
	</div>
	<div class="fetchercontainer">
		
	</div>

</div>