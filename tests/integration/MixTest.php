<?php

// include_once(realpath(__DIR__.'/../LaravelMocks.php'));
use Cookbook\Core\Exceptions\ValidationException;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once(__DIR__ . '/../database/seeders/MixDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/LocaleDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/WorkflowDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class MixTest extends Orchestra\Testbench\TestCase
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
			'--realpath' => realpath(__DIR__.'/../../vendor/Cookbook/Locales/database/migrations'),
		]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/Cookbook/Workflows/database/migrations'),
		]);

		$this->artisan('db:seed', [
			'--class' => 'LocaleDbSeeder'
		]);

		$this->artisan('db:seed', [
			'--class' => 'WorkflowDbSeeder'
		]);

		$this->artisan('db:seed', [
			'--class' => 'MixDbSeeder'
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

	public function testEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

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
		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
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
		
		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
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
		
		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
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
		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand([], $result->id));
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
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
		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand(['locale' => 'fr_FR'], $result->id));
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
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

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		
		try
		{
			$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
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