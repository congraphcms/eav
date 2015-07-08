<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Handlers;

use Illuminate\Support\ServiceProvider;

use Cookbook\Eav\Handlers\Commands\Attributes\AttributeCreateHandler;
use Cookbook\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler;
use Cookbook\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler;
use Cookbook\Eav\Handlers\Commands\Attributes\AttributeFetchHandler;
use Cookbook\Eav\Handlers\Commands\Attributes\AttributeGetHandler;
use Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler;
use Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler;
use Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler;
use Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler;
use Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler;

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
	 * The event listener mappings for package.
	 *
	 * @var array
	 */
	protected $listen = [
		// 'Cookbook\Eav\Events\AttributeSets\AfterAttributeSetFetch' => [
		// 	'Cookbook\Eav\Handlers\Events\AttributeSets\AfterAttributeSetFetchHandler',
		// ],
	];


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
		var_dump('Registering command handlers');
		$mappings = [
			// Attributes
			'Cookbook\Eav\Commands\Attributes\AttributeCreateCommand' => 
				'Cookbook\Eav\Handlers\Commands\Attributes\AttributeCreateHandler@handle',
			'Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand' => 
				'Cookbook\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler@handle',
			'Cookbook\Eav\Commands\Attributes\AttributeDeleteCommand' => 
				'Cookbook\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler@handle',
			'Cookbook\Eav\Commands\Attributes\AttributeFetchCommand' => 
				'Cookbook\Eav\Handlers\Commands\Attributes\AttributeFetchHandler@handle',
			'Cookbook\Eav\Commands\Attributes\AttributeGetCommand' => 
				'Cookbook\Eav\Handlers\Commands\Attributes\AttributeGetHandler@handle',

			// Attribute sets
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetCreateCommand' => 
				'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler@handle',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetUpdateCommand' => 
				'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler@handle',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetDeleteCommand' => 
				'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler@handle',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetFetchCommand' => 
				'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler@handle',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand' => 
				'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler@handle',
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->maps($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerCommandHandlers() {
		
		$this->app->bind('Cookbook\Eav\Handlers\Commands\Attributes\AttributeCreateHandler', function($app){
			return new AttributeCreateHandler($app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler', function($app){
			return new AttributeUpdateHandler($app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler', function($app){
			return new AttributeDeleteHandler($app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\Attributes\AttributeFetchHandler', function($app){
			return new AttributeFetchHandler($app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\Attributes\AttributeGetHandler', function($app){
			return new AttributeGetHandler($app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler', function($app){
			return new AttributeSetCreateHandler($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler', function($app){
			return new AttributeSetUpdateHandler($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler', function($app){
			return new AttributeSetDeleteHandler($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler', function($app){
			return new AttributeSetFetchHandler($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler', function($app){
			return new AttributeSetGetHandler($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
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
			'Cookbook\Eav\Handlers\Commands\Attributes\AttributeCreateHandler',
			'Cookbook\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler',
			'Cookbook\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler',
			'Cookbook\Eav\Handlers\Commands\Attributes\AttributeFetchHandler',
			'Cookbook\Eav\Handlers\Commands\Attributes\AttributeGetHandler',
			'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler',
			'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler',
			'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler',
			'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler',
			'Cookbook\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler'
		];
	}
}