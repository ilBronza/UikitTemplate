<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body class="{{ app('uikittemplate')->getBodyClass() }}">

@if(env('APP_ENV') === 'staging')
    @include('uikittemplate::utilities._clientTestEnvironment')
@endif


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

        // Only iframe lightboxes (edit/forms). Plain image lightboxes must not
        // mark the parent datatable for reload on close.
        $('body').on('click', '*[uk-lightbox] a[data-type="iframe"]', function ()
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

        window.__ibBindEscCloseParentOverlays = window.__ibBindEscCloseParentOverlays || function ()
        {
            if (window.__ibEscCloseParentOverlaysBound)
                return;

            window.__ibEscCloseParentOverlaysBound = true;

            // Escape inside an iframe never reaches the parent, so UIkit cannot close
            // the overlay. Forward it when we are framed.
            $(document).on('keydown', function (e)
            {
                if (e.key !== 'Escape')
                    return;

                if (window.self === window.top)
                    return;

                e.preventDefault();

                const parent = window.parent;

                if (typeof parent.closeLightbox === 'function')
                    parent.closeLightbox();
                else
                {
                    const openLightbox = parent.document.querySelector('.uk-lightbox.uk-open');

                    if (openLightbox)
                    {
                        if (! parent.UIkit)
                            throw new Error('Cannot close parent lightbox: UIkit is not available on parent window');

                        parent.UIkit.lightbox(openLightbox).hide();
                    }
                }

                if (typeof parent.closeModal === 'function')
                    parent.closeModal();
                else
                {
                    const openModal = parent.document.querySelector('.uk-modal.uk-open');

                    if (openModal)
                    {
                        if (! parent.UIkit)
                            throw new Error('Cannot close parent modal: UIkit is not available on parent window');

                        parent.UIkit.modal(openModal).hide();
                    }
                }

                if (typeof parent.__ibForceUnlockBodyScroll === 'function')
                    parent.__ibForceUnlockBodyScroll();
            });
        };

        window.__ibBindEscCloseParentOverlays();
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


@if(! ($ib_IFRAMED ?? false) && Auth::check())
    {!! app('menu')->render() !!}
    @includeIf('layouts.projectSpecificHeader')
@endif

@include('formfield::scripts')


@yield('layout.offcanvas')

<div class="uk-padding-small">

    @yield('content')

</div>

@include('uikittemplate::utilities.__extraViews', ['position' => 'bottom'])

@includeIf('layouts.projectSpecificFooter')
@include('uikittemplate::footer')
@if(config('uikittemplate.show_broadcast_updates', true))
    @include('uikittemplate::components._footerEventLog')
@endif


@include('uikittemplate::utilities.concurrentUri.scripts')

@includeWhen(auth()->check() && view()->exists('accountmanager::heartbeat'), 'accountmanager::heartbeat')

@if((\Auth::id() == 1)&&($action = app('request')->route()->getAction()))

    <pre>
<strong class="uk-h2">Controller:</strong>

{{ ($action['controller'] ?? 'nessun controller') }}
    
        @if(isset($fieldsetParametersFile))

<strong class="uk-h2">FieldsetsParametersFile:</strong>

{{ $fieldsetParametersFile }}
        
        @endif

<strong class="uk-h2">RelationsManagerNames:</strong>

        @foreach(app('uikittemplate')->getRelationsManagerNames() as $name)

{{ $name }}
        @endforeach

<strong class="uk-h2">FieldsGroupsName:</strong>

        @foreach(app('uikittemplate')->getFieldsGroupsNames() as $fieldsGroupsName)

{{ $fieldsGroupsName }}
        @endforeach

<strong class="uk-h2">Custom Getters:</strong>

        @foreach(app('uikittemplate')->getCustomGetters() as $customGetter)
{{ $customGetter }}
        @endforeach

    </pre>
@endif

</body>
</html>
