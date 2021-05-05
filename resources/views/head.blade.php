<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="/uikittemplate/jquery/jquery.min.js"></script>
    <script src="/uikittemplate/jquery/jquery-ui.min.js"></script>
    <script src="/uikittemplate/uikit/js/uikit.min.js"></script>
    <script src="/uikittemplate/uikit/js/uikit-icons.min.js"></script> 

    <script src="/uikittemplate/moment/moment.min.js"></script>

    <link rel="stylesheet" type="text/css" href="/uikittemplate/uikit/css/uikit.min.css"/>
    <link rel="stylesheet" type="text/css" href="/uikittemplate/uikit/templates/{{ config('uikittemplate.theme') }}.css"/>

    <script type="text/javascript">
    jQuery.ajaxSetup({headers     :{'X-CSRF-Token': '{{ csrf_token() }}'}});
    window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token()]); ?>
    </script>


    @include('crud::scripts')
    @include('datatables::scripts.mainScripts')
    @include('formfield::scripts.scripts')

    @include('ukn::scripts')


    <style type="text/css">

        table.dataTable tbody a
        {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>


    @include('layouts._projectScripts')
</head>