<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Commands;

use Illuminate\Support\ServiceProvider;

use Congraph\Eav\Commands\Attributes\AttributeCreateCommand;
use Congraph\Eav\Commands\Attributes\AttributeUpdateCommand;
use Congraph\Eav\Commands\Attributes\AttributeDeleteCommand;
use Congraph\Eav\Commands\Attributes\AttributeFetchCommand;
use Congraph\Eav\Commands\Attributes\AttributeGetCommand;

use Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand;
use Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand;
use Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand;
use Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand;
use Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand;

use Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand;
use Congraph\Eav\Commands\EntityTypes\EntityTypeUpdateCommand;
use Congraph\Eav\Commands\EntityTypes\EntityTypeDeleteCommand;
use Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand;
use Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand;

use Congraph\Eav\Commands\Entities\EntityCreateCommand;
use Congraph\Eav\Commands\Entities\EntityUpdateCommand;
use Congraph\Eav\Commands\Entities\EntityDeleteCommand;
use Congraph\Eav\Commands\Entities\EntityFetchCommand;
use Congraph\Eav\Commands\Entities\EntityGetCommand;

/**
 * CommandsServiceProvider service provider for commands
 * 
 * It will register all commands to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CommandsServiceProvider extends ServiceProvider {

	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
	protected $defer = true;


	/**
	* Register
	* 
	* @return void
	*/
	public function register() {
		$this->registerCommands();
	}

	/**
	* Register Command
	*
	* @return void
	*/
	public function registerCommands() {
		// Attributes
		
		$this->app->bind('Congraph\Eav\Commands\Attributes\AttributeCreateCommand', function($app){
			return new AttributeCreateCommand($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\Attributes\AttributeUpdateCommand', function($app){
			return new AttributeUpdateCommand($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\Attributes\AttributeDeleteCommand', function($app){
			return new AttributeDeleteCommand(
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')	
			);
		});

		$this->app->bind('Congraph\Eav\Commands\Attributes\AttributeFetchCommand', function($app){
			return new AttributeFetchCommand($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\Attributes\AttributeGetCommand', function($app){
			return new AttributeGetCommand($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});


		// Attribute sets
		
		$this->app->bind('Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand', function($app){
			return new AttributeSetCreateCommand($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand', function($app){
			return new AttributeSetUpdateCommand($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand', function($app){
			return new AttributeSetDeleteCommand(
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')
			);
		});

		$this->app->bind('Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand', function($app){
			return new AttributeSetFetchCommand($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand', function($app){
			return new AttributeSetGetCommand($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});


		// Entity types
		
		$this->app->bind('Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand', function($app){
			return new EntityTypeCreateCommand($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\EntityTypes\EntityTypeUpdateCommand', function($app){
			return new EntityTypeUpdateCommand($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\EntityTypes\EntityTypeDeleteCommand', function($app){
			return new EntityTypeDeleteCommand(
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')	
			);
		});

		$this->app->bind('Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand', function($app){
			return new EntityTypeFetchCommand($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand', function($app){
			return new EntityTypeGetCommand($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		// Entities
		
		$this->app->bind('Congraph\Eav\Commands\Entities\EntityCreateCommand', function($app){
			return new EntityCreateCommand($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Commands\Entities\EntityUpdateCommand', function($app){
			return new EntityUpdateCommand($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Commands\Entities\EntityDeleteCommand', function($app){
			return new EntityDeleteCommand($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Commands\Entities\EntityFetchCommand', function($app){
			return new EntityFetchCommand($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
		});
		$this->app->bind('Congraph\Eav\Commands\Entities\EntityGetCommand', function($app){
			return new EntityGetCommand($app->make('Congraph\Contracts\Eav\EntityRepositoryContract'));
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
			'Congraph\Eav\Commands\Attributes\AttributeCreateCommand',
			'Congraph\Eav\Commands\Attributes\AttributeUpdateCommand',
			'Congraph\Eav\Commands\Attributes\AttributeDeleteCommand',
			'Congraph\Eav\Commands\Attributes\AttributeFetchCommand',
			'Congraph\Eav\Commands\Attributes\AttributeGetCommand',

			// Attribute sets
			'Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand',

			// Entity types
			'Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeUpdateCommand',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeDeleteCommand',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand',

			// Entities
			'Congraph\Eav\Commands\Entities\EntityCreateCommand',
			'Congraph\Eav\Commands\Entities\EntityUpdateCommand',
			'Congraph\Eav\Commands\Entities\EntityDeleteCommand',
			'Congraph\Eav\Commands\Entities\EntityFetchCommand',
			'Congraph\Eav\Commands\Entities\EntityGetCommand',
		];
	}
}