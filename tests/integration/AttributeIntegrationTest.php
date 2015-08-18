<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Debug\Dumper;

class AttributeIntegrationTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		// fwrite(STDOUT, __METHOD__ . "\n");
		parent::setUp();
		// unset($this->app);
		// call migrations specific to our tests, e.g. to seed the db
		// the path option should be relative to the 'path.database'
		// path unless `--path` option is available.
		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../migrations'),
		]);

		$this->d = new Dumper();


		// $this->app = $this->createApplication();

		// $this->bus = $this->app->make('Illuminate\Contracts\Bus\Dispatcher');

	}

	public function tearDown()
	{
		// fwrite(STDOUT, __METHOD__ . "\n");
		// parent::tearDown();
		
		$this->artisan('migrate:reset');
		unset($this->app);
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

	public function testCreateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'code',
			// 'admin_label' => '123',
			// 'admin_notice' => 'admin notice',
			'field_type' => 'text',
			'localized' => false,
			'default_value' => '',
			'unique' => false,
			'required' => false,
			'filterable' => false,
			'status' => 'user_defined'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));

		$this->assertTrue(is_object($result));
		$this->assertTrue(is_int($result->id));
		$this->d->dump($result);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testCreateException()
	{
		$params = [
			// 'admin_label' => '123',
			// 'admin_notice' => 'admin notice',
			'field_type' => 'text',
			'localized' => false,
			'default_value' => '',
			'unique' => false,
			'required' => false,
			'filterable' => false,
			'status' => 'user_defined'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params) );
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'code',
			// 'admin_label' => '123',
			// 'admin_notice' => 'admin notice',
			'field_type' => 'text',
			'localized' => false,
			'default_value' => '',
			'unique' => false,
			'required' => false,
			'filterable' => false,
			'status' => 'user_defined'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$attribute = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params) );


		$params = [
			'code' => 'code2',
		];
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand($params, $attribute->id) );
		
		$this->assertTrue(is_object($result));
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->code, 'code2');
		$this->d->dump($result);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	// public function testUpdateException()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$params = [
	// 		'code' => 'code',
	// 		// 'admin_label' => '123',
	// 		// 'admin_notice' => 'admin notice',
	// 		'field_type' => 'text',
	// 		'localized' => false,
	// 		'default_value' => '',
	// 		'unique' => false,
	// 		'required' => false,
	// 		'filterable' => false,
	// 		'status' => 'user_defined'
	// 	];

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$attribute = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params) );

	// 	$params = [
	// 		'field_type' => 'text123'
	// 	];

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand($params, $attribute->id) );
	// }

	// public function testDeleteAttribute()
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
	// 		$result = $this->bus->dispatch( new Cookbook\Eav\Commands\AttributeDeleteCommand(3));

	// 		var_dump($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		var_dump($e->toArray());
	// 	}
		

	// }
	
	// public function testFetchAttribute()
	// {

	// 	try
	// 	{
	// 		$result = $this->bus->dispatch( new Cookbook\Eav\Commands\AttributeFetchCommand(2));

	// 		var_dump($result);
	// 	}
	// 	catch(\Cookbook\Core\Exceptions\ValidationException $e)
	// 	{
	// 		var_dump($e->toArray());
	// 	}
		

	// }

	
	public function testGetAttributes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		try
		{
			// $request = \Illuminate\Http\Request::create('/', 'GET', []);

			$app = $this->createApplication();
			$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand([]));

			$this->d->dump($result);
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$this->d->dump($e->toArray());
		}
		

	}

}