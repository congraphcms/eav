<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Congraph\Core\Exceptions\ValidationException;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/SampleBlogCommandSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class SampleBlogTest extends Orchestra\Testbench\TestCase
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

		// $this->artisan('db:seed', [
		// 	'--class' => 'WorkflowDbSeeder'
		// ]);

		$this->artisan('db:seed', [
			'--class' => 'SampleBlogCommandSeeder'
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

	// public function testSeeder()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		
	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand([]));
		
		
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(3, count($result));
	// 	$this->d->dump($result->toArray());

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeGetCommand([]));
		
		
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(8, count($result));
	// 	$this->d->dump($result->toArray());

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand([]));
		
		
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(3, count($result));
	// 	$this->d->dump($result->toArray());

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand([]));
		
		
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(4, count($result));
	// 	$this->d->dump($result->toArray());

		
	// }

	public function testFullTextFieldSearch()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'filter' => [
				'fields.title' => [
					'm' => 'strana',
					'in' => 'Pocetna strana, Kontakt strana'
				]
			],
			'locale' => 'sr_RS'
		];
		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand($params));
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->d->dump($result->toArray());
	}

	public function testFullTextSearch()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'filter' => [
				's' => 'strana'
			],
			'locale' => 'sr_RS'
		];
		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand($params));
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->d->dump($result->toArray());
	}


	// /**
	//  * @expectedException \Congraph\Core\Exceptions\ValidationException
	//  */
	// public function testCreateException()
	// {
	// 	$params = [
	// 		'attribute_set' => ['id' => 1],
	// 		'locale_id' => 0,
	// 		'fields' => [
	// 			'attribute1' => '',
	// 			'attribute2' => ''
	// 		]
	// 	];

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityCreateCommand($params));
	// }

	// public function testUpdateEntity()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$params = [
	// 		'fields' => [
	// 			'attribute3' => [
	// 				'fr_FR' => 'changed value'
	// 			]
	// 		]
	// 	];
		
	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityUpdateCommand($params, 1));
		
		
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
	// 	$this->assertTrue(is_int($result->id));
	// 	$this->assertEquals($result->fields->attribute3['fr_FR'], 'changed value');
	// 	$this->d->dump($result->toArray());
	// }

	// /**
	//  * @expectedException \Congraph\Core\Exceptions\ValidationException
	//  */
	// public function testUpdateException()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$params = [
	// 		'fields' => [
	// 			'attribute1' => ''
	// 		]
	// 	];
		
	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityUpdateCommand($params, 1));
	// }

	// public function testDeleteEntity()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityDeleteCommand([], 1));

	// 	$this->assertEquals(1, $result);
	// 	$this->d->dump($result);
		

	// }

	// /**
	//  * @expectedException \Congraph\Core\Exceptions\NotFoundException
	//  */
	// public function testDeleteException()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityDeleteCommand([], 133));
	// }
	
	// public function testFetchEntity()
	// {

	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityFetchCommand(['locale' => 'en_US'], 1));
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
	// 	$this->assertTrue(is_int($result->id));
	// 	$this->d->dump($result->toArray());
		

	// }

	
	// public function testGetEntities()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand([]));

	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(4, count($result));
	// 	$this->d->dump($result->toArray());

	// }

	// public function testGetParams()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['locale' => 'en_US', 'sort' => ['fields.attribute3'], 'limit' => 3, 'offset' => 0]));

	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(3, count($result));

	// 	$this->d->dump($result->toArray());
	// }

	// public function testGetFilters()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$filter = [ 'fields.attribute3' => 'value3-en' ];

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => $filter, 'locale' => 'en_US', 'sort' => ['fields.attribute1']]));
		
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));

	// 	$this->d->dump($result->toArray());

	// }

}