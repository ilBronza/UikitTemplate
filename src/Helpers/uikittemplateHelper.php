<?php

function iFrameRoute(string $route, array $parameters = [])
{
	$parameters['iframed'] = true;

	return route($route, $parameters);
}

function morime($variable = null)
{
	if(\Auth::id() == 1)
		mori($variable);
}

