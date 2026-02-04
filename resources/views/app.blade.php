<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body class="{{ app('uikittemplate')->getBodyClass() }}">
@include('uikittemplate::utilities.concurrentUri.headerbar')

<script>
    jQuery(document).ready(function ($)
	{
        window.checkElementsToRefresh = function ()
        {

            if (window.fetcherToRefresh)
            {
                $(window.fetcherToRefresh).find('a.refresh').click();
                window.fetcherToRefresh = null;
            }

            if (window.tableToRefresh)
            {
                let table = $(window.tableToRefresh).DataTable();

                window.reloadDatatable(table);

                window.tableToRefresh = null;
            }
        }

        $('body').on('click', '.clickfetchermodal', function ()
        {
            window.tableToRefresh = $(this).parents('.datatable');
            window.fetcherToRefresh = $(this).parents('.ibfetchercontainer');
        });

        $('body').on('click', '*[uk-lightbox] a', function ()
        {
            window.tableToRefresh = $(this).parents('.datatable');
            window.fetcherToRefresh = $(this).parents('.ibfetchercontainer');
        });

        UIkit.util.on(document, 'hide', '.uk-modal', function (e)
        {
            window.checkElementsToRefresh();
        });

        UIkit.util.on(document, 'hide', '.uk-lightbox', function (e)
        {
            window.checkElementsToRefresh();
        });
    });
</script>

<style type="text/css">
    input[type=datetime-local]::-webkit-calendar-picker-indicator {
        margin-left: 0px;
        background-color: transparent!important;
    }
</style>

<style type="text/css">
    .ibfetchercontainer {
        position: relative;
    }

    .ibfetcherbuttons .spinner {
        display: none;
    }
</style>

<script type="text/javascript">
    jQuery(document).ready(function($)
    {
        console.log('this happens because when select multiple is empty, the system doesn\'t load the select name as null. This means that you can\'t empty a relation if you send nothing');

        window.convertNullMultipleSelectToNullValue = function (form, target)
        {
            $(target).prop('disabled', true);
            let flatname = $(target).data('flatname');

            $(form).append('<input type="hidden" value="" name="' + flatname + '" />');
        }

        $('body').on('submit', 'form', function(e)
        {
            var form = this;

            $(this).find('select').each(function()
            {
                if($(this).prop('multiple'))
                {
                    if($(this).val().length == 0)
                        window.convertNullMultipleSelectToNullValue(form, this);

                    else if($(this).val().length == 1)
                        if(($(this).val()[0] === '')||($(this).val()[0] === null)||(typeof $(this).val()[0] === 'undefined'))
                            window.convertNullMultipleSelectToNullValue(form, this);
                }
            });
        });

        $('body').on('change', '.charttype', function ()
        {
            let chartid = $(this).data('chartid');
            let chartName = 'myChart' + chartid;

            let chartOptionsName;

            let type = $(this).val();

            if((type == 'pie')||(type == 'doughnut'))
                chartOptionsName = 'pieChartOptions' + chartid;
            else
                chartOptionsName = 'chartOptions' + chartid;

            if(window[chartName])
                window[chartName].destroy();

            var ctx = document.getElementById(chartid).getContext("2d");

            var temp = jQuery.extend(true, {}, window[chartOptionsName]);
            temp.type = type;

            window[chartName] = new Chart(ctx, temp);
        });
    });

</script>



@if((! __ib_IFRAMED__)&& Auth::id())
    {!! app('menu')->render() !!}
@endif

@include('formfield::scripts')

@includeIf('layouts.projectSpecificHeader')

@yield('layout.offcanvas')

<div class="uk-padding-small">

    @yield('content')

</div>

@include('uikittemplate::utilities.__extraViews', ['position' => 'bottom'])

@includeIf('layouts.projectSpecificFooter')
@include('uikittemplate::footer')


@include('uikittemplate::utilities.concurrentUri.scripts')

@if((\Auth::id() == 1)&&($action = app('request')->route()->getAction()))

    <pre>
Controller: 

{{ ($action['controller'] ?? 'nessun controller') }}
    
        @if(isset($fieldsetParametersFile))

FieldsetsParametersFile: 

{{ $fieldsetParametersFile }}
        
        @endif

        @foreach(app('uikittemplate')->getFieldsGroupsNames() as $fieldsGroupsName)

FieldsGroupsName: 

{{ $fieldsGroupsName }}
        @endforeach

    </pre>
@endif

</body>
</html>
