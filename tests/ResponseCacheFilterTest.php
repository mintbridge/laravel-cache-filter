<?php namespace MintBridge\LaravelCacheFilter;

use Mockery as m;

class ResponseCacheFilterTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->cache  = m::mock('Illuminate\Cache\Repository');
		$this->config = m::mock('Illuminate\Config\Repository');
	}

	public function tearDown()
	{
		m::close();
	}

	/**
	 * @dataProvider urlDataProvider
	 */
	public function test_key_generated_from_url($url, $key)
	{
		$request = m::mock('Illuminate\Http\Request');
		$request->shouldReceive('url')
			->once()
			->andReturn($url);

		$filter = new ResponseCacheFilter($this->cache, $this->config);

		$result = $filter->makeCacheKey($request);

		$this->assertEquals($key, $result);
	}

	public function urlDataProvider()
	{
		return array(
			array(
				'http://www.google.com',
				'response-httpwwwgooglecom'
			),
			array(
				'www.google.com',
				'response-wwwgooglecom'
			),
			array(
				'http://www.mintbridge.co.uk/some/path',
				'response-httpwwwmintbridgecouksomepath'
			),
			array(
				'http://www.mintbridge.co.uk/another/path with spaces',
				'response-httpwwwmintbridgecoukanotherpath-with-spaces'
			)
		);
	}

	public function test_fetching_response_from_cache()
	{
		$route = m::mock('Illuminate\Routing\Route');

		$request = m::mock('Illuminate\Http\Request');
		$request->shouldReceive('url')
			->once()
			->andReturn('http://www.mintbridge.co.uk/some/path');

		$this->cache->shouldReceive('has')
			->once()
			->andReturn(true);

		$this->cache->shouldReceive('get')
			->once()
			->andReturn(array(
			'content'    => 'content',
			'headers'    => 'headers',
			'version'    => 'version',
			'statusCode' => 200,
			'charset'    => 'charset'
		));

		$filter = new ResponseCacheFilter($this->cache, $this->config);

		$response = $filter->fetch($route, $request);

		$this->assertEquals('content', $response->getContent());
		$this->assertEquals('headers', $response->headers);
		$this->assertEquals('version', $response->getProtocolVersion());
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals('charset', $response->getCharset());
	}

	public function test_storing_a_response_in_the_cache()
	{
		$route = m::mock('Illuminate\Routing\Route');

		$request = m::mock('Illuminate\Http\Request');
		$request->shouldReceive('url')
			->once()
			->andReturn('http://www.mintbridge.co.uk/some/path');

		$response = \Symfony\Component\HttpFoundation\Response::create('content', 200);
		$response->headers = 'headers';
		$response->setCharset('charset');
		$response->setProtocolVersion('version');

		$this->cache->shouldReceive('has')
			->once()
			->andReturn(false);

		$this->cache->shouldReceive('put')
			->once()
			->with(
				'response-httpwwwmintbridgecouksomepath',
				array(
					'content'    => 'content',
					'headers'    => 'headers',
					'version'    => 'version',
					'statusCode' => 200,
					'charset'    => 'charset'
				),
				60
			)
			->andReturn();

		$filter = new ResponseCacheFilter($this->cache, $this->config);
		$filter->store($route, $request, $response);
	}

	public function test_serializing_a_response()
	{
		$data = array(
			'content'    => 'content',
			'headers'    => 'headers',
			'version'    => 'version',
			'statusCode' => 200,
			'charset'    => 'charset'
		);
		$request = m::mock('Symfony\Component\HttpFoundation\Response');
		$request->headers = $data['headers'];
		$request->shouldReceive('getContent')
			->once()
			->andReturn($data['content']);
		$request->shouldReceive('getProtocolVersion')
			->once()
			->andReturn($data['version']);
		$request->shouldReceive('getStatusCode')
			->once()
			->andReturn($data['statusCode']);
		$request->shouldReceive('getCharset')
			->once()
			->andReturn($data['charset']);

		$filter = new ResponseCacheFilter($this->cache, $this->config);

		$result = $filter->serializeResponse($request);

		$this->assertEquals($data, $result);
	}
}