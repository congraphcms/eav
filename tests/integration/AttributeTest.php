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
			'--realpath' => realpath(__DIR__.'/../../vendor/Congraph/Filesystem/database/migrations'),
		]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/Congraph/Locales/database/migrations'),
		]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/Congraph/Workflows/database/migrations'),
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
			'port'		=> '3306',
			'database'	=> 'congraph_testbench',
			'username'  => 'root',
			'password'  => '',
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
		// 	'Congraph::eav', $config
		// );

		// var_dump('CONFIG SETTED');
	}

	protected function getPackageProviders($app)
	{
		return [
			'Congraph\Core\CoreServiceProvider', 
			'Congraph\Locales\LocalesServiceProvider', 
			'Congraph\Eav\EavServiceProvider', 
			'Congraph\Filesystem\FilesystemServiceProvider',
			'Congraph\Workflows\WorkflowsServiceProvider'
		];
	}

	public function testCreateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'code',
			'admin_label' => 'Code',
			'admin_notice' => 'Enter code here.',
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

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->d->dump($result->toArray());
	}

	public function testCreateWithOptions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'code',
			'field_type' => 'select',
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

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertTrue(is_array($result->options));
		$this->assertFalse(empty($result->options));
		// $this->d->dump($result->toArray());
	}

	/**
	 * @expectedException \Congraph\Core\Exceptions\ValidationException
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

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params) );
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			// 'code' => 'attribute_updated',
			'admin_label' => 'changed',
			'admin_notice' => 'changed_notice'
		];
		
		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeUpdateCommand($params, 1) );
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->assertEquals('attribute_updated', $result->code);
		$this->assertEquals('changed', $result->admin_label);
		$this->assertEquals('changed_notice', $result->admin_notice);

		// $this->d->dump($result->toArray());
	}

	public function testUpdateSameCode()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'code' => 'attribute1',
			'admin_label' => 'changed',
			'admin_notice' => 'changed_notice'
		];
		
		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeUpdateCommand($params, 1) );
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('attribute1', $result->code);
		$this->assertEquals('changed', $result->admin_label);
		$this->assertEquals('changed_notice', $result->admin_notice);

		// $this->d->dump($result->toArray());
	}

	public function testDeleteAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		try
		{
			$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeDeleteCommand([], 2));
		}
		catch (\Exception $e)
		{

		}

		$this->assertEquals(2, $result->id);
		// $this->d->dump($result);
		

	}

	/**
	 * @expectedException \Congraph\Core\Exceptions\NotFoundException
	 */
	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeDeleteCommand([], 133));
	}
	
	public function testFetchAttribute()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeFetchCommand([], 1));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->code, 'attribute1');
		// $this->d->dump($result->toArray());
		

	}

	
	public function testGetAttributes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand([]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(16, count($result));
		// $this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand(['sort' => ['-code'], 'limit' => 3, 'offset' => 1]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		$arrayResult = $result->toArray();
		// $this->d->dump($arrayResult);

		$arrayResultWithMeta = $result->toArray(true);
		$this->assertEquals(['-code'], $arrayResultWithMeta['meta']['sort']);
		$this->assertEquals(3, $arrayResultWithMeta['meta']['limit']);
		$this->assertEquals(1, $arrayResultWithMeta['meta']['offset']);
		$this->assertEquals([], $arrayResultWithMeta['meta']['filter']);
		$this->assertEquals([], $arrayResultWithMeta['meta']['include']);
		$this->assertEquals(3, $arrayResultWithMeta['meta']['count']);
		// $this->assertEquals(16, $arrayResultWithMeta['meta']['total']);
		// $this->d->dump($arrayResultWithMeta);
	}

	public function testGetFilters()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$filter = [ 'id' => 5 ];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		// $this->d->dump($result->toArray());
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));

		

		$filter = [ 'id' => ['in'=>'5,6,7'] ];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['nin'=>[5,6,7,1]] ];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(12, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['lt'=>3] ];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(2, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['lte'=>3] ];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['ne'=>3] ];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand(['filter' => $filter]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(15, count($result));

		// $this->d->dump($result->toArray());
	}

}