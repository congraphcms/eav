<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Congraph\Core\Exceptions\ValidationException;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Congraph\Eav\Commands\Attributes\AttributeCreateCommand;
use Congraph\Eav\Commands\Attributes\AttributeUpdateCommand;
use Congraph\Eav\Commands\Entities\EntityCreateCommand;
use Congraph\Eav\Commands\Entities\EntityUpdateCommand;
use Congraph\Eav\Commands\Entities\EntityFetchCommand;
use Congraph\Eav\Commands\Entities\EntityGetCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class SelectFieldTest extends Orchestra\Testbench\TestCase
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
			'code' => 'select_attribute',
			'field_type' => 'select',
			'localized' => true,
			'default_value' => null,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'options' => [
				[
					'value' => 'select_option1',
					'label' => 'Option 1',
					'default' => true
				],
				[
					'value' => 'select_option3',
					'label' => 'Option 3',
					'default' => 0
				],
				[
					'value' => 'select_option2',
					'label' => 'Option 2',
					'default' => 0
				]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		try
		{
			$result = $bus->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation error occured');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertTrue(is_array($result->options));
		$this->assertEquals(3, count($result->options));
		$this->assertEquals('select_option1', $result->options[0]->value);
		$this->assertEquals('select_option3', $result->options[1]->value);
		$this->assertEquals('select_option2', $result->options[2]->value);
		
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'select_attribute_changed',
			'options' => [
				[
					'id' => 1,
					'value' => 'option_changed',
					'label' => 'Option Changed',
					'default' => true
				],
				[
					'value' => 'option_new',
					'label' => 'Option New',
					'default' => false
				]
			]
		];
		$id = 9;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'select_attribute_changed');
		$this->assertTrue(is_array($result->options));
		$this->assertEquals(2, count($result->options));
		$this->assertEquals('option_changed', $result->options[0]->value);
		$this->assertEquals('Option Changed', $result->options[0]->label);
		$this->assertEquals(1, $result->options[0]->id);
		$this->assertEquals('option_new', $result->options[1]->value);
		$this->assertEquals('Option New', $result->options[1]->label);
		$this->assertEquals(4, $result->options[1]->id);
		$this->assertEquals(0, $result->options[1]->default);
		
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
				'test_select_attribute' => 'option2'
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test value', $result->fields->test_text_attribute);
		$this->assertEquals('option2', $result->fields->test_select_attribute);
		// $this->d->dump($result->toArray());
	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');

		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_select_attribute' => 'option3'
			]
		];
		$id = 4;

		$command = $app->make(EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option3', $result->fields->test_select_attribute);
		// $this->d->dump($result->toArray());
	}

	public function testFetchEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$id = 4;

		$command = $app->make(EntityFetchCommand::class);
		// $command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);

	}

	public function testFilterEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');

		$params = ['filter' => ['fields.test_select_attribute' => 'option1']];
		$command = $app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals('option1', $result[0]->fields->test_select_attribute);

		$params = ['filter' => ['fields.test_select_attribute' => ['in' => 'option1']]];
		$command = $app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $bus->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals('option1', $result[0]->fields->test_select_attribute);

	}

}