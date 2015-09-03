<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Debug\Dumper;

class EntityTypeTest extends Orchestra\Testbench\TestCase
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

		$this->artisan('db:seed', [
			'--class' => 'Cookbook\Eav\Seeders\TestDbSeeder'
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
		// unset($this->app);

		parent::tearDown();
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

	public function testCreateEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'test-type',
			'name' => 'Test Type',
			'plural_name' => 'Test Types',
			'multiple_sets' => 1
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeCreateCommand($params) );

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
			'code' => '',
			'name' => 'Test Type',
			'plural_name' => 'Test Types',
			'multiple_sets' => 1
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeCreateCommand($params) );
	}

	public function testUpdateEntityType()
	{
		$params = [
			'code' => 'test_type_changed'
		];

		
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeUpdateCommand($params, 1) );
		
		$this->assertTrue(is_object($result));
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test_type_changed', $result->code);
		$this->d->dump($result);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testUpdateException()
	{
		$params = [
			'code' => ''
		];

		
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeUpdateCommand($params, 1) );
	}


	public function testDeleteEntityType()
	{

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeDeleteCommand([], 1) );

		$this->assertEquals($result, 1);
		$this->d->dump($result);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\NotFoundException
	 */
	public function testDeleteException()
	{
		
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeDeleteCommand([], 133) );
	}

	public function testFetchEntityType()
	{
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeFetchCommand([], 1));

		$this->assertTrue(is_object($result));
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->id);
		$this->d->dump($result);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\NotFoundException
	 */
	public function testFetchException()
	{
		
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeFetchCommand([], 133));
	}

	public function testGetEntityTypes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeGetCommand([]));

		$this->assertTrue(is_array($result));
		$this->assertEquals(count($result), 3);
		$this->d->dump($result);

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeGetCommand(['sort' => ['-code'], 'limit' => 2, 'offset' => 1]));

		$this->assertTrue(is_array($result));
		$this->assertEquals(count($result), 2);

		$this->d->dump($result);
	}

}