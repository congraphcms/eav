<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Handlers;

use Illuminate\Support\ServiceProvider;

use Congraph\Eav\Handlers\Commands\Attributes\AttributeCreateHandler;
use Congraph\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler;
use Congraph\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler;
use Congraph\Eav\Handlers\Commands\Attributes\AttributeFetchHandler;
use Congraph\Eav\Handlers\Commands\Attributes\AttributeGetHandler;

use Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler;
use Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler;
use Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler;
use Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler;
use Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler;

use Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeCreateHandler;
use Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeUpdateHandler;
use Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeDeleteHandler;
use Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeFetchHandler;
use Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeGetHandler;

use Congraph\Eav\Handlers\Commands\Entities\EntityCreateHandler;
use Congraph\Eav\Handlers\Commands\Entities\EntityUpdateHandler;
use Congraph\Eav\Handlers\Commands\Entities\EntityDeleteHandler;
use Congraph\Eav\Handlers\Commands\Entities\EntityFetchHandler;
use Congraph\Eav\Handlers\Commands\Entities\EntityGetHandler;

/**
 * HandlersServiceProvider service provider for handlers
 * 
 * It will register all handlers to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
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
		// 'Congraph\Eav\Events\AttributeSets\AfterAttributeSetFetch' => [
		// 	'Congraph\Eav\Handlers\Events\AttributeSets\AfterAttributeSetFetchHandler',
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
		
		$mappings = [
			// Attributes
			'Congraph\Eav\Commands\Attributes\AttributeCreateCommand' => 
				'Congraph\Eav\Handlers\Commands\Attributes\AttributeCreateHandler@handle',
			'Congraph\Eav\Commands\Attributes\AttributeUpdateCommand' => 
				'Congraph\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler@handle',
			'Congraph\Eav\Commands\Attributes\AttributeDeleteCommand' => 
				'Congraph\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler@handle',
			'Congraph\Eav\Commands\Attributes\AttributeFetchCommand' => 
				'Congraph\Eav\Handlers\Commands\Attributes\AttributeFetchHandler@handle',
			'Congraph\Eav\Commands\Attributes\AttributeGetCommand' => 
				'Congraph\Eav\Handlers\Commands\Attributes\AttributeGetHandler@handle',

			// Attribute sets
			'Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand' => 
				'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler@handle',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand' => 
				'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler@handle',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand' => 
				'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler@handle',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand' => 
				'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler@handle',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand' => 
				'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler@handle',

			// Entity types
			'Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand' => 
				'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeCreateHandler@handle',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeUpdateCommand' => 
				'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeUpdateHandler@handle',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeDeleteCommand' => 
				'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeDeleteHandler@handle',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand' => 
				'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeFetchHandler@handle',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand' => 
				'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeGetHandler@handle',

			// Entities
			'Congraph\Eav\Commands\Entities\EntityCreateCommand' => 
				'Congraph\Eav\Handlers\Commands\Entities\EntityCreateHandler@handle',
			'Congraph\Eav\Commands\Entities\EntityUpdateCommand' => 
				'Congraph\Eav\Handlers\Commands\Entities\EntityUpdateHandler@handle',
			'Congraph\Eav\Commands\Entities\EntityDeleteCommand' => 
				'Congraph\Eav\Handlers\Commands\Entities\EntityDeleteHandler@handle',
			'Congraph\Eav\Commands\Entities\EntityFetchCommand' => 
				'Congraph\Eav\Handlers\Commands\Entities\EntityFetchHandler@handle',
			'Congraph\Eav\Commands\Entities\EntityGetCommand' => 
				'Congraph\Eav\Handlers\Commands\Entities\EntityGetHandler@handle',
			
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->maps($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerCommandHandlers() {
		
		// Attributes
		
		$this->app->bind('Congraph\Eav\Handlers\Commands\Attributes\AttributeCreateHandler', function($app){
			return new AttributeCreateHandler($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler', function($app){
			return new AttributeUpdateHandler($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler', function($app){
			return new AttributeDeleteHandler(
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')	
			);
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\Attributes\AttributeFetchHandler', function($app){
			return new AttributeFetchHandler($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\Attributes\AttributeGetHandler', function($app){
			return new AttributeGetHandler($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});


		// Attribute sets
		
		$this->app->bind('Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler', function($app){
			return new AttributeSetCreateHandler($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler', function($app){
			return new AttributeSetUpdateHandler($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler', function($app){
			return new AttributeSetDeleteHandler(
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')
			);
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler', function($app){
			return new AttributeSetFetchHandler($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler', function($app){
			return new AttributeSetGetHandler($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});


		// Entity types
		
		$this->app->bind('Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeCreateHandler', function($app){
			return new EntityTypeCreateHandler($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeUpdateHandler', function($app){
			return new EntityTypeUpdateHandler($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeDeleteHandler', function($app){
			return new EntityTypeDeleteHandler(
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')	
			);
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeFetchHandler', function($app){
			return new EntityTypeFetchHandler($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeGetHandler', function($app){
			return new EntityTypeGetHandler($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		// Entities
		
		$this->app->bind('Congraph\Eav\Handlers\Commands\Entities\EntityCreateHandler', function($app){
			return new EntityCreateHandler($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Handlers\Commands\Entities\EntityUpdateHandler', function($app){
			return new EntityUpdateHandler($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Handlers\Commands\Entities\EntityDeleteHandler', function($app){
			return new EntityDeleteHandler($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Handlers\Commands\Entities\EntityFetchHandler', function($app){
			return new EntityFetchHandler($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Handlers\Commands\Entities\EntityGetHandler', function($app){
			return new EntityGetHandler($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
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
			// attributes
			'Congraph\Eav\Handlers\Commands\Attributes\AttributeCreateHandler',
			'Congraph\Eav\Handlers\Commands\Attributes\AttributeUpdateHandler',
			'Congraph\Eav\Handlers\Commands\Attributes\AttributeDeleteHandler',
			'Congraph\Eav\Handlers\Commands\Attributes\AttributeFetchHandler',
			'Congraph\Eav\Handlers\Commands\Attributes\AttributeGetHandler',

			// Attribute sets
			'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetCreateHandler',
			'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetUpdateHandler',
			'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetDeleteHandler',
			'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetFetchHandler',
			'Congraph\Eav\Handlers\Commands\AttributeSets\AttributeSetGetHandler',

			// Entity types
			'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeCreateHandler',
			'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeUpdateHandler',
			'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeDeleteHandler',
			'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeFetchHandler',
			'Congraph\Eav\Handlers\Commands\EntityTypes\EntityTypeGetHandler',

			// Entities
			'Congraph\Eav\Handlers\Commands\Entities\EntityCreateHandler',
			'Congraph\Eav\Handlers\Commands\Entities\EntityUpdateHandler',
			'Congraph\Eav\Handlers\Commands\Entities\EntityDeleteHandler',
			'Congraph\Eav\Handlers\Commands\Entities\EntityFetchHandler',
			'Congraph\Eav\Handlers\Commands\Entities\EntityGetHandler',
		];
	}
}