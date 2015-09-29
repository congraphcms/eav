<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Cookbook\Core\Exceptions\ValidationException;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Cache;

class EntityTest extends Orchestra\Testbench\TestCase
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

	public function testCreateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'type' => 'tests',
			'attribute_set' => ['id' => 1],
			'locale_id' => 0,
			'fields' => [
				'attribute1' => '234',
				'attribute2' => ''
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));

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
			'type' => '',
			'attribute_set' => ['id' => 1],
			'locale_id' => 0,
			'fields' => [
				'attribute1' => '',
				'attribute2' => ''
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
	}

	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'locale_id' => 0,
			'fields' => [
				'attribute1' => 'changed value'
			]
		];
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, 1));
		
		$this->d->dump($result);
		$this->assertTrue(is_object($result));
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->fields->attribute1, 'changed value');
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testUpdateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'locale_id' => 0,
			'fields' => [
				'attribute1' => ''
			]
		];
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, 1));
	}

	public function testDeleteEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityDeleteCommand([], 1));

		$this->assertEquals(1, $result);
		$this->d->dump($result);
		

	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\NotFoundException
	 */
	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityDeleteCommand([], 133));
	}
	
	public function testFetchEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand([], 1));
		$this->assertTrue(is_object($result));
		$this->assertTrue(is_int($result->id));
		$this->d->dump($result);
		

	}

	
	public function testGetEntities()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand([]));

		$this->assertTrue(is_array($result));
		$this->assertEquals(count($result), 3);
		$this->d->dump($result);

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['sort' => ['fields.attribute3'], 'limit' => 3, 'offset' => 0]));

		$this->assertTrue(is_array($result));
		$this->assertEquals(3, count($result));

		$this->d->dump($result);
	}

	public function testGetFilters()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$filter = [ 'fields.attribute1' => ['in' => ['value12','value1']] ];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => $filter, 'sort' => ['fields.attribute3']]));

		$this->d->dump($result);
		
		$this->assertTrue(is_array($result));
		$this->assertEquals(2, count($result));

		

		// $filter = [ 'id' => ['in'=>'5,6,7'] ];

		// $result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		// $this->assertTrue(is_array($result));
		// $this->assertEquals(3, count($result));

		// $this->d->dump($result);

		// $filter = [ 'id' => ['nin'=>[5,6,7,1]] ];

		// $result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		// $this->assertTrue(is_array($result));
		// $this->assertEquals(3, count($result));

		// $this->d->dump($result);

		// $filter = [ 'id' => ['lt'=>3] ];

		// $result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		// $this->assertTrue(is_array($result));
		// $this->assertEquals(2, count($result));

		// $this->d->dump($result);

		// $filter = [ 'id' => ['lte'=>3] ];

		// $result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		// $this->assertTrue(is_array($result));
		// $this->assertEquals(3, count($result));

		// $this->d->dump($result);

		// $filter = [ 'id' => ['ne'=>3] ];

		// $result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		// $this->assertTrue(is_array($result));
		// $this->assertEquals(6, count($result));

		// $this->d->dump($result);
	}

}