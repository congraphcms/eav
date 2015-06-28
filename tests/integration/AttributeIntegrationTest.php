<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));

class AttributeIntegrationTest extends Orchestra\Testbench\TestCase
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


		$this->app = $this->createApplication();

		$this->bus = $this->app->make('Illuminate\Contracts\Bus\Dispatcher');

	}

	public function tearDown()
	{
		// parent::tearDown();

		// $this->artisan('migrate:reset', [
		// 	'--database' => 'testbench',
		// 	// '--realpath' => realpath(__DIR__.'/../../migrations'),
		// ]);
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

		// $config = require(realpath(__DIR__.'/../../config/eav.php'));

		// $app['config']->set(
		// 	'Cookbook::eav', $config
		// );

		// var_dump('CONFIG SETTED');
	}

	protected function getPackageProviders($app)
	{
		return ['Cookbook\EAV\EAVServiceProvider'];
	}

	// public function testCreateAttribute()
	// {
	// 	$params = [
	// 		'code' => 'code',
	// 		'admin_label' => '',
	// 		'admin_notice' => 'admin notice',
	// 		'field_type' => 'text',
	// 		'localized' => false,
	// 		'default_value' => '',
	// 		'unique' => false,
	// 		'required' => false,
	// 		'filterable' => false,
	// 		'status' => 'user_defined'
	// 	];

	// 	// $response = $this->call('POST', '/attribute', $params);

	// 	// $this->assertResponseOk();

	// 	// $this->assertEquals('test', $response->getContent());
		
	// 	$request = \Illuminate\Http\Request::create('/', 'POST', $params);

	// 	try
	// 	{
	// 		$result = $this->bus->dispatch( new Cookbook\EAV\Commands\AttributeCreateCommand($request));

	// 		var_dump($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		var_dump($e->toArray());
	// 	}
		

	// }

	// public function testUpdateAttribute()
	// {
	// 	$params = [
	// 		'admin_notice' => 'admin notice 2',
	// 	];

	// 	// $response = $this->call('POST', '/attribute', $params);

	// 	// $this->assertResponseOk();

	// 	// $this->assertEquals('test', $response->getContent());
		
	// 	$request = \Illuminate\Http\Request::create('/', 'PUT', $params);

	// 	try
	// 	{
	// 		$result = $this->bus->dispatch( new Cookbook\EAV\Commands\AttributeUpdateCommand(3, $request));

	// 		var_dump($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		var_dump($e->toArray());
	// 	}
		

	// }

	public function testDeleteAttribute()
	{
		// $params = [
		// 	'admin_notice' => 'admin notice 2',
		// ];

		// $response = $this->call('POST', '/attribute', $params);

		// $this->assertResponseOk();

		// $this->assertEquals('test', $response->getContent());
		
		$request = \Illuminate\Http\Request::create('/', 'DELETE', []);

		try
		{
			$result = $this->bus->dispatch( new Cookbook\EAV\Commands\AttributeDeleteCommand(3));

			var_dump($result);
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			var_dump($e->toArray());
		}
		

	}
}