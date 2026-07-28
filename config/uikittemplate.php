<?php

return [
	// 'theme' => env('APP_UIKITTHEME', 'own'),

	'version' => '1.0.1',

	'logo' => env('APP_LOGO'),

	// Controls the footer event log used by CRUD broadcast updates.
	// When disabled, the component and its browser listeners are not rendered.
	'show_broadcast_updates' => env('APP_UIKIT_SHOW_BROADCAST_UPDATES', true),

//	'theme' => env('APP_UIKITTHEME', 'yoosomething'),
//  'theme' => env('APP_UIKITTHEME', 'flow'),
//	'theme' => env('APP_UIKITTHEME', 'designescapes'),
//	'theme' => env('APP_UIKITTHEME', 'makai'),
//	'theme' => env('APP_UIKITTHEME', 'vision'),
//	'theme' => env('APP_UIKITTHEME', 'vibe'),
	'theme' => env('APP_UIKITTHEME', 'max'),

	'pdf' => [
		'theme' => env('APP_UIKITTHEME', 'yoosomething')
	]

];
