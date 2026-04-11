<?php

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

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class DecimalFieldTest extends DatabaseTestCase
{
	protected function seeders(): array
	{
		return [
			Database\Seeders\EavDbSeeder::class,
			Database\Seeders\LocaleDbSeeder::class,
			Database\Seeders\FileDbSeeder::class,
			Database\Seeders\WorkflowDbSeeder::class,
		];
	}

    // ----------------------------------------
    // TESTS **********************************
    // ----------------------------------------

	public function testCreateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'decimal_attribute',
			'field_type' => 'decimal',
			'localized' => true,
			'default_value' => 'default',
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'options' => []
		];

		$command = $this->app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('decimal_attribute', $result->code);
		
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'decimal_attribute_changed'
		];
		$id = 11;

		$command = $this->app->make(AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'decimal_attribute_changed');
		
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
				'test_decimal_attribute' => 33.33
			]
		];

		$command = $this->app->make(EntityCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test value', $result->fields->test_text_attribute);
		$this->assertEquals('option2', $result->fields->test_select_attribute);
		$this->assertEquals(123, $result->fields->test_integer_attribute);
		$this->assertEquals(33.33, $result->fields->test_decimal_attribute);
		// $this->d->dump($result->toArray());
	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_decimal_attribute' => 44.44
			]
		];
		$id = 4;

		$command = $this->app->make(EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(44.44, $result->fields->test_decimal_attribute);
		// $this->d->dump($result->toArray());
	}

	public function testFetchEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");
		$id = 4;

		$command = $this->app->make(EntityFetchCommand::class);
		// $command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);

	}

	public function testFilterEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$params = ['filter' => ['fields.test_decimal_attribute' => '11.1']];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);

		$params = ['filter' => ['fields.test_decimal_attribute' => ['in' => '11.1']]];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);

		$params = ['filter' => ['fields.test_decimal_attribute' => ['nin' => '11.1']]];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(0, count($result));

		$params = ['filter' => ['fields.test_decimal_attribute' => ['lt' => '11.1']]];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(0, count($result));

		$params = ['filter' => ['fields.test_decimal_attribute' => ['lte' => '11.1']]];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);

		$params = ['filter' => ['fields.test_decimal_attribute' => ['gt' => '11.1']]];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(0, count($result));

		$params = ['filter' => ['fields.test_decimal_attribute' => ['gte' => '11.1']]];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals(11.1, $result[0]->fields->test_decimal_attribute);
	}

}