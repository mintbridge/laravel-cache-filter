<?php namespace MintBridge\LaravelCacheFilter;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelCacheFilterServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// TODO: Is there a better way to do this?
		Route::filter(
			'cache.response.fetch',
			'MintBridge\LaravelCacheFilter\ResponseCacheFilter@fetch'
		);
		Route::filter(
			'cache.response.store',
			'MintBridge\LaravelCacheFilter\ResponseCacheFilter@store'
		);
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('mintbridge/laravel-cache-filter');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
