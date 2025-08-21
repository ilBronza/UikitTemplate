<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<script type="text/javascript">

        window.csrfToken = '{{ csrf_token() }}';
        window.replace_model_id_string = "{{ config("datatables.replace_model_id_string") }}";

        window.fieldsVisibilityString = "{{ __('datatables::fields.fieldsVisibilityLabel') }}";
	</script>

	<script src="{{ config('app.url') }}/js/app.js?v={{ config('uikittemplate.version', "1.0.0") }}"></script>

	<script type="text/javascript">
        jQuery.ajaxSetup({headers: {'X-CSRF-Token': '{{ csrf_token() }}'}});
	</script>

	<link rel="icon" type="image/x-icon" href="{{ config('uikittemplate.favicon.path', '/favicon.ico') }}">

	<link href="/fa/css/all.min.css" rel="stylesheet"/>

	<link rel="stylesheet" type="text/css"
		  href="{{ config('app.url') }}/css/app.css?v={{ config('uikittemplate.version', "1.0.0") }}"/>
	<link rel="stylesheet" type="text/css"
		  href="{{ config('app.url') }}/uikittemplate/uikit/templates/{{ config('uikittemplate.theme') }}.css?v={{ config('uikittemplate.version', "1.0.0") }}"/>
	<link rel="stylesheet" type="text/css"
		  href="{{ config('app.url') }}/css/own.css?v={{ config('uikittemplate.version', "1.0.0") }}"/>
	<link rel="stylesheet" type="text/css"
		  href="{{ config('app.url') }}/css/datatable.css?v={{ config('uikittemplate.version', "1.0.0") }}"/>

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

        /*table.dataTable tbody a {*/
        /*    display: inline-block;*/
        /*    text-overflow: ellipsis;*/
        /*    white-space: nowrap;*/
        /*}*/

        table.dataTable tbody a:not(.uk-icon) {
            overflow: hidden;
        }

        .uk-accordion-title::before {
            float: left;
        }
	</style>

	@includeIf('layouts._projectScripts')

</head>