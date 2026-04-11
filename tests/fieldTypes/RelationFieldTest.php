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

final class RelationFieldTest extends DatabaseTestCase
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
			'code' => 'relation_attribute',
			'field_type' => 'relation',
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
		$this->assertEquals('relation_attribute', $result->code);

	}

	public function testAllowedTypes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'relation_attribute',
			'field_type' => 'relation',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true
		];

		$command = $this->app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_object($result->data));
		$this->assertFalse($result->data->allowed_types);

		$params = [
            'code' => 'relation_attribute2',
            'field_type' => 'relation',
            'localized' => false,
            'unique' => false,
            'required' => false,
			'filterable' => true,
			'data' => [
				'allowed_types' => false
			]
        ];

		$command = $this->app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);

        // $this->d->dump($result->toArray());

        $this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
        $this->assertTrue(is_object($result->data));
		$this->assertFalse($result->data->allowed_types);

		$params = [
			'code' => 'relation_attribute3',
			'field_type' => 'relation',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'data' => [
				'allowed_types' => 1
			]
		];

		$command = $this->app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		try {
			$result = $this->dispatch($command);
		} catch( ValidationException $e) {
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation error occured');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
        $this->assertTrue(is_object($result->data));
		$this->assertTrue(is_array($result->data->allowed_types));
		$this->assertEquals(1, count($result->data->allowed_types));
        $this->assertEquals(1, $result->data->allowed_types[0]);

		$params = [
			'code' => 'relation_attribute4',
			'field_type' => 'relation',
			'localized' => false,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'data' => [
				'allowed_types' => ['tests']
			]
		];

		$command = $this->app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		try {
			$result = $this->dispatch($command);
		} catch( ValidationException $e) {
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation error occured');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
        $this->assertTrue(is_object($result->data));
		$this->assertTrue(is_array($result->data->allowed_types));
		$this->assertEquals(1, count($result->data->allowed_types));
        $this->assertEquals(1, $result->data->allowed_types[0]);

	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'relation_attribute_changed'
		];
		$id = 13;

		$command = $this->app->make(AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'relation_attribute_changed');

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
				'test_decimal_attribute' => 33.33,
				'test_datetime_attribute' => '1987-08-19T11:00:00+0200',
				'test_relation_attribute' => ['id' => 2, 'type' => 'entity']
			]
		];

		$command = $this->app->make(EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test value', $result->fields->test_text_attribute);
		$this->assertEquals('option2', $result->fields->test_select_attribute);
		$this->assertEquals(123, $result->fields->test_integer_attribute);
		$this->assertEquals(33.33, $result->fields->test_decimal_attribute);
		$this->assertEquals('1987-08-19T09:00:00+00:00', $result->toArray()['fields']['test_datetime_attribute']);
		$this->assertEquals(2, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);

	}

	public function testCreateMultipleEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => 'test_fields',
			'attribute_set' => ['id' => 4],
			'locale' => 'en_US',
			'fields' => [
				'test_relation_collection_attribute' => [
					['id' => 2, 'type' => 'entity'],
					['id' => 1, 'type' => 'entity']
				]
			]
		];

		$command = $this->app->make(EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertTrue(is_array($result->fields->test_relation_collection_attribute));
		$this->assertEquals(2, $result->fields->test_relation_collection_attribute[0]->id);
		$this->assertEquals(1, $result->fields->test_relation_collection_attribute[1]->id);
		$this->assertEquals('entity', $result->fields->test_relation_collection_attribute[0]->type);
		$this->assertEquals('entity', $result->fields->test_relation_collection_attribute[1]->type);

	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_relation_attribute' => ['id' => 2, 'type' => 'entity']
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
		$this->assertEquals(11.1, $result->fields->test_decimal_attribute);
		$this->assertEquals(2, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
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
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);

		$params = ['include' => 'fields.test_relation_attribute'];
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
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals('tests', $result->toArray()['fields']['test_relation_attribute']['entity_type']);

		$params = ['include' => 'fields.test_relation_attribute', 'locale' => 'en_US'];
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
		$this->assertEquals(1, $result->fields->test_relation_attribute->id);
		$this->assertEquals('entity', $result->fields->test_relation_attribute->type);
		$this->assertEquals('tests', $result->toArray()['fields']['test_relation_attribute']['entity_type']);
	}

}
