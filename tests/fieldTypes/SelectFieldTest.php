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

final class SelectFieldTest extends DatabaseTestCase
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
			'code' => 'select_attribute',
			'field_type' => 'select',
			'localized' => true,
			'default_value' => null,
			'unique' => false,
			'required' => false,
			'filterable' => true,
			'options' => [
				[
					'value' => 'select_option1',
					'label' => 'Option 1',
					'default' => true
				],
				[
					'value' => 'select_option3',
					'label' => 'Option 3',
					'default' => 0
				],
				[
					'value' => 'select_option2',
					'label' => 'Option 2',
					'default' => 0
				]
			]
		];

		$command = $this->app->make(AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		try
		{
			$result = $this->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			$this->fail('Unexpected validation error occured');
		}

		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertTrue(is_array($result->options));
		$this->assertEquals(3, count($result->options));
		$this->assertEquals('select_option1', $result->options[0]->value);
		$this->assertEquals('select_option3', $result->options[1]->value);
		$this->assertEquals('select_option2', $result->options[2]->value);
		
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'admin_label' => 'select_attribute_changed',
			'options' => [
				[
					'id' => 1,
					'value' => 'option_changed',
					'label' => 'Option Changed',
					'default' => true
				],
				[
					'value' => 'option_new',
					'label' => 'Option New',
					'default' => false
				]
			]
		];
		$id = 9;

		$command = $this->app->make(AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		// $this->d->dump($result->toArray());

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->admin_label, 'select_attribute_changed');
		$this->assertTrue(is_array($result->options));
		$this->assertEquals(2, count($result->options));
		$this->assertEquals('option_changed', $result->options[0]->value);
		$this->assertEquals('Option Changed', $result->options[0]->label);
		$this->assertEquals(1, $result->options[0]->id);
		$this->assertEquals('option_new', $result->options[1]->value);
		$this->assertEquals('Option New', $result->options[1]->label);
		$this->assertEquals(4, $result->options[1]->id);
		$this->assertEquals(0, $result->options[1]->default);
		
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
				'test_select_attribute' => 'option2'
			]
		];

		$command = $this->app->make(EntityCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test value', $result->fields->test_text_attribute);
		$this->assertEquals('option2', $result->fields->test_select_attribute);
		// $this->d->dump($result->toArray());
	}


	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$params = [
			'locale' => 'en_US',
			'fields' => [
				'test_select_attribute' => 'option3'
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
		$this->assertEquals('option3', $result->fields->test_select_attribute);
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

	}

	public function testFilterEntity()
	{

		fwrite(STDOUT, __METHOD__ . "\n");


		$params = ['filter' => ['fields.test_select_attribute' => 'option1']];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals('option1', $result[0]->fields->test_select_attribute);

		$params = ['filter' => ['fields.test_select_attribute' => ['in' => 'option1']]];
		$command = $this->app->make(EntityGetCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));
		$this->assertEquals('option1', $result[0]->fields->test_select_attribute);

	}

}