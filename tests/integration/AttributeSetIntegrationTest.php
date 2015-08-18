<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Illuminate\Support\Facades\Cache;
class AttributeSetIntegrationTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		parent::setUp();

		// call migrations specific to our tests, e.g. to seed the db
		// the path option should be relative to the 'path.database'
		// path unless `--path` option is available.
		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../migrations'),
		]);

		// $this->app = $this->createApplication();

		$this->bus = $this->app->make('Illuminate\Contracts\Bus\Dispatcher');

	}

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 *
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   	=> 'mysql',
			'host'      => '127.0.0.1',
			'port'		=> '33060',
			'database'	=> 'cookbook_testbench',
			'username'  => 'homestead',
			'password'  => 'secret',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		]);

		$app['config']->set('cache.default', 'file');

		$app['config']->set('cache.stores.file', [
			'driver'	=> 'file',
			'path'   	=> realpath(__DIR__ . '/../storage/cache/'),
		]);

		// $config = require(realpath(__DIR__.'/../../config/eav.php'));

		// $app['config']->set(
		// 	'Cookbook::eav', $config
		// );

		// var_dump('CONFIG SETTED');
	}

	protected function getPackageProviders($app)
	{
		return ['Cookbook\Eav\EavServiceProvider'];
	}

	// public function testCreateAttributeSet()
	// {
	// 	$params = [
	// 		'code' => 'test-1attr-set',
	// 		'name' => 'Test Attr Set',
	// 		'entity_type_id' => 1,
	// 		'attributes' => [
	// 			['id' => 1],
	// 			['id' => 2]
	// 		]
	// 	];

	// 	// $response = $this->call('POST', '/attribute', $params);

	// 	// $this->assertResponseOk();

	// 	// $this->assertEquals('test', $response->getContent());
		
	// 	$request = \Illuminate\Http\Request::create('/', 'POST', $params);

	// 	try
	// 	{
	// 		$result = $this->bus->dispatch( new Cookbook\Eav\Commands\AttributeSets\AttributeSetCreateCommand($request));

	// 		dd($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		// var_dump($e);
	// 		dd($e->toArray());
	// 	}
		

	// }

	// public function testUpdateAttributeSet()
	// {
	// 	$params = [
	// 		'code' => 'test-1attr-set',
	// 		'name' => 'Test Attr 2 Set',
	// 		'attributes' => [
	// 			['id' => 1]
	// 		]
	// 	];

	// 	// $response = $this->call('POST', '/attribute', $params);

	// 	// $this->assertResponseOk();

	// 	// $this->assertEquals('test', $response->getContent());
		
	// 	$request = \Illuminate\Http\Request::create('/', 'PUT', $params);

	// 	try
	// 	{
	// 		$result = $this->bus->dispatch( new Cookbook\Eav\Commands\AttributeSets\AttributeSetUpdateCommand(2, $request));

	// 		dd($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		dd($e->toArray());
	// 	}
		

	// }

	// public function testDeleteAttributeSet()
	// {
	// 	// $params = [
	// 	// 	'admin_notice' => 'admin notice 2',
	// 	// ];

	// 	// $response = $this->call('POST', '/attribute', $params);

	// 	// $this->assertResponseOk();

	// 	// $this->assertEquals('test', $response->getContent());
		
	// 	$request = \Illuminate\Http\Request::create('/', 'DELETE', []);

	// 	try
	// 	{
	// 		$result = $this->bus->dispatch( new Cookbook\Eav\Commands\AttributeSets\AttributeSetDeleteCommand(2));

	// 		dd($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		dd($e->toArray());
	// 	}
		

	// }
	
	// public function testFetchAttribute()
	// {

	// 	try
	// 	{
	// 		$request = \Illuminate\Http\Request::create('/', 'GET', []);

	// 		$result = $this->bus->dispatch( new Cookbook\Eav\Commands\AttributeSets\AttributeSetFetchCommand(17, $request));

	// 		dd($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		dd($e->toArray());
	// 	}
		

	// }

	public function testGetAttributeSets()
	{

		try
		{
			// $request = \Illuminate\Http\Request::create('/', 'GET', []);

			$result = $this->bus->dispatch( new Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand([]));

			dd($result);
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			dd($e->toArray());
		}
		

	}
}