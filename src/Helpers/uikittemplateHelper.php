<?php

function iFrameRoute(string $route, array $parameters = [])
{
	$parameters['iframed'] = true;

	return route($route, $parameters);
}

function iFrameUrl(string $url, array $parameters = [])
{
	$parameters['iframed'] = true;

	if (strpos($url, "?") === false)
		return $url . '?iframed=true';

	return $url . '&iframed=true';
}

function ff($variable)
{
	try
	{
		$methods = get_class_methods($variable);
	}
	catch(\Throwable $e)
	{
		$methods = [];
	}

	sort($methods);

	return dd([
		$variable,
		$methods
	]);
}

function mm($variable)
{
	$methods = get_class_methods($variable);

	if (config('app.debug') == false)
		return dd($methods);

	return dd([$methods, debug_backtrace()]);
}

function mmm($variable)
{
	$methods = get_class_methods($variable);

	if (config('app.debug') == false)
		return mori($methods);

	return mori([$methods, debug_backtrace()]);
}

function gcm($object)
{
	return mori(get_class_methods($object));
}

function mori($variable = null)
{
	echo json_encode($variable);

	die();
}

function morime($variable = null)
{
	if (Auth::id() == 1)
		dd($variable);
}

function moriMethods($variable = null)
{
	echo json_encode(
		get_class_methods($variable)
	);

	die();
}

