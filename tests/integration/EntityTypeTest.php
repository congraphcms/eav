<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

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

	public function testCreateEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'test-type',
			'endpoint' => 'test-types',
			'name' => 'Test Type',
			'plural_name' => 'Test Types',
			'workflow_id' => 1,
			'default_point_id' => 1,
			'localized_workflow' => 0,
			'multiple_sets' => 1
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeCreateCommand($params) );

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->d->dump($result->toArray());
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testCreateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => '',
			'name' => 'Test Type',
			'plural_name' => 'Test Types',
			'workflow_id' => 1,
			'default_point_id' => 1,
			'localized_workflow' => 0,
			'multiple_sets' => 1
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeCreateCommand($params) );
	}

	public function testUpdateEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'test_type_changed'
		];

		
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeUpdateCommand($params, 1) );
		
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test_type_changed', $result->code);
		$this->d->dump($result->toArray());
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testUpdateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => ''
		];

		
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeUpdateCommand($params, 1) );
	}


	public function testDeleteEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

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
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeDeleteCommand([], 133) );
	}

	public function testFetchEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeFetchCommand([], 1));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->id);
		$this->d->dump($result->toArray());
	}

	public function testFetchWithInclude()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeFetchCommand(['include' => 'workflow, default_point, attribute_sets.attributes'], 1));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->id);
		$this->assertEquals(3, count($result->attribute_sets));
		$this->d->dump($result->toArray());
		$arrayWithMeta = $result->toArray(true, false);
		$this->assertEquals(1, $arrayWithMeta['meta']['id']);
		$this->assertEquals('workflow, default_point, attribute_sets.attributes', $arrayWithMeta['meta']['include']);
		$this->assertEquals(12, count($arrayWithMeta['included']));

		$this->d->dump($arrayWithMeta);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\NotFoundException
	 */
	public function testFetchException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

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

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(4, count($result));
		$this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\EntityTypes\EntityTypeGetCommand(['sort' => ['-code'], 'limit' => 2, 'offset' => 1, 'include' => ['workflow', 'default_point', 'attribute_sets.attributes']]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(count($result), 2);

		$this->d->dump($result->toArray());
	}

}