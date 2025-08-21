<?php

use Symfony\Component\VarDumper\Caster\ScalarStub;
use Symfony\Component\VarDumper\VarDumper;

if (! function_exists('class_uses_recursive'))
{
	function class_uses_recursive($class) : array
	{
		if (is_object($class))
		{
			$class = get_class($class);
		}

		$results = [];

		do
		{
			$results += trait_uses_recursive($class);
		} while ($class = get_parent_class($class));

		return array_unique($results);
	}

	function trait_uses_recursive($trait) : array
	{
		$traits = class_uses($trait) ? : [];

		foreach ($traits as $t)
		{
			$traits += trait_uses_recursive($t);
		}

		return $traits;
	}
}

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

function ff(mixed ...$vars) : never
{
	if (! in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) && ! headers_sent())
	{
		header('HTTP/1.1 500 Internal Server Error');
	}

	if (! $vars)
	{
		VarDumper::dump(new ScalarStub('🐛'));

		exit(1);
	}

	if (array_key_exists(0, $vars) && 1 === count($vars))
	{
		VarDumper::dump(get_class_methods($vars[0]));
	}
	else
	{
		foreach ($vars as $k => $v)
		{
			VarDumper::dump($v, is_int($k) ? 1 + $k : $k);
		}
	}

	exit(1);
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

