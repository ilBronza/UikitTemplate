@if(isset($checkConcurrentUri) && ($concurrentUri = app()->get('concurrentUriChecker')))

<div id="ib-concurrent-uri">
	
	@if(! $concurrentUri->userIsAllowed())

		<div class="uk-alert uk-alert-danger" uk-alert>
			<a class="uk-alert-close" uk-close></a>
			<p>@lang('crud::crud.aDifferentUserIsWatchingThisPage')</p>
		</div>

	@elseif($concurrentUri->isMultipleOpening())

		<div class="uk-alert uk-alert-warning" uk-alert>
			<a class="uk-alert-close" uk-close></a>
			<p>@lang('crud::crud.youAlreadyOpenedThisUrlOnADifferentPage')</p>
		</div>

	@else

		<div class="uk-alert uk-alert-success" uk-alert>
			<a class="uk-alert-close" uk-close></a>
			<p>@lang('crud::crud.youOwnThisUri')</p>
		</div>

	@endif

</div>

<h1>{{ $concurrentUri->getPrevalentPageKey() }}</h1>

@endif