<?php

function iFrameRoute(string $route, array $parameters = [])
{
	$parameters['iframed'] = true;

	return route($route, $parameters);
}

function iFrameUrl(string $url, array $parameters = [])
{
	$parameters['iframed'] = true;

	if(strpos($url, "?") === false)
		return $url . '?iframed=true';

	return $url . '&iframed=true';
}

function morime($variable = null)
{
	if(\Auth::id() == 1)
		mori($variable);
}

