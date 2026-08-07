<?php

namespace IlBronza\UikitTemplate\Helpers;

use Illuminate\Routing\Route;
use RuntimeException;

class RouteTitleTranslator
{
	/**
	 * Package routes must declare routeTranslationPrefix (or ibPackage).
	 * Host / framework routes (login, home, ...) fall back to resources/lang/{locale}/routes.php.
	 */
	public static function getTranslationPrefix(?Route $route = null) : string
	{
		$route ??= request()->route();

		if (! $route)
			throw new RuntimeException('Cannot resolve route title translation prefix without a route.');

		if ($prefix = ($route->action['routeTranslationPrefix'] ?? null))
		{
			if (is_array($prefix))
				$prefix = end($prefix);

			if (! is_string($prefix) || $prefix === '')
				throw new RuntimeException('routeTranslationPrefix must resolve to a non-empty string on route [' . ($route->getName() ?? 'unnamed') . '].');

			return $prefix;
		}

		if ($package = ($route->action['ibPackage'] ?? null))
		{
			if (is_array($package))
				$package = end($package);

			if (! is_string($package) || ! method_exists($package, 'getRouteTranslationPrefix'))
				throw new RuntimeException('Route ibPackage must be a package class with getRouteTranslationPrefix().');

			return $package::getRouteTranslationPrefix();
		}

		return 'routes.';
	}

	public static function isPackageRoute(?Route $route = null) : bool
	{
		$route ??= request()->route();

		if (! $route)
			return false;

		return isset($route->action['routeTranslationPrefix'])
			|| isset($route->action['ibPackage']);
	}

	/**
	 * Historical lang files mash the first two route-name segments:
	 * ibProducts.orders.index → ibProductsorders.index
	 * Proper nested files keep the full dotted name (Vehicles).
	 */
	public static function candidateItems(string $routeName) : array
	{
		$candidates = [$routeName];

		$mashed = static::mashFirstTwoSegments($routeName);

		if ($mashed !== $routeName)
			$candidates[] = $mashed;

		return array_values(array_unique($candidates));
	}

	public static function mashFirstTwoSegments(string $routeName) : string
	{
		$parts = explode('.', $routeName);

		if (count($parts) < 2)
			return $routeName;

		$mashed = $parts[0] . $parts[1];
		$rest = array_slice($parts, 2);

		if ($rest === [])
			return $mashed;

		return $mashed . '.' . implode('.', $rest);
	}

	public static function resolve(string $routeName, array $replace = [], ?Route $route = null) : string
	{
		$route ??= request()->route();
		$prefix = static::getTranslationPrefix($route);
		$tried = [];

		foreach (static::candidateItems($routeName) as $item)
		{
			$key = $prefix . $item;
			$tried[] = $key;

			if (trans()->has($key))
				return trans($key, $replace);
		}

		// Host app routes: title is optional → app name.
		// Package routes: missing key is a design error → break.
		if (! static::isPackageRoute($route))
			return config('app.name');

		throw new RuntimeException(
			'Missing route title translation for route [' . $routeName . ']. Tried: ' . implode(', ', $tried)
		);
	}
}
