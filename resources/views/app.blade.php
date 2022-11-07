<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body class="{{ app('uikittemplate')->getBodyClass() }}">
  @include('uikittemplate::utilities.concurrentUri.headerbar')

<style type="text/css">
input[type=datetime-local]::-webkit-calendar-picker-indicator
{
  margin-left: 0px;
  background-color: transparent!important;
}	
</style>


<style type="text/css">
.ibfetchercontainer
{
  position: relative;
}

.ibfetcherbuttons .spinner
{
  display: none;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($)
{
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






<script type="text/javascript">

window.ibFetcherSpin = function(target)
{
  var id = $(target).attr('id');

  console.log('spinno');
  $('fetcherbuttons-' + id).find('.spinner').css('display', 'block');
  $('fetcherbuttons-' + id).find('a.refresh').css('display', 'none');
}

window.ibFetcherStopSpinning = function(target)
{
  var id = $(target).attr('id');

  console.log('DEspinno');
  $('fetcherbuttons-' + id).find('.spinner').css('display', 'none');
  $('fetcherbuttons-' + id).find('a.refresh').css('display', 'inline');
}

window.ibFetcherCollectData = function(target)
{
  var id = $(target).attr('id');

  var fieldContainer = $('.ibfetcherfields.' + id);

  if(! fieldContainer.length)
    return null;

  var data = {};

  $(fieldContainer).find('input').each(function ()
  {
    var name = $(this).attr('name');
    var value = $(this).val();

    data[name] = value;
  });

  return data;
}

window.ibFetcherFetch = function (target, warn = false)
{
  window.ibFetcherSpin(target);

  let id = $(target).attr('id');
  let event = $(target).data('event');
  let url = $(target).data('url');

  let data = window.ibFetcherCollectData(target);

  $.ajax({
    url: url,
    data: data,
    success: function (response)
    {
      $('.fetchercontainer.' + id).html(response);

      $(target).data('loaded', true);
      window.ibFetcherStopSpinning(target);

      if(warn)
      {
        let title = $(target).data('title');

        if(! title)
          title = 'Elemento';

        window.addSuccessNotification(title + ' caricato con successo');
      }
    },
    error: function(response)
    {
      let title = $(target).data('title');

      if(! title)
        title = 'Elemento';

      window.addDangerNotification('Caricamento di ' + title + ' interrotto');
      window.ibFetcherStopSpinning(target);      
    }
  });
}

window.ibFetcherExpand = function (target, warn = false)
{
  let url = new URL($(target).data('url'));

  url.searchParams.append('fullpagechart', true);

  let data = window.ibFetcherCollectData(target);

  for (var key in data)
    url.searchParams.append(key, data[key]);

  window.open(url);
}


window.ibInitializeFetcher = function (target)
{
  if($(target).data('loaded'))
    return ;

  window.ibFetcherFetch(target);
}

$(window).on('load', function ()
{
  $('.ibfetcher').each(function()
  {
    window.ibInitializeFetcher($(this));
  });

  $('.ibfetcherbuttons .refresh').on('click', function()
  {
    var container = $(this).parents('.ibfetcherbuttons');
    var id = $(container).data('id');
    let target = $('#' + id);

    window.ibFetcherFetch(target, true);
  });

  $('.ibfetcherbuttons .expand').on('click', function()
  {
    var container = $(this).parents('.ibfetcherbuttons');
    var id = $(container).data('id');
    let target = $('#' + id);

    window.ibFetcherExpand(target, true);
  });
})
</script>

@if(empty($iframed)&& Auth::id())
  {!! app('menu')->render() !!}
@endif

{{-- @if(empty($iframed)&& Auth::id())
    @include('navbar.navbar')
@endif
 --}}
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
</body>
</html>
