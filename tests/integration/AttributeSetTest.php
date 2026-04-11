<?php

use Illuminate\Support\Facades\Cache;
use Congraph\Core\Facades\Trunk;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class AttributeSetTest extends DatabaseTestCase
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

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand::class);
		$command->setParams($params);
		try {
			$result = $this->dispatch($command);
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

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);
	}

	public function testUpdateAttributeSet()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'attribute_set_changed'
		];
		$id = 1;
		
		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
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
		
		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		
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
		
		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}


	public function testDeleteAttributeSet()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertEquals(1, $result->id);
		// $this->d->dump($result);
	}

	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$this->expectException(\Congraph\Core\Exceptions\NotFoundException::class);

		$id = 133;

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}

	public function testFetchAttributeSet()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
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

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
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

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}

	public function testGetAttributeSets()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand::class);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->assertEquals(4, count($result));
		// $this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = ['sort' => ['-code'], 'limit' => 2, 'offset' => 1];

		$command = $this->app->make(\Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(2, count($result));

		// $this->d->dump($result->toArray());
	}

}