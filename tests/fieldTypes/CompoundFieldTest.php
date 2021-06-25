<?php

use Congraph\Core\Exceptions\ValidationException;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Congraph\Eav\Commands\Attributes\AttributeCreateCommand;
use Congraph\Eav\Commands\Attributes\AttributeUpdateCommand;
use Congraph\Eav\Commands\Entities\EntityCreateCommand;
use Congraph\Eav\Commands\Entities\EntityUpdateCommand;
use Congraph\Eav\Commands\Entities\EntityFetchCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class CompoundFieldTest extends Orchestra\Testbench\TestCase
{

	// ----------------------------------------
    // ENVIRONMENT
    // ----------------------------------------

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

    /**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 *
	 * @return void
	 */
	protected function defineEnvironment($app)
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
		$app['config']->set('app.timezone', 'Europe/Belgrade');

		$app['config']->set('cache.stores.file', [
			'driver'	=> 'file',
			'path'   	=> realpath(__DIR__ . '/../storage/cache/'),
		]);
	}

    // ----------------------------------------
    // DATABASE
    // ----------------------------------------

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
		/**
		 * EAV Migrations
		 */
        $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations'));

        $this->artisan('migrate', ['--database' => 'testbench'])->run();


		/**
		 * FileSystem Migrations
		 */
		$this->loadMigrationsFrom(realpath(__DIR__.'/../../vendor/Congraph/Filesystem/database/migrations'));

        $this->artisan('migrate', ['--database' => 'testbench'])->run();


		/**
		 * Locales Migrations
		 */
		$this->loadMigrationsFrom(realpath(__DIR__.'/../../vendor/Congraph/Locales/database/migrations'));

        $this->artisan('migrate', ['--database' => 'testbench'])->run();


		/**
		 * Workflows Migrations
		 */
		$this->loadMigrationsFrom(realpath(__DIR__.'/../../vendor/Congraph/Workflows/database/migrations'));

        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->beforeApplicationDestroyed(function () {
			/**
			 * EAV Migrations
			 */
			$this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations'));
            $this->artisan('migrate:reset', ['--database' => 'testbench'])->run();

			/**
			 * FileSystem Migrations
			 */
			$this->loadMigrationsFrom(realpath(__DIR__.'/../../vendor/Congraph/Filesystem/database/migrations'));
            $this->artisan('migrate:reset', ['--database' => 'testbench'])->run();

			/**
			 * Locales Migrations
			 */
			$this->loadMigrationsFrom(realpath(__DIR__.'/../../vendor/Congraph/Locales/database/migrations'));
            $this->artisan('migrate:reset', ['--database' => 'testbench'])->run();

			/**
			 * Workflows Migrations
			 */
			$this->loadMigrationsFrom(realpath(__DIR__.'/../../vendor/Congraph/Workflows/database/migrations'));
            $this->artisan('migrate:reset', ['--database' => 'testbench'])->run();
        });
    }


    // ----------------------------------------
    // SETUP
    // ----------------------------------------

    public function setUp(): void {
		parent::setUp();

		$this->d = new Dumper();

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
	}

	public function tearDown(): void {
		$this->artisan('db:seed', [
			'--class' => 'ClearDB'
		]);
		parent::tearDown();
	}

    // ----------------------------------------
    // TESTS **********************************
    // ----------------------------------------

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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation exception thrown.');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation exception thrown.');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			// $this->assertEquals("You need to specify an expected value type.", $errors)
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);

		try
		{
			$result = $bus->dispatch($command);
			$this->fail('Expected exception not thrown.');
		}
		catch(\Congraph\Core\Exceptions\ValidationException $e)
		{
			$errors = $e->getErrors();
			// $this->d->dump($errors);
			$this->assertEqualsCanonicalizing([
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
		$id = 20;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Attributes\AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');

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

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('changed', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('changed test2', $result->fields->test_compound_attribute);
		
	}

	public function testUpdateLocalizedEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');

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

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test1', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('test1 test2', $result->fields->test_compound_attribute);

		$params = [
			'fields' => [
				'test_compound_text1_attribute' => 'changed'
			]
		];
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

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
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

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
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('changed-again', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('changed-again test2', $result->fields->test_compound_attribute);
		$this->assertEquals('changed-again to-en', $result->fields->test_localized_compound_attribute);
		
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityFetchCommand::class);
		// $command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
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
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals('back', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('back test2', $result->fields->test_compound_attribute);
		$this->assertEquals('back to-en', $result->fields->test_localized_compound_attribute);
		
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityFetchCommand::class);
		// $command->setParams($params);
		$command->setId($id);

		$result = $bus->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertEquals('back', $result->fields->test_compound_text1_attribute);
		$this->assertEquals('test2', $result->fields->test_compound_text2_attribute);
		$this->assertEquals('back test2', $result->fields->test_compound_attribute);
		$this->assertEquals('back to-en', $result->fields->test_localized_compound_attribute['en_US']);
		$this->assertEquals('back changed-fr', $result->fields->test_localized_compound_attribute['fr_FR']);
		
	}

}
