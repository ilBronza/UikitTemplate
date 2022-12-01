<?php

function iFrameRoute(string $route, array $parameters = [])
{
	$parameters['iframed'] = true;

	return route($route, $parameters);
}

// function mori($variable = null)
// {
// 	echo htmlspecialchars(json_encode($variable));
// 	die();
// }

function morime($variable = null)
{
	if(\Auth::id() == 1)
		mori($variable);
}

