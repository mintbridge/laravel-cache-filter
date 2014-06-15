# Laravel Cache Filter

Often you have responses that don't change very often, this package lets you
store these responses in the cache and then return them automatically using
a filter the next time they are requested.

## Installation

Begin by installing this package through Composer.

```
{
    "require": {
		"mintbridge/laravel-cache-filter": "1.0.x"
	}
}
```

Then add the service provider to you app config.

```php

// app/config/app.php

'providers' => [
    '...',
    'MintBridge\LaravelCacheFilter\LaravelCacheFilterServiceProvider'
];
```

## Usage
The package provide 2 filters, these can be used with Laravels before and after route filters:

```php
Route::get('some-route', array(
	'before' => 'cache.response.fetch',
	'after' => 'cache.response.store',
	...
));
```

The fetch filter will check to see if the response has been cached and if so return it. The store filter takes the generated response and adds it to the cache ready for the next time it is requested.