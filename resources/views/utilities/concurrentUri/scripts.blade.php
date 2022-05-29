@if(isset($checkConcurrentUri) && ($concurrentUri = app()->get('concurrentUriChecker')))


<script type="text/javascript">
	jQuery(document).ready(function()
	{

		$(window).on('beforeunload', function()
		{
			jQuery.ajax({
				url: '{{ route('concurrentUri.leavePage') }}',
				type: 'POST',
				data : {
					url: "{{ $concurrentUri->getBasePathKey() }}",
					pageKey: "{{ $concurrentUri->getPrevalentPageKey() }}"
				},
				success: function(response)
				{
					console.log(response);
				}
			});
		});

		// $(window).on('unload', function(){
		// 	alert('asdasd');
		// });

	window.concurrentUriStatus = 'owner';

	@if(! $concurrentUri->userIsAllowed())

		window.concurrentUriStatus = 'notOwner';
		window.addDangerNotification("@lang('crud::crud.aDifferentUserIsWatchingThisPage')");

	@elseif($concurrentUri->isMultipleOpening())

		window.addWarningNotification("@lang('crud::crud.youAlreadyOpenedThisUrlOnADifferentPage')");

	@endif

		setInterval(function()
			{
				jQuery.ajax({
					url: '{{ route('concurrentUri.tick') }}',
					type: 'POST',
					data : {
						url: "{{ $concurrentUri->getBasePathKey() }}",
						pageKey: "{{ $concurrentUri->getPrevalentPageKey() }}"
					},
					success: function(response)
					{
						console.log(response.userData.pageKey);
						let target = $('#ib-concurrent-uri .uk-alert');

						if(response.status == 'gained')
						{
							target
								.removeClass('uk-alert-danger')
								.removeClass('uk-alert-warning')
								.addClass('uk-alert-success');

							window.addSuccessNotification(response.message);
							window.concurrentUriStatus = 'owner';
						}
						else if(response.status == 'kept')
						{
							window.concurrentUriStatus = 'owner';
						}
						else if(response.status == 'morePages')
						{
							if(window.concurrentUriStatus == 'owner')
							{
								target
									.removeClass('uk-alert-danger')
									.removeClass('uk-alert-success')
									.addClass('uk-alert-warning');

								window.addWarningNotification(response.message);
							}


							window.concurrentUriStatus = 'notOwner';
						}
						else if(response.status == 'notAllowed')
						{
							if(window.concurrentUriStatus == 'owner')
							{
								target
									.removeClass('uk-alert-success')
									.removeClass('uk-alert-warning')
									.addClass('uk-alert-danger');

								window.addDangerNotification(response.message);
							}

							window.concurrentUriStatus = 'notOwner';		
						}
					}
				});
			}, {{ $concurrentUri->getJavascriptTickInterval() }});


		// setInterval(function()
		// 	{
		// 		jQuery.ajax({
		// 			url: '{-- route('concurrentUri.check') --}',
		// 			type: 'POST',
		// 			data : {
		// 				url: "{{ $concurrentUri->getBasePathKey() }}",
		// 				pageKey: "{{ $concurrentUri->getPrevalentPageKey() }}"
		// 			},
		// 			success: function(response)
		// 			{
		// 				console.log(response);
		// 			}
		// 		});
		// 	}, 100);



	});
</script>

@endif