<?php

use Illuminate\Support\Facades\Cache;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class EntityTypeTest extends DatabaseTestCase
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

	public function testCreateEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'test-type',
			'endpoint' => 'test-types',
			'name' => 'Test Type',
			'plural_name' => 'Test Types',
			'workflow_id' => 1,
			'default_point_id' => 1,
			'localized_workflow' => 0,
			'multiple_sets' => 1
		];

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

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
			'name' => 'Test Type',
			'plural_name' => 'Test Types',
			'workflow_id' => 1,
			'default_point_id' => 1,
			'localized_workflow' => 0,
			'multiple_sets' => 1
		];

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);
	}

	public function testUpdateEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'code' => 'test_type_changed'
		];
		$id = 1;
		
		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('test_type_changed', $result->code);
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
		
		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}


	public function testDeleteEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeDeleteCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertEquals(1, $result->id);
		// $this->d->dump($result);
	}

	/**
	 * @expectedException \Congraph\Core\Exceptions\NotFoundException
	 */
	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\NotFoundException::class);

		$id = 133;

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeDeleteCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}

	public function testFetchEntityType()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->id);
		// $this->d->dump($result->toArray());
	}

	public function testFetchWithInclude()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = ['include' => 'workflow, default_point, attribute_sets.attributes'];
		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->id);
		$this->assertEquals(3, count($result->attribute_sets));
		// $this->d->dump($result->toArray());
		$arrayWithMeta = $result->toArray(true, false);
		$this->assertEquals(1, $arrayWithMeta['meta']['id']);
		$this->assertEquals('workflow, default_point, attribute_sets.attributes', $arrayWithMeta['meta']['include']);
		$this->assertEquals(12, count($arrayWithMeta['included']));

		// $this->d->dump($arrayWithMeta);
	}

	public function testFetchException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\NotFoundException::class);

		$id = 133;

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}

	public function testGetEntityTypes()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand::class);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(4, count($result));
		// $this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'sort' => ['-code'],
			'limit' => 2,
			'offset' => 1,
			'include' => [
				'workflow',
				'default_point',
				'attribute_sets.attributes'
			]
		];

		$command = $this->app->make(\Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(count($result), 2);

		// $this->d->dump($result->toArray());
	}

}