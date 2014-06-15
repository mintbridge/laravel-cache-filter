<?php namespace MintBridge\LaravelCacheFilter;

use Illuminate\Cache\Repository as Cache;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResponseCacheFilter {

	/**
	 * The cache implementation.
	 *
	 * @var \Illuminate\Config\Repository
	 */
	private $cache;

	/**
	 * The cache key prefix.
	 *
	 * @var string
	 */
	private $prefix = 'response-';

	/**
	 * The cache time to live.
	 *
	 * @var integer
	 */
	private $ttl = 60;

	/**
	 * Create a new response cache filter.
	 *
	 * @param  \Illuminate\Cache\Repository  $cache
	 * @param  \Illuminate\Config\Repository  $cache
	 * @return void
	 */
	public function __construct(Cache $cache, Config $config)
	{
		$this->cache  = $cache;
		// TODO: fix package config
		// $this->prefix = $config->get('laravel-cache-filter::prefix', $this->prefix);
		// $this->ttl = $config->get('laravel-cache-filter::ttl', $this->ttl);
	}

	/**
	 * Find (if it exists) and return the cached response
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \Illuminate\Http\Request   $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function fetch(Route $route, Request $request)
	{
		if ($this->ttl) {

			$key = $this->makeCacheKey($request);

			if ($this->cache->has($key)) {
				$response = $this->unserializeResponse($this->cache->get($key));

				return $response;
			}
		}
	}

	/**
	 * Cache the passed response
	 *
	 * @param  \Illuminate\Routing\Route                    $route
	 * @param  \Illuminate\Http\Request                     $request
	 * @param  \Symfony\Component\HttpFoundation\Response   $response
	 *
	 * @return void
	 */
	public function store(Route $route, Request $request, Response $response)
	{
		if ($ttl = $this->ttl) {

			$key = $this->makeCacheKey($request);

			if ( ! $this->cache->has($key)) {
				$cacheable = $this->serializeResponse($response);

				$this->cache->put($key, $cacheable, $ttl);
			}
		}
	}

	/**
	 * Generate the cache key from the request url
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return string
	 */
	public function makeCacheKey(Request $request)
	{
		return $this->prefix.Str::slug($request->url());
	}

	/**
	 * Serialize the response object ready for caching
	 *
	 * @param  \Symfony\Component\HttpFoundation\Response   $response
	 *
	 * @return string
	 */
	public function serializeResponse(Response $response)
	{
		return array(
			'content'    => $response->getContent(),
			'headers'    => $response->headers,
			'version'    => $response->getProtocolVersion(),
			'statusCode' => $response->getStatusCode(),
			'charset'    => $response->getCharset()
		);
	}

	/**
	 * Unserialize the cached response
	 *
	 * @param  string  $cacheable
	 *
	 * @return \Symfony\Component\HttpFoundation\Response   $response
	 */
	public function unserializeResponse($cacheable)
	{
		$response = Response::create($cacheable['content'], $cacheable['statusCode']);

		$response->headers = $cacheable['headers'];
		$response->setCharset($cacheable['charset']);
		$response->setProtocolVersion($cacheable['version']);

		return $response;
	}
}
