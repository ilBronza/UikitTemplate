<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<script type="text/javascript">

        window.csrfToken = '{{ csrf_token() }}';
        window.replace_model_id_string = "{{ config("datatables.replace_model_id_string") }}";

        @if($googleMapsApiKey = config('services.google.maps_key'))
		window.googleMapsApiKey = "{{ $googleMapsApiKey }}";
        @endif

        window.fieldsVisibilityString = "{{ __('datatables::fields.fieldsVisibilityLabel') }}";
	</script>

	@if(config('crud.broadcast.enabled'))
		@php
			$reverbConfig = [
				'key' => config('broadcasting.connections.reverb.key'),
				'host' => config('broadcasting.connections.reverb.options.host'),
				'port' => config('broadcasting.connections.reverb.options.port'),
				'scheme' => config('broadcasting.connections.reverb.options.scheme'),
			];
		@endphp
		<script type="text/javascript">
			window.reverbConfig = {{ Illuminate\Support\Js::from($reverbConfig) }};
		</script>
	@endif

	<script src="{{ config('app.url') }}/js/app.js?v={{ config('uikittemplate.version', "1.0.0") }}"></script>

	@if(config('crud.broadcast.enabled'))
		<script type="text/javascript">
            window.Echo = window.createReverbEcho();
		</script>

		@if(auth()->check())
			<script type="text/javascript">
			(() => {
				let horizonAlertShown = false;

				const checkBroadcastHealth = async () => {
					try {
						const response = await fetch('{{ route('uikittemplate.broadcast-health') }}', {
							headers: {'Accept': 'application/json'},
							credentials: 'same-origin',
						});

						if (! response.ok) {
							return;
						}

						const {status} = await response.json();

						if (status === 'running') {
							horizonAlertShown = false;
							return;
						}

						if (! horizonAlertShown) {
							horizonAlertShown = true;
							alert('Horizon non è attivo: gli aggiornamenti in tempo reale potrebbero non arrivare.');
						}
					} catch (error) {
						console.warn('Impossibile verificare lo stato di Horizon.', error);
					}
				};

				checkBroadcastHealth();
				window.setInterval(checkBroadcastHealth, 60_000);
			})();
		</script>
		@endif
	@endif

	<script type="text/javascript">
        jQuery.ajaxSetup({headers: {'X-CSRF-Token': '{{ csrf_token() }}'}});
	</script>

	<link rel="icon" type="image/x-icon" href="{{ config('uikittemplate.favicon.path', '/favicon.png') }}">

	<link rel="stylesheet" type="text/css"
		  href="{{ config('app.url') }}/css/app.css?v={{ config('uikittemplate.version', "1.0.0") }}"/>
	<link rel="stylesheet" type="text/css"
		  href="{{ config('app.url') }}/uikittemplate/uikit/templates/{{ config('uikittemplate.theme') }}.css?v={{ config('uikittemplate.version', "1.0.0") }}"/>
	<link rel="stylesheet" type="text/css"
		  href="{{ config('app.url') }}/css/own.css?v={{ config('uikittemplate.version', "1.0.0") }}"/>

	<title>{{ strip_tags(app('uikittemplate')->getPageTitle()) }}</title>

	@include('formfield::scripts.scripts')

	<script type="text/javascript">
        var onloadCallback = function ()
        {
            grecaptcha.render('recaptcha', {
                'sitekey': '6LeIUKooAAAAAEajMZUwoQRf2Q-VSLODcukkQCIm'
            });
        };
	</script>

	@include('ukn::scripts')
	<style type="text/css">

        .uk-tooltip {
            max-width: 100% !important;
        }

        table.dataTable tbody a:not(.uk-icon) {
            overflow: hidden;
        }

        .uk-accordion-title::before {
            float: left;
        }

	</style>

	@includeIf('layouts._projectScripts')

</head>
