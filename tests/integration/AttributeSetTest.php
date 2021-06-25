<?php

use Illuminate\Support\Facades\Cache;
use Congraph\Core\Facades\Trunk;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/EavDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/FileDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class AttributeSetTest extends Orchestra\Testbench\TestCase
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

	public function testCreateAttributeSet()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'test-attr-set',
			'name' => 'Test Attr Set',
			'entity_type' => 'tests',
			'primary_attribute' => 'attribute1',
			'attributes' => [
				['code' => 'attribute1'],
				['id' => 2]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand::class);
		$command->setParams($params);
		try {
			$result = $bus->dispatch($command);
		} catch(\Congraph\Core\Exceptions\ValidationException $e) {
			$this->d->dump($e->getErrors());
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->d->dump($result->toArray());
	}

	public function testCreateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$this->expectException(\Congraph\Core\Exceptions\ValidationException::class);

		$params = [
			'code' => '',
			'name' => 'Test Attr Set',
			'entity_type_id' => 1,
			'primary_attribute_id' => 2,
			'attributes' => [
				['id' => 1],
				['id' => 2]
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand::class);
		$command->setParams($params);
		
		$result = $bus->dispatch($command);
	}

	public function testUpdateAttributeSet()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'attribute_set_changed'
		];
		$id = 1;
		
		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('attribute_set_changed', $result->code);
		$this->assertEquals(3, count($result->attributes));
		// $this->d->dump($result->toArray());
	}

	public function testUpdateSetAttributes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'primary_attribute_id' => 7,
			'attributes' => [
				[
					'id' => 6,
					'type' => 'attributes'
				],
				[
					'id' => 7,
					'type' => 'attributes'
				],
				[
					'id' => 1,
					'type' => 'attributes'
				]
			]
		];
		$id = 1;
		
		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(3, count($result->attributes));
		// $this->d->dump($result->toArray());
	}


	public function testUpdateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\ValidationException::class);
		
		$params = [
			'code' => ''
		];
		$id = 1;
		
		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
	}


	public function testDeleteAttributeSet()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand::class);
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
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand::class);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
	}

	public function testFetchAttributeSet()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand::class);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(3, count($result->attributes));
		// $this->d->dump($result->toArray());
	}

	public function testFetchWithInclude()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = ['include' => 'attributes'];
		$id = 1;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(3, count($result->attributes));
		// $this->d->dump($result->toArray());

		$arrayWithMeta = $result->toArray(true, false);
		$this->assertEquals(1, $arrayWithMeta['meta']['id']);
		$this->assertEquals('attributes', $arrayWithMeta['meta']['include']);
		// $this->d->dump($arrayWithMeta);
		$this->assertEquals(3, count($arrayWithMeta['included']));


	}

	public function testFetchException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\NotFoundException::class);

		$id = 133;

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand::class);
		$command->setId($id);
		
		$result = $bus->dispatch($command);
	}

	public function testGetAttributeSets()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand::class);
		
		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(4, count($result));
		// $this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = ['sort' => ['-code'], 'limit' => 2, 'offset' => 1];

		$app = $this->createApplication();
		$bus = $app->make('Congraph\Core\Bus\CommandDispatcher');
		$command = $app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand::class);
		$command->setParams($params);
		
		$result = $bus->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(2, count($result));

		// $this->d->dump($result->toArray());
	}

}