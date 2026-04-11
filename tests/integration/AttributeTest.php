<?php

use Illuminate\Support\Facades\Cache;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class AttributeTest extends DatabaseTestCase
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
			'code' => 'code',
			'admin_label' => 'Code',
			'admin_notice' => 'Enter code here.',
			'field_type' => 'text',
			'localized' => false,
			'default_value' => '',
			'unique' => false,
			'required' => false,
			'filterable' => false,
			'status' => 'user_defined'
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);
		// $command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->d->dump($result->toArray());
	}

	public function testCreateWithOptions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'code',
			'field_type' => 'select',
			'localized' => false,
			'default_value' => '',
			'unique' => false,
			'required' => false,
			'filterable' => false,
			'status' => 'user_defined',
			'options' => [
				[
					'value' => 'option1',
					'label' => 'Option 1',
					'default' => true,
					'sort_order' => 0
				],
				[
					'value' => 'option2',
					'label' => 'Option 2',
					'default' => 0,
					'sort_order' => 2
				],
				[
					'value' => 'option3',
					'label' => 'Option 3',
					'default' => 0,
					'sort_order' => 1
				]
			]
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertTrue(is_array($result->options));
		$this->assertFalse(empty($result->options));
		// $this->d->dump($result->toArray());
	}

	public function testCreateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\ValidationException::class);

		$params = [
			'field_type' => 'text',
			'localized' => false,
			'default_value' => '',
			'unique' => false,
			'required' => false,
			'filterable' => false,
			'status' => 'user_defined'
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);
	}

	public function testUpdateAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			// 'code' => 'attribute_updated',
			'admin_label' => 'changed',
			'admin_notice' => 'changed_notice'
		];
		
		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId(1);
		
		$result = $this->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->assertEquals('attribute_updated', $result->code);
		$this->assertEquals('changed', $result->admin_label);
		$this->assertEquals('changed_notice', $result->admin_notice);

		// $this->d->dump($result->toArray());
	}

	public function testUpdateSameCode()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'attribute1',
			'admin_label' => 'changed',
			'admin_notice' => 'changed_notice'
		];
		
		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeUpdateCommand::class);
		$command->setParams($params);
		$command->setId(1);
		
		$result = $this->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('attribute1', $result->code);
		$this->assertEquals('changed', $result->admin_label);
		$this->assertEquals('changed_notice', $result->admin_notice);

		// $this->d->dump($result->toArray());
	}

	public function testDeleteAttribute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeDeleteCommand::class);
		$command->setId(2);
		
		$result = $this->dispatch($command);

		$this->assertEquals(2, $result->id);
		// $this->d->dump($result);
		

	}

	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\NotFoundException::class);

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeDeleteCommand::class);
		$command->setId(233);
		
		$result = $this->dispatch($command);
	}
	
	public function testFetchAttribute()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeFetchCommand::class);
		$command->setId(1);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->code, 'attribute1');
		// $this->d->dump($result->toArray());
		

	}

	
	public function testGetAttributes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeGetCommand::class);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(16, count($result));
		// $this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'sort' => ['-code'],
			'limit' => 3,
			'offset' => 1
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeGetCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		$arrayResult = $result->toArray();
		// $this->d->dump($arrayResult);

		$arrayResultWithMeta = $result->toArray(true);
		$this->assertEquals(['-code'], $arrayResultWithMeta['meta']['sort']);
		$this->assertEquals(3, $arrayResultWithMeta['meta']['limit']);
		$this->assertEquals(1, $arrayResultWithMeta['meta']['offset']);
		$this->assertEquals([], $arrayResultWithMeta['meta']['filter']);
		$this->assertEquals([], $arrayResultWithMeta['meta']['include']);
		$this->assertEquals(3, $arrayResultWithMeta['meta']['count']);
		// $this->assertEquals(16, $arrayResultWithMeta['meta']['total']);
		// $this->d->dump($arrayResultWithMeta);
	}

	public function testGetFilters()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$filter = [ 'id' => 5 ];
		$params = [ 'filter' => $filter ];

		$command = $this->app->make(\Congraph\Eav\Commands\Attributes\AttributeGetCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		// $this->d->dump($result->toArray());
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));

		

		$filter = [ 'id' => ['in'=>'5,6,7'] ];
		$params = [ 'filter' => $filter ];

		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['nin'=>[5,6,7,1]] ];
		$params = [ 'filter' => $filter ];

		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(12, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['lt'=>3] ];
		$params = [ 'filter' => $filter ];

		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(2, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['lte'=>3] ];
		$params = [ 'filter' => $filter ];

		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		// $this->d->dump($result->toArray());

		$filter = [ 'id' => ['ne'=>3] ];
		$params = [ 'filter' => $filter ];

		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(15, count($result));

		// $this->d->dump($result->toArray());
	}

}