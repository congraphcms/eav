<?php

use Cookbook\Core\Exceptions\ValidationException;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DatetimeFieldTest extends Orchestra\Testbench\TestCase
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

		$this->artisan('db:seed', [
			'--class' => 'Cookbook\Eav\Seeders\TestDbSeeder'
		]);

		$this->d = new Dumper();

	}

	public function tearDown()
	{
		// fwrite(STDOUT, __METHOD__ . "\n");
		// parent::tearDown();
		
		$this->artisan('migrate:reset');
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
			'port'		=> '33060',
			'database'	=> 'cookbook_testbench',
			'username'  => 'homestead',
			'password'  => 'secret',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		]);

		$app['config']->set('cache.default', 'file');

		// $app['config']->set('app.timezone', 'UTC');

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
		return ['Cookbook\Core\CoreServiceProvider', 'Cookbook\Eav\EavServiceProvider'];
	}

	public function testCreateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'datetime_attribute',
			'field_type' => 'datetime',
			'localized' => true,
			'default_value' => 'default',
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
		$this->assertEquals('datetime_attribute', $result->code);
		
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'datetime_attribute_changed'
		];

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		
		$result = $bus->dispatch( new Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand($params, 13) );
		
		$this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->code, 'datetime_attribute_changed');
		
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
				'test_datetime_attribute' => '1987-08-19T11:00:00+0200'
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
		
	}

	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'locale_id' => 0,
			'fields' => [
				'test_datetime_attribute' => '1987-08-19T11:00:00+0200'
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
		$this->assertEquals('1987-08-19T09:00:00+0000', $result->toArray()['fields']['test_datetime_attribute']);
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


	}

}