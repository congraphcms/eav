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

final class NodeFieldTest extends DatabaseTestCase
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
			'code' => 'node_attribute',
			'field_type' => 'node',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'options' => []
		];

		$command = $this->app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('node_attribute', $result->code);

	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'node_attribute_changed'
		];
		$id = 13;

		$command = $this->app->make(AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'node_attribute_changed');

	}

	public function testCreateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale' => 'en_US',
			'fields' => [
				'test_node_attribute' => ['id' => 2, 'type' => 'entity']
			]
		];

		$command = $this->app->make(EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(2, $result->fields->test_node_attribute->id);
		$this->assertEquals('entity', $result->fields->test_node_attribute->type);

	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_node_attribute' => ['id' => 2, 'type' => 'entity']
			]
		];
		$id = 4;
		$command = $this->app->make(EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);

		$this->assertTrue(is_int($result->id));
		$this->assertEquals(2, $result->fields->test_node_attribute->id);
		$this->assertEquals('entity', $result->fields->test_node_attribute->type);
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
		// $this->d->dump($result->toArray(true, true));
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_node_attribute->id);
		$this->assertEquals('entity', $result->fields->test_node_attribute->type);
		$this->assertEquals('tests', $result->toArray()['fields']['test_node_attribute']['entity_type']);

		$params = ['locale' => 'en_US'];
		$id = 4;
		$command = $this->app->make(EntityFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray(true, true));
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('field text value', $result->fields->test_text_attribute);
		$this->assertEquals('option1', $result->fields->test_select_attribute);
		$this->assertEquals(11, $result->fields->test_integer_attribute);
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(1, $result->fields->test_node_attribute->id);
		$this->assertEquals('entity', $result->fields->test_node_attribute->type);
		$this->assertEquals('tests', $result->toArray()['fields']['test_node_attribute']['entity_type']);
	}

}
