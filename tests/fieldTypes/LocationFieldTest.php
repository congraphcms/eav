<?php

use Congraph\Core\Exceptions\ValidationException;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class LocationFieldTest extends Orchestra\Testbench\TestCase
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
			'code' => 'location_attribute',
			'field_type' => 'location',
			'localized' => false,
			'required' => false
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('location_attribute', $result->code);

	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'location_attribute_changed'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeUpdateCommand($params, 16) );

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'location_attribute_changed');

	}

	public function testCreateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale' => 'en_US',
			'fields' => [
				'test_text_attribute' => 'test value',
				'test_select_attribute' => 'option2',
				'test_integer_attribute' => 123,
				'test_decimal_attribute' => 33.33,
				'test_datetime_attribute' => '1987-08-19T11:00:00+0200',
				'test_relation_attribute' => ['id' => 2, 'type' => 'entity'],
				'test_location_attribute' => ['lat' => 33, 'lng' => 44]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityCreateCommand($params));
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test value', $result->fields->test_text_attribute);
		$this->assertEquals('option2', $result->fields->test_select_attribute);
		$this->assertEquals(123, $result->fields->test_integer_attribute);
		$this->assertEquals(33.33, $result->fields->test_decimal_attribute);
		$this->assertEquals('1987-08-19T09:00:00+00:00', $result->toArray()['fields']['test_datetime_attribute']);
		$this->assertEquals(2, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertTrue(is_object($result->fields->test_location_attribute));

		$this->assertEquals(33, $result->fields->test_location_attribute->lat);
		$this->assertEquals(44, $result->fields->test_location_attribute->lng);

	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_location_attribute' => ['lat' => 33, 'lng' => 44]
			]
		];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityUpdateCommand($params, 4));


		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);

		$this->assertTrue(is_object($result->fields->test_location_attribute));
		$this->assertEquals(33, $result->fields->test_location_attribute->lat);
		$this->assertEquals(44, $result->fields->test_location_attribute->lng);

		// $this->d->dump($result->toArray());
	}

	public function testFetchEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityFetchCommand([], 4));
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertTrue(is_object($result->fields->test_location_attribute));
		$this->assertEquals(12, $result->fields->test_location_attribute->lat);
		$this->assertEquals(35, $result->fields->test_location_attribute->lng);
	}

}
