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

function mori($variable = null)
{
	echo json_encode($variable);

	die();
}

function morime($variable = null)
{
	if(\Auth::id() == 1)
		mori($variable);
}

function moriMethods($variable = null)
{
	echo json_encode(
		get_class_methods($variable)
	);

	die();
}

