<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

@include('uikittemplate::head')

<body class="{{ app('uikittemplate')->getBodyClass() }}">

@if((! defined('__ib_IFRAMED__'))&& Auth::id())
	{!! app('menu')->render() !!}
@endif


<div uk-height-viewport="min-height: 300">
	<div class="uk-margin-auto uk-width-2xlarge uk-position-center">
		<div class="uk-width-medium uk-margin-auto">
			<img class="uk-margin-bottom" width="100%" src="{{ config('mail.logo.path', config('menu.logo.path')) }}"
				 alt="{{ config('app.name') }}"/>
		</div>
		<div class="uk-width-xlarge uk-alert-danger" uk-alert>
			<div uk-grid class="uk-flex uk-flex-middle">
				@if($title ?? false)
				<div class="uk-width-small">
					<span class="uk-h3">{!! $title !!}</span><br/>
				</div>
				@endif

				<div class="uk-width-expand">
					@yield('message')
				</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>
