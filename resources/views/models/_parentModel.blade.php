@if (view()->exists($parentModel->getRouteBasename() . '_teaser', [
	$parentModel->getRouteClassname() => $parentModel
	]))
@else
	@isset($parentModelTeaser)
		@include($parentModelTeaser['view'], $parentModelTeaser['parameters'])
	@endisset
@endif