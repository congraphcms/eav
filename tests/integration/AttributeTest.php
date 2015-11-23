<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class AttributeTest extends Orchestra\Testbench\TestCase
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
			'--realpath' => realpath(__DIR__.'/../../database/migrations'),
		]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/Cookbook/Filesystem/database/migrations'),
		]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/Cookbook/Locales/database/migrations'),
		]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/Cookbook/Workflows/database/migrations'),
		]);

		$this->artisan('db:seed', [
			'--class' => 'EavDbSeeder'
		]);

		$this->artisan('db:seed', [
			'--class' => 'LocaleDbSeeder'
		]);

		$this->artisan('db:seed', [
			'--class' => 'FileDbSeeder'
		]);

		$this->artisan('db:seed', [
			'--class' => 'WorkflowDbSeeder'
		]);

		$this->d = new Dumper();


		// $this->app = $this->createApplication();

		// $this->bus = $this->app->make('Illuminate\Contracts\Bus\Dispatcher');

	}

	public function tearDown()
	{
		// fwrite(STDOUT, __METHOD__ . "\n");
		// parent::tearDown();
		$this->artisan('db:seed', [
			'--class' => 'ClearDB'
		]);
		DB::disconnect();
		// $this->artisan('migrate:reset');
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
		return [
			'Cookbook\Core\CoreServiceProvider', 
			'Cookbook\Locales\LocalesServiceProvider', 
			'Cookbook\Eav\EavServiceProvider', 
			'Cookbook\Filesystem\FilesystemServiceProvider',
			'Cookbook\Workflows\WorkflowsServiceProvider'
		];
	}

	public function testCreateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'code',
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

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->d->dump($result->toArray());
	}

	public function testCreateWithOptions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'code',
			'field_type' => 'text',
			'localized' => false,
			'default_value' => '',
			'unique' => false,
			'required' => false,
			'filterable' => false,
			'status' => 'user_defined',
			'options' => [
				[
					'value' => 'option1',
					'label' => 'Option 1',
					'default' => true,
					'sort_order' => 0
				],
				[
					'value' => 'option2',
					'label' => 'Option 2',
					'default' => 0,
					'sort_order' => 2
				],
				[
					'value' => 'option3',
					'label' => 'Option 3',
					'default' => 0,
					'sort_order' => 1
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertTrue(is_array($result->options));
		$this->assertFalse(empty($result->options));
		$this->d->dump($result->toArray());
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testCreateException()
	{
		$params = [
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

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'code' => 'attribute_updated',
		];
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand($params, 1) );
		
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->code, 'attribute_updated');
		$this->d->dump($result->toArray());
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
			'code' => ''
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand($params, 1) );
	}

	public function testDeleteAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeDeleteCommand([], 1));

		$this->assertEquals($result, 1);
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

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeDeleteCommand([], 133));
	}
	
	public function testFetchAttribute()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeFetchCommand([], 1));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->code, 'attribute1');
		$this->d->dump($result->toArray());
		

	}

	
	public function testGetAttributes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand([]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(15, count($result));
		$this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['sort' => ['-code'], 'limit' => 3, 'offset' => 1]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		$arrayResult = $result->toArray();
		$this->d->dump($arrayResult);

		$arrayResultWithMeta = $result->toArray(true);
		$this->assertEquals(['-code'], $arrayResultWithMeta['meta']['sort']);
		$this->assertEquals(3, $arrayResultWithMeta['meta']['limit']);
		$this->assertEquals(1, $arrayResultWithMeta['meta']['offset']);
		$this->assertEquals([], $arrayResultWithMeta['meta']['filter']);
		$this->assertEquals([], $arrayResultWithMeta['meta']['include']);
		$this->assertEquals(3, $arrayResultWithMeta['meta']['count']);
		$this->assertEquals(15, $arrayResultWithMeta['meta']['total']);
		$this->d->dump($arrayResultWithMeta);
	}

	public function testGetFilters()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$filter = [ 'id' => 5 ];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->d->dump($result->toArray());
		
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));

		

		$filter = [ 'id' => ['in'=>'5,6,7'] ];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		$this->d->dump($result->toArray());

		$filter = [ 'id' => ['nin'=>[5,6,7,1]] ];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(11, count($result));

		$this->d->dump($result->toArray());

		$filter = [ 'id' => ['lt'=>3] ];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(2, count($result));

		$this->d->dump($result->toArray());

		$filter = [ 'id' => ['lte'=>3] ];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		$this->d->dump($result->toArray());

		$filter = [ 'id' => ['ne'=>3] ];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(14, count($result));

		$this->d->dump($result->toArray());
	}

}