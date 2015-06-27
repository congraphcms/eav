<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV;

use Illuminate\Support\ServiceProvider;

/**
 * ManagersServiceProvider service provider for managers
 * 
 * It will register all manager to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EAVServiceProvider extends ServiceProvider {

	/**
	* Register
	* 
	* @return void
	*/
	public function register() {
		$this->mergeConfigFrom(realpath(__DIR__ . '/config/eav.php'), 'eav');
		$this->registerServiceProviders();
	}

	/**
	 * Boot
	 * 
	 * @return void
	 */
	public function boot() {
		$this->publishes([
			__DIR__.'/config/eav.php' => config_path('eav.php'),
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
		$this->app->register('Cookbook\EAV\Managers\ManagersServiceProvider');

		// Fields
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\EAV\Fields\FieldsServiceProvider');

		// Commands
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\EAV\Commands\CommandsServiceProvider');

		// Handlers
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\EAV\Handlers\HandlersServiceProvider');

		// Validators
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\EAV\Validators\ValidatorsServiceProvider');

		// Repositories
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\EAV\Repositories\RepositoriesServiceProvider');

		// Core
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\Core\CoreServiceProvider');

		
	}

}