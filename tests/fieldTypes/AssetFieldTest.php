<?php

use Cookbook\Core\Exceptions\ValidationException;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Cache;

class AssetFieldTest extends Orchestra\Testbench\TestCase
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
			'--realpath' => realpath(__DIR__.'/../../migrations'),
		]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/Cookbook/Filesystem/migrations'),
		]);

		$this->artisan('db:seed', [
			'--class' => 'Cookbook\Eav\Seeders\TestDbSeeder'
		]);
		$this->artisan('db:seed', [
			'--class' => 'Cookbook\Eav\Seeders\FileDbSeeder'
		]);

		$this->d = new Dumper();

		Storage::deleteDir('files');
		Storage::deleteDir('uploads');

		Storage::copy('temp/test.jpg', 'files/test.jpg');
		Storage::copy('temp/test.jpg', 'files/test2.jpg');

		Storage::copy('temp/test.jpg', 'uploads/1.jpg');

	}

	public function tearDown()
	{
		// fwrite(STDOUT, __METHOD__ . "\n");
		// parent::tearDown();
		
		$this->artisan('migrate:reset');
		// unset($this->app);
		Storage::deleteDir('files');
		Storage::deleteDir('uploads');

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
			'port'		=> '33060',
			'database'	=> 'cookbook_testbench',
			'username'  => 'homestead',
			'password'  => 'secret',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		]);

		$app['config']->set('cache.default', 'file');

		$app['config']->set('cache.stores.file', [
			'driver'	=> 'file',
			'path'   	=> realpath(__DIR__ . '/../storage/cache/'),
		]);

		$app['config']->set('filesystems.default', 'local');

		$app['config']->set('filesystems.disks.local', [
			'driver'	=> 'local',
			'root'   	=> realpath(__DIR__ . '/../storage/'),
		]);

		// $config = require(realpath(__DIR__.'/../../config/eav.php'));

		// $app['config']->set(
		// 	'Cookbook::eav', $config
		// );

		// var_dump('CONFIG SETTED');
	}

	protected function getPackageProviders($app)
	{
		return ['Cookbook\Core\CoreServiceProvider', 'Cookbook\Eav\EavServiceProvider', 'Cookbook\Filesystem\FilesystemServiceProvider'];
	}

	public function testCreateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'asset_attribute',
			'field_type' => 'asset',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'options' => []
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeCreateCommand($params));

		$this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('asset_attribute', $result->code);
		
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'asset_attribute_changed'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand($params, 15) );
		
		$this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->code, 'asset_attribute_changed');
		
	}

	public function testCreateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale_id' => 0,
			'fields' => [
				'test_text_attribute' => 'test value',
				'test_textarea_attribute' => 'test value for textarea',
				'test_select_attribute' => 'option2',
				'test_integer_attribute' => 123,
				'test_decimal_attribute' => 33.33,
				'test_datetime_attribute' => '1987-08-19T11:00:00+0200',
				'test_relation_attribute' => ['id' => 2, 'type' => 'entity'],
				'test_asset_attribute' => ['id' => 1, 'type' => 'file']
			]
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityCreateCommand($params));
		$this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test value', $result->fields->test_text_attribute);
		$this->assertEquals('test value for textarea', $result->fields->test_textarea_attribute);
		$this->assertEquals('option2', $result->fields->test_select_attribute);
		$this->assertEquals(123, $result->fields->test_integer_attribute);
		$this->assertEquals(33.33, $result->fields->test_decimal_attribute);
		$this->assertEquals('1987-08-19T09:00:00+0000', $result->toArray()['fields']['test_datetime_attribute']);
		$this->assertEquals(2, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals(1, $result->fields->test_asset_attribute->id);
		$this->assertEquals('file', $result->fields->test_asset_attribute->type);
		
	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'locale_id' => 0,
			'fields' => [
				'test_asset_attribute' => ['id' => 2, 'type' => 'file']
			]
		];
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityUpdateCommand($params, 4));
		
		
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('field text area value', $result->fields->test_textarea_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals(2, $result->fields->test_asset_attribute->id);
		$this->assertEquals('file', $result->fields->test_asset_attribute->type);
		$this->d->dump($result->toArray());
	}

	public function testFetchEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand([], 4));
		$this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('field text area value', $result->fields->test_textarea_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals(1, $result->fields->test_asset_attribute->id);
		$this->assertEquals('file', $result->fields->test_asset_attribute->type);

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand(['include' => 'fields.test_asset_attribute, fields.test_relation_attribute'], 4));
		$this->d->dump($result->toArray(false, true));
		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('field text area value', $result->fields->test_textarea_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals('tests', $result->toArray()['fields']['test_relation_attribute']['entity_type']);
		$this->assertEquals(1, $result->fields->test_asset_attribute->id);
		$this->assertEquals('file', $result->fields->test_asset_attribute->type);
		$this->assertEquals('test.jpg', $result->toArray()['fields']['test_asset_attribute']['name']);
	}

	public function testFileDelete()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\Filesystem\Commands\Files\FileDeleteCommand([], 1));
		$this->d->dump($result);

		$result = $bus->dispatch( new Cookbook\Eav\Commands\Entities\EntityFetchCommand([], 4));
		$this->d->dump($result->toArray());
		$this->assertFalse(isset($result->fields->test_asset_attribute));
	}

}