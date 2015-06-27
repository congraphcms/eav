<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Handlers;

use Illuminate\Support\ServiceProvider;

use Cookbook\EAV\Handlers\Commands\AttributeCreateHandler;
use Cookbook\EAV\Handlers\Commands\AttributeUpdateHandler;

/**
 * HandlersServiceProvider service provider for handlers
 * 
 * It will register all handlers to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class HandlersServiceProvider extends ServiceProvider {

	/**
	 * Boot
	 * 
	 * @return void
	 */
	public function boot() {
		$this->mapCommandHandlers();
	}


	/**
	 * Register
	 * 
	 * @return void
	 */
	public function register() {
		$this->registerCommandHandlers();
	}

	/**
	 * Maps Command Handlers
	 *
	 * @return void
	 */
	public function mapCommandHandlers() {
		
		$mappings = [
			'Cookbook\EAV\Commands\AttributeCreateCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeCreateHandler@handle',
			'Cookbook\EAV\Commands\AttributeUpdateCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeUpdateHandler@handle'
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->maps($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerCommandHandlers() {
		
		$this->app->bind('Cookbook\EAV\Handlers\Commands\AttributeCreateHandler', function($app){
			return new AttributeCreateHandler($app->make('Cookbook\Contracts\EAV\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\EAV\Handlers\Commands\AttributeUpdateHandler', function($app){
			return new AttributeUpdateHandler($app->make('Cookbook\Contracts\EAV\AttributeRepositoryContract'));
		});
	}


	/**
     * Get the services provided by the provider.
     *
     * @return array
     */
	public function provides()
	{
		return [
			'Cookbook\EAV\Handlers\Commands\AttributeCreateHandler',
			'Cookbook\EAV\Handlers\Commands\AttributeUpdateHandler'
		];
	}
}