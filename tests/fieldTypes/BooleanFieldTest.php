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

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class BooleanFieldTest extends DatabaseTestCase
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
			'code' => 'boolean_attribute',
			'field_type' => 'boolean',
			'localized' => false,
			'default_value' => null,
			'unique' => false,
			'required' => false,
			'filterable' => true
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('boolean_attribute', $result->code);
		$this->assertEquals('boolean', $result->field_type);
		
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'boolean_attribute_changed'
		];
		$id = 15;

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'boolean_attribute_changed');
		
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
				'test_boolean_attribute' => 1
			]
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test value', $result->fields->test_text_attribute);
		$this->assertEquals('option2', $result->fields->test_select_attribute);
		$this->assertEquals(123, $result->fields->test_integer_attribute);
		$this->assertEquals(1, $result->fields->test_boolean_attribute);
		// $this->d->dump($result->toArray());
	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_boolean_attribute' => 1
			]
		];
		$id = 4;
		
		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->fields->test_boolean_attribute);
		// $this->d->dump($result->toArray());
	}

	public function testFetchEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 4;

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityFetchCommand::class);
		// $command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(0, $result->fields->test_boolean_attribute);

	}

	public function testFilterEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);

		$params = ['filter' => ['fields.test_boolean_attribute' => '0']];

		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals(0, $result[0]->fields->test_boolean_attribute);

		$params = ['filter' => ['fields.test_boolean_attribute' => ['ne' => '1']]];
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals(0, $result[0]->fields->test_boolean_attribute);

		$params = ['filter' => ['fields.test_boolean_attribute' => '1']];
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(0, count($result));
	}

}