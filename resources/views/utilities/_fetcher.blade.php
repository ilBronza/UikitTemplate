{{-- <script type="text/javascript">
jQuery(document).ready(function($)
{
	$.ajax({
		url: '{{ $url }}',
		success: function(response)
		{
			$('#{{ $fetcherId }}').append(response);
		}
	})
})
</script>
 --}}

<div
	class="ibfetcher"
	data-url="{{ $url }}"
	id="{{ $fetcherId }}"
	data-event="load"
	>

	cliccame
</div>