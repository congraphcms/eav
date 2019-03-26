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

class RelationFieldTest extends Orchestra\Testbench\TestCase
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
			'code' => 'relation_attribute',
			'field_type' => 'relation',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'options' => []
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('relation_attribute', $result->code);

	}

	public function testAllowedTypes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'relation_attribute',
			'field_type' => 'relation',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch(new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_object($result->data));
		$this->assertFalse($result->data->allowed_types);

		$params = [
            'code' => 'relation_attribute2',
            'field_type' => 'relation',
            'localized' => false,
            'unique' => false,
            'required' => false,
			'filterable' => true,
			'data' => [
				'allowed_types' => false
			]
        ];

        $app = $this->createApplication();
        $bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

        $result = $bus->dispatch(new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));

        // $this->d->dump($result->toArray());

        $this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
        $this->assertTrue(is_object($result->data));
		$this->assertFalse($result->data->allowed_types);

		$params = [
			'code' => 'relation_attribute3',
			'field_type' => 'relation',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'data' => [
				'allowed_types' => 1
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try {
			$result = $bus->dispatch(new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));
		} catch( ValidationException $e) {
			$this->d->dump($e->getErrors());
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
        $this->assertTrue(is_object($result->data));
		$this->assertTrue(is_array($result->data->allowed_types));
		$this->assertEquals(1, count($result->data->allowed_types));
        $this->assertEquals(1, $result->data->allowed_types[0]);

		$params = [
			'code' => 'relation_attribute4',
			'field_type' => 'relation',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'data' => [
				'allowed_types' => ['tests']
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try {
			$result = $bus->dispatch(new Congraph\Eav\Commands\Attributes\AttributeCreateCommand($params));
		} catch( ValidationException $e) {
			$this->d->dump($e->getErrors());
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
        $this->assertTrue(is_object($result->data));
		$this->assertTrue(is_array($result->data->allowed_types));
		$this->assertEquals(1, count($result->data->allowed_types));
        $this->assertEquals(1, $result->data->allowed_types[0]);

	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'relation_attribute_changed'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Attributes\AttributeUpdateCommand($params, 13) );

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'relation_attribute_changed');

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
				'test_relation_attribute' => ['id' => 2, 'type' => 'entity']
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

	}

	public function testCreateMultipleEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale' => 'en_US',
			'fields' => [
				'test_relation_collection_attribute' => [
					['id' => 2, 'type' => 'entity'],
					['id' => 1, 'type' => 'entity']
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityCreateCommand($params));
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertTrue(is_array($result->fields->test_relation_collection_attribute));
		$this->assertEquals(2, $result->fields->test_relation_collection_attribute[0]->id);
		$this->assertEquals(1, $result->fields->test_relation_collection_attribute[1]->id);
		$this->assertEquals('entity', $result->fields->test_relation_collection_attribute[0]->type);
		$this->assertEquals('entity', $result->fields->test_relation_collection_attribute[1]->type);

	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_relation_attribute' => ['id' => 2, 'type' => 'entity']
			]
		];

		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityUpdateCommand($params, 4));


		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(2, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
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

		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityFetchCommand(['include' => 'fields.test_relation_attribute'], 4));
		// $this->d->dump($result->toArray(true, true));
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals('tests', $result->toArray()['fields']['test_relation_attribute']['entity_type']);


		$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityFetchCommand(['include' => 'fields.test_relation_attribute', 'locale' => 'en_US'], 4));
		// $this->d->dump($result->toArray(true, true));
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals('tests', $result->toArray()['fields']['test_relation_attribute']['entity_type']);
	}

	// public function testFilterEntity()
	// {

	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => '11.1']]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['in' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['nin' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(0, count($result));

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['lt' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(0, count($result));

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['lte' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);


	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['gt' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(0, count($result));

	// 	$result = $bus->dispatch( new Congraph\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['gte' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);
	// }

}
