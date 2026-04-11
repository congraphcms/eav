<?php

require_once __DIR__ . '/../database/seeders/ClearDB.php';
require_once __DIR__ . '/../database/seeders/EavDbSeeder.php';
require_once __DIR__ . '/../database/seeders/FileDbSeeder.php';
require_once __DIR__ . '/../database/seeders/helpers.php';
require_once __DIR__ . '/../database/seeders/LocaleDbSeeder.php';
require_once __DIR__ . '/../database/seeders/MixDbSeeder.php';
require_once __DIR__ . '/../database/seeders/SampleBlogCommandSeeder.php';
require_once __DIR__ . '/../database/seeders/SampleBlogSeeder.php';
require_once __DIR__ . '/../database/seeders/WorkflowDbSeeder.php';

use Congraph\Core\Bus\CommandDispatcher;
use Congraph\Core\CoreServiceProvider;
use Congraph\Eav\EavServiceProvider;
use Congraph\Filesystem\FilesystemServiceProvider;
use Congraph\Locales\LocalesServiceProvider;
use Congraph\Workflows\WorkflowsServiceProvider;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\ServiceProvider as InterventionImageServiceProvider;

abstract class DatabaseTestCase extends \Orchestra\Testbench\TestCase
{
	protected function getPackageProviders($app)
	{
		return [
			InterventionImageServiceProvider::class,
			CoreServiceProvider::class,
			LocalesServiceProvider::class,
			FilesystemServiceProvider::class,
			WorkflowsServiceProvider::class,
			EavServiceProvider::class,
		];
	}

	protected function defineEnvironment($app)
	{
		$driver = env('DB_CONNECTION', 'sqlite');

		$connection = [
			'driver' => $driver,
			'prefix' => '',
		];

		if ($driver === 'sqlite') {
			$connection['database'] = env('DB_DATABASE', ':memory:');
			$connection['foreign_key_constraints'] = true;
		} else {
			$connection['host'] = env('DB_HOST', '127.0.0.1');
			$connection['port'] = env('DB_PORT', $driver === 'pgsql' ? '5432' : '3306');
			$connection['database'] = env('DB_DATABASE', 'congraph_test');
			$connection['username'] = env('DB_USERNAME', 'congraph');
			$connection['password'] = env('DB_PASSWORD', 'congraph');
			$connection['charset'] = 'utf8';
			$connection['prefix_indexes'] = true;
			$connection['schema'] = env('DB_SCHEMA', 'public');
			$connection['sslmode'] = env('DB_SSLMODE', 'prefer');

			if ($driver === 'mysql') {
				$connection['charset'] = 'utf8mb4';
				$connection['collation'] = 'utf8mb4_unicode_ci';
			}
		}

		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', $connection);
		$app['config']->set('cache.default', 'file');
		$app['config']->set('app.timezone', 'Europe/Belgrade');
		$app['config']->set('cache.stores.file', [
			'driver' => 'file',
			'path' => realpath(__DIR__ . '/../storage/cache/') ?: __DIR__ . '/../storage/cache',
		]);
		$app['config']->set('filesystems.default', 'local');
		$app['config']->set('filesystems.disks.local', [
			'driver' => 'local',
			'root' => realpath(__DIR__ . '/../storage/') ?: __DIR__ . '/../storage',
		]);
	}

	protected function defineDatabaseMigrations()
	{
		$this->loadMigrationsFrom(realpath(__DIR__ . '/../../database/migrations'));
		$this->loadMigrationsFrom(realpath(__DIR__ . '/../../../filesystem/database/migrations'));
		$this->loadMigrationsFrom(realpath(__DIR__ . '/../../../locales/database/migrations'));
		$this->loadMigrationsFrom(realpath(__DIR__ . '/../../../workflows/database/migrations'));

		$this->artisan('migrate', ['--database' => 'testbench'])->run();

		$this->beforeApplicationDestroyed(function () {
			$this->artisan('migrate:reset', ['--database' => 'testbench'])->run();
		});
	}

	protected function setUp(): void
	{
		parent::setUp();

		foreach ($this->seeders() as $seeder) {
			$this->artisan('db:seed', ['--class' => $seeder]);
		}

		Storage::deleteDirectory('files');
		Storage::deleteDirectory('uploads');
		Storage::makeDirectory('files');
		Storage::makeDirectory('uploads');
		Storage::copy('temp/test.jpg', 'files/test.jpg');
		Storage::copy('temp/test.jpg', 'files/test2.jpg');
		Storage::copy('temp/test.jpg', 'uploads/test.jpg');
	}

	protected function tearDown(): void
	{
		foreach ($this->cleanupSeeders() as $seeder) {
			$this->artisan('db:seed', ['--class' => $seeder]);
		}

		Storage::deleteDirectory('files');
		Storage::deleteDirectory('uploads');

		$cacheFiles = Storage::files('cache');
		$cacheDirs = Storage::directories('cache');

		foreach ($cacheFiles as $file) {
			Storage::delete($file);
		}

		foreach ($cacheDirs as $dir) {
			Storage::deleteDirectory($dir);
		}

		parent::tearDown();
	}

	protected function seeders(): array
	{
		return [];
	}

	protected function cleanupSeeders(): array
	{
		return [
			Database\Seeders\ClearDB::class,
		];
	}

	protected function dispatch(object $command): mixed
	{
		return $this->app->make(CommandDispatcher::class)->dispatch($command);
	}
}
