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

<script type="text/javascript">

window.ibFetcherFetch = function (target)
{
  console.log(target);
}

window.ibInitializeFetcher = function (target)
{
  let event = target.data('event', false);

  console.log(event);

  target.on('load', function()
  {
    alert('asd');
    $.ajax({
      url: target.data('url'),
      success: function (response)
      {
        target.html(response);
      }
    });
  })

  // if(event)
  //   target.on(event, window.ibFetcherFetch(target));

  // else
  //   window.ibFetcherFetch(target);
}

jQuery(document).ready(function($)
{
  $('.ibfetcher').each(function()
  {
    window.ibInitializeFetcher($(this));
  });
})
</script>

{!! app('menu')->render() !!}

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
