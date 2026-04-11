<?php

use Congraph\Core\Exceptions\ValidationException;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class MixTest extends DatabaseTestCase
{
	protected function seeders(): array
	{
		return [
			Database\Seeders\MixDbSeeder::class,
			Database\Seeders\LocaleDbSeeder::class,
			Database\Seeders\WorkflowDbSeeder::class,
		];
	}

    // ----------------------------------------
    // TESTS **********************************
    // ----------------------------------------

	public function testEntity()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);

		// create nones entity
		fwrite(STDOUT, 'create nones entity' . "\n");

		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'fields' => [
				'simple_text' => 'tekstic',
				'simple_select' => 'option1'
			]
		];

		$command->setParams($params);
		
		try
		{
			$result = $this->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// create nones entity but publish it
		fwrite(STDOUT, 'create nones entity but publish it' . "\n");
		$params = [
			'entity_type' => ['id' => 1],
			'attribute_set' => ['id' => 1],
			'status' => 'published',
			'fields' => [
				'simple_text' => 'tekstic',
				'simple_select' => 'option1'
			]
		];
		
		$command->setParams($params);
		
		try
		{
			$result = $this->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('published', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// create localized entity for one locale
		fwrite(STDOUT, 'create localized entity for one locale' . "\n");
		$params = [
			'entity_type' => ['id' => 2],
			'attribute_set' => ['id' => 2],
			'locale' => 'en_US',
			'fields' => [
				'localized_text' => 'text en',
				'simple_text' => 'tekstic',
				'localized_select' => 'option1_locale1',
				'simple_select' => 'option1'
			]
		];
		
		$command->setParams($params);
		
		try
		{
			$result = $this->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertEquals('en_US', $result->locale);

		$this->assertEquals('text en', $result->fields->localized_text);
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals('option1_locale1', $result->fields->localized_select);
		$this->assertEquals('option1', $result->fields->simple_select);
		
		// $this->d->dump($result->toArray());

		// fetch half made entity (doesn't have all locales)
		fwrite(STDOUT, 'fetch half made entity (doesn\'t have all locales)' . "\n");
		$params = [];
		$id = $result->id;

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityFetchCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		try
		{
			$result = $this->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals(['en_US' => 'text en', 'fr_FR' => null], $result->fields->localized_text);
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals(['en_US' => 'option1_locale1', 'fr_FR' => null], $result->fields->localized_select);
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// fetch locale that isn\'t made
		fwrite(STDOUT, 'fetch locale that isn\'t made' . "\n");
		$params = ['locale' => 'fr_FR'];
		$id = $result->id;

		$command->setParams($params);
		$command->setId($id);
		
		try
		{
			$result = $this->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertEquals('fr_FR', $result->locale);

		$this->assertFalse(isset($result->localized_text));
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertFalse(isset($result->localized_select));
		$this->assertEquals('option1', $result->fields->simple_select);
		// $this->d->dump($result->toArray());

		// create localized entity for all locales
		fwrite(STDOUT, 'create localized entity for all locales' . "\n");
		$params = [
			'entity_type' => ['id' => 2],
			'attribute_set' => ['id' => 2],
			'fields' => [
				'localized_text' => [
					'en_US' => 'text en',
					'fr_FR' => 'text fr'
				],
				'simple_text' => 'tekstic',
				'localized_select' => [
					'en_US' => 'option1_locale1',
					'fr_FR' => 'option1_locale2'
				],
				'simple_select' => 'option1'
			]
		];
		$id = null;

		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
		$command->setParams($params);
		$command->setId($id);
		
		try
		{
			$result = $this->dispatch($command);
		}
		catch(ValidationException $e)
		{
			$this->d->dump($e->getErrors());
			throw $e;
		}

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('draft', $result->status);
		$this->assertFalse(isset($result->locale));

		$this->assertEquals(['en_US' => 'text en', 'fr_FR' => 'text fr'], $result->fields->localized_text);
		$this->assertEquals('tekstic', $result->fields->simple_text);
		$this->assertEquals(['en_US' => 'option1_locale1', 'fr_FR' => 'option1_locale2'], $result->fields->localized_select);
		$this->assertEquals('option1', $result->fields->simple_select);
		
		// $this->d->dump($result->toArray());
	}

}