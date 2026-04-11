<?php

use Congraph\Core\Exceptions\ValidationException;
use Symfony\Component\VarDumper\VarDumper as Dumper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/../Fixtures/DatabaseTestCase.php';

final class SampleBlogTest extends DatabaseTestCase
{
	protected function seeders(): array
	{
		return [
			Database\Seeders\SampleBlogCommandSeeder::class,
		];
	}

    // ----------------------------------------
    // TESTS **********************************
    // ----------------------------------------

	public function testFullTextFieldSearch()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'filter' => [
				'fields.title' => [
					'm' => 'strana',
					'in' => 'Pocetna strana, Kontakt strana'
				]
			],
			'locale' => 'sr_RS'
		];
		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->d->dump($result->toArray());
	}

	public function testFullTextSearch()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'filter' => [
				's' => 'strana'
			],
			'locale' => 'sr_RS'
		];
		$command = $this->app->make(\Congraph\Eav\Commands\Entities\EntityGetCommand::class);
		$command->setParams($params);
		
		$result = $this->dispatch($command);
		
		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		// $this->d->dump($result->toArray());
	}

}