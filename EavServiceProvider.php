<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav;

use Congraph\Eav\Commands\CommandsServiceProvider;
use Congraph\Eav\Fields\FieldsServiceProvider;
use Congraph\Eav\Managers\ManagersServiceProvider;
use Congraph\Eav\Repositories\RepositoriesServiceProvider;
use Congraph\Eav\Services\ServicesServiceProvider;
use Congraph\Eav\Validators\ValidatorsServiceProvider;
use Illuminate\Support\ServiceProvider;

/**
 * EavServiceProvider service provider for EAV package
 * 
 * It will register all manager to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EavServiceProvider extends ServiceProvider {

	/**
	* Register
	* 
	* @return void
	*/
	public function register() {
		$this->mergeConfigFrom(realpath(__DIR__ . '/config/config.php'), 'cb.eav');
		$this->registerServiceProviders();
	}

	/**
	 * Boot
	 * 
	 * @return void
	 */
	public function boot() {
		$this->publishes([
			__DIR__.'/config/config.php' => config_path('cb.eav.php'),
			__DIR__.'/database/migrations' => database_path('/migrations'),
		]);
	}

	/**
	 * Register Service Providers for this package
	 * 
	 * @return void
	 */
	protected function registerServiceProviders(){
		// Managers
		// -----------------------------------------------------------------------------
		$this->app->register(ManagersServiceProvider::class);

		// Fields
		// -----------------------------------------------------------------------------
		$this->app->register(FieldsServiceProvider::class);

		// Commands
		// -----------------------------------------------------------------------------
		$this->app->register(CommandsServiceProvider::class);

		// Validators
		// -----------------------------------------------------------------------------
		$this->app->register(ValidatorsServiceProvider::class);

		// Services
		// -----------------------------------------------------------------------------
		$this->app->register(ServicesServiceProvider::class);

		// Repositories
		// -----------------------------------------------------------------------------
		$this->app->register(RepositoriesServiceProvider::class);

		

		
	}

}
