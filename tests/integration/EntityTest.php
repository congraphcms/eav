<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Congraph\Core\Exceptions\ValidationException;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class EntityTest extends Orchestra\Testbench\TestCase
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

	public function testCreateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'locale' => 'en_US',
			'fields' => [
				'attribute1' => '234',
				'attribute2' => '',
				'attribute3' => 'english'
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		
		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->d->dump($result->toArray());
	}

	public function testCreateManualTimestamp()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'locale' => 'en_US',
			'fields' => [
				'attribute1' => '234',
				'attribute2' => '',
				'attribute3' => 'english'
			],
			'created_at' => '1995-08-19T11:00:00+02:00',
			'updated_at' => '1996-08-19T11:00:00+02:00'
		];

		$app = $this->createApplication();$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		
		Config::set('cb.eav.allow_manual_timestamps', true);

		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('1995-08-19T09:00:00+00:00', $result->toArray()['created_at']);
		$this->assertEquals('1996-08-19T09:00:00+00:00', $result->toArray()['updated_at']);
		// $this->d->dump($result->toArray());
	}


	public function testCreateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\ValidationException::class);

		$params = [
			'attribute_set' => ['id' => 1],
			'locale_id' => 0,
			'fields' => [
				'attribute1' => '',
				'attribute2' => ''
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		
		$result = $bus->dispatch($command);
	}

	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'fields' => [
				'attribute3' => [
					'fr_FR' => 'changed value'
				]
			]
		];
		$id = 1;
		
		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->fields->attribute3['fr_FR'], 'changed value');
		// $this->d->dump($result->toArray());
	}

	public function testUpdateStatus()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'locale' => 'en_US',
			'status' => 'published'
		];
		$id = 1;
		
		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->status, 'published');
		
	}

	public function testUpdateManualTimestamp()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'created_at' => '1995-08-19T11:00:00+02:00',
			'updated_at' => '1996-08-19T11:00:00+02:00'
		];
		$id = 1;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		Config::set('cb.eav.allow_manual_timestamps', true);
		
		$result = $bus->dispatch($command);
		

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('1995-08-19T09:00:00+00:00', $result->toArray()['created_at']);
		$this->assertEquals('1996-08-19T09:00:00+00:00', $result->toArray()['updated_at']);
		// $this->d->dump($result->toArray());
	}

	public function testUpdateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\ValidationException::class);

		$params = [
			'fields' => [
				'attribute1' => ''
			]
		];
		$id = 1;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
	}

	public function testDeleteEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityDeleteCommand::class);
		$command->setId($id);
		
		$result = $bus->dispatch($command);

		$this->assertEquals(1, $result->id);
		// $this->d->dump($result);
		

	}


	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\NotFoundException::class);

		$id = 133;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityDeleteCommand::class);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
	}
	
	public function testFetchEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [ 'locale' => 'en_US' ];
		$id = 1;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->d->dump($result->toArray());
		

	}

	
	public function testGetEntities()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);

		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(4, count($result));
		// $this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'locale' => 'en_US',
			'sort' => ['fields.attribute3'],
			'limit' => 3,
			'offset' => 0
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);
		$command->setParams($params);

		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		// $this->d->dump($result->toArray());
	}

	public function testGetFilters()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$filter = [ 'fields.attribute3' => 'value3-en' ];
		$params = [
			'filter' => $filter,
			'locale' => 'en_US',
			'sort' => ['fields.attribute1']
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);
		$command->setParams($params);

		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));

		// $this->d->dump($result->toArray());

	}

}