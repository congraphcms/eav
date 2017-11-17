<?php

use Cookbook\Core\Exceptions\ValidationException;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class CompundFieldTest extends Orchestra\Testbench\TestCase
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
			'port'		=> '3306',
			'database'	=> 'cookbook_testbench',
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
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'literal',
						'value' => 'hello'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation exception thrown.');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('compound_attribute', $result->code);
		$this->assertEquals('compound', $result->field_type);

	}

	public function testCreateLocalizedAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'literal',
						'value' => 'hello'
					],
					[
						'type' => 'operator',
						'value' => 'CONCAT'
					],
					[
						'type' => 'field',
						'value' => 21
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation exception thrown.');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('compound_attribute', $result->code);
		$this->assertEquals('compound', $result->field_type);
		$this->assertEquals(1, $result->localized);
	}

	public function testCreateAttributeFail()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		// test no data
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => []
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.expected_value" => "You need to specify an expected value type.",
				"data.inputs" => "You need to specify inputs for compound field."
			], $errors);
		}

		// test invalid expected value
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'invalid',
				'inputs' => [
					[
						'type' => 'literal',
						'value' => 'test'
					],
					[
						'type' => 'operator',
						'value' => 'CONCAT'
					],
					[
						'type' => 'field',
						'value' => 1
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.expected_value" => 'Invalid expected_value type: \'invalid\'.'
			], $errors);
		}

		// test invalid inputs
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => 'test'
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Inputs data needs to be an array.'
			], $errors);
		}

		// test empty inputs
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => []
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'You need to have at least one input.'
			], $errors);
		}

		// test invalid inputs array
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					'field' => 'not good'
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Every inputs needs to be defined as an array.'
			], $errors);
		}

		// test invalid input (no type)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'value' => 'test'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Input needs to have a type.'
			], $errors);
		}

		// test invalid input (invalid type)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'invalid',
						'value' => 'test'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs.type" => 'Invalid input type: \'invalid\'.'
			], $errors);
		}

		// test invalid input (no value)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'field',
						// 'value' => 'test'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Input needs to have a value.'
			], $errors);
		}

		// test invalid input (invalid field value)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'field',
						'value' => 'invalid'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs.value" => 'Invalid input value (unknown field id).'
			], $errors);
		}


		// test invalid input (invalid operator value)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'operator',
						'value' => 'ADD'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs.value" => 'Invalid input value (unknown operator).'
			], $errors);
		}

		// test invalid input order (start with operator)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'operator',
						'value' => 'CONCAT'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Inputs can\'t start or finish with operator.'
			], $errors);
		}

		// test invalid input order (finish with operator)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'literal',
						'value' => 'test'
					],
					[
						'type' => 'operator',
						'value' => 'CONCAT'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Inputs can\'t start or finish with operator.'
			], $errors);
		}

		// test invalid input order (two operators together)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'literal',
						'value' => 'test'
					],
					[
						'type' => 'operator',
						'value' => 'CONCAT'
					],
					[
						'type' => 'operator',
						'value' => 'CONCAT'
					],
					[
						'type' => 'literal',
						'value' => 'great'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Can\'t have two value inputs or two operators together.'
			], $errors);
		}

		// test invalid input order (two values together)
		$params = [
			'code' => 'compound_attribute',
			'field_type' => 'compound',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'searchable' => true,
			'data' => [
				'expected_value' => 'string',
				'inputs' => [
					[
						'type' => 'literal',
						'value' => 'test'
					],
					[
						'type' => 'literal',
						'value' => 'great'
					]
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));
			$this->fail('Expected exception not thrown.');
		}
		catch(\Cookbook\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertArraySubset([
				"data.inputs" => 'Can\'t have two value inputs or two operators together.'
			], $errors);
		}


	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'compound attribute'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand($params, 20) );

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('compound', $result->field_type);
		$this->assertEquals($result->admin_label, 'compound attribute');

	}

	public function testCreateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale' => 'en_US',
			'fields' => [
				'test_compound_text1_attribute' => 'test1',
				'test_compound_text2_attribute' => 'test2',
				'test_compound_attribute' => 'invalid'
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test1', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('test1 test2', $result->fields->test_compound_attribute);

	}

	public function testCreateLocalizedEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale' => 'en_US',
			'fields' => [
				'test_compound_text1_attribute' => 'test1',
				'test_compound_text2_attribute' => 'test2',
				'test_compound_localized_text_attribute' => 'test3',
				'test_compound_attribute' => 'invalid',
				'test_localized_compound_attribute' => 'invalid'
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test1', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('test3', $result->fields->test_compound_localized_text_attribute);
		$this->assertEquals('test1 test2', $result->fields->test_compound_attribute);
		$this->assertEquals('test1 test3', $result->fields->test_localized_compound_attribute);

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'fields' => [
				'test_compound_text1_attribute' => 'test1',
				'test_compound_text2_attribute' => 'test2',
				'test_compound_localized_text_attribute' => [
					'en_US' => 'test3-en',
					'fr_FR' => 'test3-fr'
				],
				'test_compound_attribute' => 'invalid',
				'test_localized_compound_attribute' => [
					'en_US' => 'invalid-en',
					'fr_FR' => 'invalid-fr'
				]
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test1', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('test3-en', $result->fields->test_compound_localized_text_attribute['en_US']);
		$this->assertEquals('test3-fr', $result->fields->test_compound_localized_text_attribute['fr_FR']);
		$this->assertEquals('test1 test2', $result->fields->test_compound_attribute);
		$this->assertEquals('test1 test3-en', $result->fields->test_localized_compound_attribute['en_US']);
		$this->assertEquals('test1 test3-fr', $result->fields->test_localized_compound_attribute['fr_FR']);

	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale' => 'en_US',
			'fields' => [
				'test_compound_text1_attribute' => 'test1',
				'test_compound_text2_attribute' => 'test2',
				'test_compound_attribute' => 'invalid'
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test1', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('test1 test2', $result->fields->test_compound_attribute);

		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_compound_text1_attribute' => 'changed'
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, $result->id));
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('changed', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('changed test2', $result->fields->test_compound_attribute);
		
	}

	public function testUpdateLocalizedEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'fields' => [
				'test_compound_text1_attribute' => 'test1',
				'test_compound_text2_attribute' => 'test2',
				'test_compound_localized_text_attribute' => [
					'en_US' => 'test3-en',
					'fr_FR' => 'test3-fr'
				],
				'test_compound_attribute' => 'invalid',
				'test_localized_compound_attribute' => [
					'en_US' => 'invalid-en',
					'fr_FR' => 'invalid-fr'
				]
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test1', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('test1 test2', $result->fields->test_compound_attribute);

		$params = [
			'fields' => [
				'test_compound_text1_attribute' => 'changed'
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, $result->id));
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('changed', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('changed test2', $result->fields->test_compound_attribute);
		$this->assertEquals('changed test3-en', $result->fields->test_localized_compound_attribute['en_US']);
		$this->assertEquals('changed test3-fr', $result->fields->test_localized_compound_attribute['fr_FR']);

		$params = [
			'fields' => [
				'test_compound_text1_attribute' => 'changed-again',
				'test_compound_localized_text_attribute' => [
					'en_US' => 'changed-en',
					'fr_FR' => 'changed-fr'
				],
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, $result->id));
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('changed-again', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('changed-again test2', $result->fields->test_compound_attribute);
		$this->assertEquals('changed-again changed-en', $result->fields->test_localized_compound_attribute['en_US']);
		$this->assertEquals('changed-again changed-fr', $result->fields->test_localized_compound_attribute['fr_FR']);

		$params = [
			'locale' => 'en_US',
			'fields' => [
				// 'test_compound_text1_attribute' => 'back',
				'test_compound_localized_text_attribute' => 'to-en'
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, $result->id));
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('changed-again', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('changed-again test2', $result->fields->test_compound_attribute);
		$this->assertEquals('changed-again to-en', $result->fields->test_localized_compound_attribute);

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand([], $result->id));

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertEquals('changed-again', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('changed-again test2', $result->fields->test_compound_attribute);
		$this->assertEquals('changed-again to-en', $result->fields->test_localized_compound_attribute['en_US']);
		$this->assertEquals('changed-again changed-fr', $result->fields->test_localized_compound_attribute['fr_FR']);

		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_compound_text1_attribute' => 'back'
			]
		];

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, $result->id));
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('back', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('back test2', $result->fields->test_compound_attribute);
		$this->assertEquals('back to-en', $result->fields->test_localized_compound_attribute);

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand([], $result->id));

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertEquals('back', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('back test2', $result->fields->test_compound_attribute);
		$this->assertEquals('back to-en', $result->fields->test_localized_compound_attribute['en_US']);
		$this->assertEquals('back changed-fr', $result->fields->test_localized_compound_attribute['fr_FR']);
		
	}

	// public function testFetchEntity()
	// {

	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand([], 4));
	// 	// $this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
	// 	$this->assertTrue(is_int($result->id));
	// 	$this->assertEquals('field text value', $result->fields->test_text_attribute);
	// 	$this->assertEquals('option1', $result->fields->test_select_attribute);
	// 	$this->assertEquals(11, $result->fields->test_integer_attribute);
	// 	$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
	// 	$this->assertEquals(1, $result->fields->test_relation_attribute->id);
	// 	$this->assertEquals('entity', $result->fields->test_relation_attribute->type);

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand(['include' => 'fields.test_relation_attribute'], 4));
	// 	// $this->d->dump($result->toArray(true, true));
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
	// 	$this->assertTrue(is_int($result->id));
	// 	$this->assertEquals('field text value', $result->fields->test_text_attribute);
	// 	$this->assertEquals('option1', $result->fields->test_select_attribute);
	// 	$this->assertEquals(11, $result->fields->test_integer_attribute);
	// 	$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
	// 	$this->assertEquals(1, $result->fields->test_relation_attribute->id);
	// 	$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
	// 	$this->assertEquals('tests', $result->toArray()['fields']['test_relation_attribute']['entity_type']);


	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand(['include' => 'fields.test_relation_attribute', 'locale' => 'en_US'], 4));
	// 	// $this->d->dump($result->toArray(true, true));
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
	// 	$this->assertTrue(is_int($result->id));
	// 	$this->assertEquals('field text value', $result->fields->test_text_attribute);
	// 	$this->assertEquals('option1', $result->fields->test_select_attribute);
	// 	$this->assertEquals(11, $result->fields->test_integer_attribute);
	// 	$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
	// 	$this->assertEquals(1, $result->fields->test_relation_attribute->id);
	// 	$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
	// 	$this->assertEquals('tests', $result->toArray()['fields']['test_relation_attribute']['entity_type']);
	// }

	// public function testFilterEntity()
	// {

	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$app = $this->createApplication();
	// 	$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => '11.1']]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['in' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['nin' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
	// 	$this->assertEquals(0, count($result));

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['lt' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
	// 	$this->assertEquals(0, count($result));

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['lte' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);


	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['gt' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
	// 	$this->assertEquals(0, count($result));

	// 	$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityGetCommand(['filter' => ['fields.test_decimal_attribute' => ['gte' => '11.1']]]));
	// 	$this->d->dump($result->toArray());
	// 	$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
	// 	$this->assertEquals(1, count($result));
	// 	$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);
	// }

}
