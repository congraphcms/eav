<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Congraph\Core\Exceptions\ValidationException;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/MixDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class MixTest extends Orchestra\Testbench\TestCase
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
			'--class' => 'MixDbSeeder'
		]);

		$this->artisan('db:seed', [
			'--class' => 'LocaleDbSeeder'
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

	public function testEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);

		// create nones entity
		fwrite(STDOUT, 'create nones entity' . "\n");

		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'fields' => [
				'simple_text' => 'tekstic',
				'simple_select' => 'option1'
			]
		];

		$command->setParams($params);
		
		try
		{
			$result = $bus->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// create nones entity but publish it
		fwrite(STDOUT, 'create nones entity but publish it' . "\n");
		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'status' => 'published',
			'fields' => [
				'simple_text' => 'tekstic',
				'simple_select' => 'option1'
			]
		];
		
		$command->setParams($params);
		
		try
		{
			$result = $bus->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('published', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// create localized entity for one locale
		fwrite(STDOUT, 'create localized entity for one locale' . "\n");
		$params = [
			'entity_type' => ['id' => 2],
			'attribute_set' => ['id' => 2],
			'locale' => 'en_US',
			'fields' => [
				'localized_text' => 'text en',
				'simple_text' => 'tekstic',
				'localized_select' => 'option1_locale1',
				'simple_select' => 'option1'
			]
		];
		
		$command->setParams($params);
		
		try
		{
			$result = $bus->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertEquals('en_US', $result->locale);

		$this->assertEquals('text en', $result->fields->localized_text);
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals('option1_locale1', $result->fields->localized_select);
		$this->assertEquals('option1', $result->fields->simple_select);
		
		// $this->d->dump($result->toArray());

		// fetch half made entity (doesn't have all locales)
		fwrite(STDOUT, 'fetch half made entity (doesn\'t have all locales)' . "\n");
		$params = [];
		$id = $result->id;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		try
		{
			$result = $bus->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals(['en_US' => 'text en', 'fr_FR' => null], $result->fields->localized_text);
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals(['en_US' => 'option1_locale1', 'fr_FR' => null], $result->fields->localized_select);
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// fetch locale that isn\'t made
		fwrite(STDOUT, 'fetch locale that isn\'t made' . "\n");
		$params = ['locale' => 'fr_FR'];
		$id = $result->id;

		$command->setParams($params);
		$command->setId($id);
		
		try
		{
			$result = $bus->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertEquals('fr_FR', $result->locale);

		$this->assertFalse(isset($result->localized_text));
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertFalse(isset($result->localized_select));
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// create localized entity for all locales
		fwrite(STDOUT, 'create localized entity for all locales' . "\n");
		$params = [
			'entity_type' => ['id' => 2],
			'attribute_set' => ['id' => 2],
			'fields' => [
				'localized_text' => [
					'en_US' => 'text en',
					'fr_FR' => 'text fr'
				],
				'simple_text' => 'tekstic',
				'localized_select' => [
					'en_US' => 'option1_locale1',
					'fr_FR' => 'option1_locale2'
				],
				'simple_select' => 'option1'
			]
		];
		$id = null;

		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		try
		{
			$result = $bus->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals(['en_US' => 'text en', 'fr_FR' => 'text fr'], $result->fields->localized_text);
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals(['en_US' => 'option1_locale1', 'fr_FR' => 'option1_locale2'], $result->fields->localized_select);
		$this->assertEquals('option1', $result->fields->simple_select);
		
		// $this->d->dump($result->toArray());
	}

}