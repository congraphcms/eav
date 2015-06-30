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
use Cookbook\EAV\Handlers\Commands\AttributeDeleteHandler;
use Cookbook\EAV\Handlers\Commands\AttributeFetchHandler;
use Cookbook\EAV\Handlers\Commands\AttributeGetHandler;
use Cookbook\EAV\Handlers\Commands\AttributeSetCreateHandler;

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
			'Cookbook\EAV\Commands\AttributeUpdateCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeUpdateHandler@handle',
			'Cookbook\EAV\Commands\AttributeDeleteCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeDeleteHandler@handle',
			'Cookbook\EAV\Commands\AttributeFetchCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeFetchHandler@handle',
			'Cookbook\EAV\Commands\AttributeGetCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeGetHandler@handle',
			'Cookbook\EAV\Commands\AttributeSetCreateCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeSetCreateHandler@handle',
			'Cookbook\EAV\Commands\AttributeSetUpdateCommand' => 'Cookbook\EAV\Handlers\Commands\AttributeSetUpdateHandler@handle',
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

		$this->app->bind('Cookbook\EAV\Handlers\Commands\AttributeDeleteHandler', function($app){
			return new AttributeDeleteHandler($app->make('Cookbook\Contracts\EAV\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\EAV\Handlers\Commands\AttributeFetchHandler', function($app){
			return new AttributeFetchHandler($app->make('Cookbook\Contracts\EAV\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\EAV\Handlers\Commands\AttributeGetHandler', function($app){
			return new AttributeGetHandler($app->make('Cookbook\Contracts\EAV\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\EAV\Handlers\Commands\AttributeSetCreateHandler', function($app){
			return new AttributeSetCreateHandler($app->make('Cookbook\Contracts\EAV\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\EAV\Handlers\Commands\AttributeSetUpdateHandler', function($app){
			return new AttributeSetUpdateHandler($app->make('Cookbook\Contracts\EAV\AttributeSetRepositoryContract'));
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
			'Cookbook\EAV\Handlers\Commands\AttributeUpdateHandler',
			'Cookbook\EAV\Handlers\Commands\AttributeDeleteHandler',
			'Cookbook\EAV\Handlers\Commands\AttributeFetchHandler',
			'Cookbook\EAV\Handlers\Commands\AttributeGetHandler',
			'Cookbook\EAV\Handlers\Commands\AttributeSetCreateHandler',
			'Cookbook\EAV\Handlers\Commands\AttributeSetUpdateHandler'
		];
	}
}