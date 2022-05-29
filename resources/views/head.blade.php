<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="/js/app.js"></script>

    <link rel="stylesheet" type="text/css" href="/css/app.css"/>

    <link rel="stylesheet" type="text/css" href="/uikittemplate/uikit/templates/{{ config('uikittemplate.theme') }}.css"/>
    <link rel="stylesheet" type="text/css" href="/css/own.css"/>

    <script type="text/javascript">

        jQuery.ajaxSetup({headers     :{'X-CSRF-Token': '{{ csrf_token() }}'}});
        window.replace_model_id_string = "{{ config("datatables.replace_model_id_string") }}";

    </script>

    <title>{{ UikitTemplate::getPageTitle() }}</title>

    @include('formfield::scripts.scripts')

    @include('ukn::scripts')
    <style type="text/css">

        .uk-tooltip
        {
            max-width: 100%!important;
        }

        table.dataTable tbody a
        {
            display: inline-block;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        table.dataTable tbody a:not(.uk-icon)
        {
            overflow: hidden;            
        }

        .uk-accordion-title::before
        {
            float: left;
        }
    </style>

    @includeIf('layouts._projectScripts')

</head>