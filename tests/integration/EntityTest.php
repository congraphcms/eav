<?php

use Congraph\Core\Exceptions\ValidationException;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class EntityTest extends DatabaseTestCase
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

	public function testCreateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'locale' => 'en_US',
			'fields' => [
				'attribute1' => '234',
				'attribute2' => '',
				'attribute3' => 'english'
			]
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->d->dump($result->toArray());
	}

	public function testCreateManualTimestamp()
	{
		fwrite(STDOUT, __METHOD__ . "\n");


		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'locale' => 'en_US',
			'fields' => [
				'attribute1' => '234',
				'attribute2' => '',
				'attribute3' => 'english'
			],
			'created_at' => '1995-08-19T11:00:00+02:00',
			'updated_at' => '1996-08-19T11:00:00+02:00'
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		
		Config::set('cb.eav.allow_manual_timestamps', true);

		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('1995-08-19T09:00:00+00:00', $result->toArray()['created_at']);
		$this->assertEquals('1996-08-19T09:00:00+00:00', $result->toArray()['updated_at']);
		// $this->d->dump($result->toArray());
	}


	public function testCreateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\ValidationException::class);

		$params = [
			'attribute_set' => ['id' => 1],
			'locale_id' => 0,
			'fields' => [
				'attribute1' => '',
				'attribute2' => ''
			]
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);
	}

	public function testUpdateEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'fields' => [
				'attribute3' => [
					'fr_FR' => 'changed value'
				]
			]
		];
		$id = 1;
		
		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->fields->attribute3['fr_FR'], 'changed value');
		// $this->d->dump($result->toArray());
	}

	public function testUpdateStatus()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'locale' => 'en_US',
			'status' => 'published'
		];
		$id = 1;
		
		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		
		// $this->d->dump($result->toArray());
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals($result->status, 'published');
		
	}

	public function testUpdateManualTimestamp()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'created_at' => '1995-08-19T11:00:00+02:00',
			'updated_at' => '1996-08-19T11:00:00+02:00'
		];
		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		Config::set('cb.eav.allow_manual_timestamps', true);
		
		$result = $this->dispatch($command);
		

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('1995-08-19T09:00:00+00:00', $result->toArray()['created_at']);
		$this->assertEquals('1996-08-19T09:00:00+00:00', $result->toArray()['updated_at']);
		// $this->d->dump($result->toArray());
	}

	public function testUpdateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$this->expectException(\Congraph\Core\Exceptions\ValidationException::class);

		$params = [
			'fields' => [
				'attribute1' => ''
			]
		];
		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityUpdateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}

	public function testDeleteEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityDeleteCommand::class);
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

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityDeleteCommand::class);
		$command->setId($id);
		
		$result = $this->dispatch($command);
	}
	
	public function testFetchEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [ 'locale' => 'en_US' ];
		$id = 1;

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		$result = $this->dispatch($command);
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		// $this->d->dump($result->toArray());
		

	}

	
	public function testGetEntities()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);

		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(4, count($result));
		// $this->d->dump($result->toArray());

	}

	public function testGetParams()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'locale' => 'en_US',
			'sort' => ['fields.attribute3'],
			'limit' => 3,
			'offset' => 0
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);
		$command->setParams($params);

		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(3, count($result));

		// $this->d->dump($result->toArray());
	}

	public function testGetFilters()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$filter = [ 'fields.attribute3' => 'value3-en' ];
		$params = [
			'filter' => $filter,
			'locale' => 'en_US',
			'sort' => ['fields.attribute1']
		];

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);
		$command->setParams($params);

		$result = $this->dispatch($command);

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(1, count($result));

		// $this->d->dump($result->toArray());

	}

}