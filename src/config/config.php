<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Cache time to live
	|--------------------------------------------------------------------------
	|
	| Determines how long the cached version is kept around in minutes. It's
	| passed through to the Cache put method so can support Carbon objects,
	| see http://laravel.com/docs/cache#cache-usage
	|
	| Set to false to disable caching
	*/

	'ttl' => 60,

	/*
	|--------------------------------------------------------------------------
	| Cache key prefix
	|--------------------------------------------------------------------------
	|
	| The prefix that is used when generating cache keys
	|
	*/

	'prefix' => 'response-'

);
