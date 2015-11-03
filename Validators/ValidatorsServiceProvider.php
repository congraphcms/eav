<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators;

use Illuminate\Support\ServiceProvider;

use Cookbook\Eav\Validators\Attributes\AttributeCreateValidator;
use Cookbook\Eav\Validators\Attributes\AttributeUpdateValidator;
use Cookbook\Eav\Validators\Attributes\AttributeDeleteValidator;
use Cookbook\Eav\Validators\Attributes\AttributeFetchValidator;
use Cookbook\Eav\Validators\Attributes\AttributeGetValidator;

use Cookbook\Eav\Validators\AttributeSets\AttributeSetCreateValidator;
use Cookbook\Eav\Validators\AttributeSets\AttributeSetUpdateValidator;
use Cookbook\Eav\Validators\AttributeSets\AttributeSetDeleteValidator;
use Cookbook\Eav\Validators\AttributeSets\AttributeSetFetchValidator;
use Cookbook\Eav\Validators\AttributeSets\AttributeSetGetValidator;

use Cookbook\Eav\Validators\EntityTypes\EntityTypeCreateValidator;
use Cookbook\Eav\Validators\EntityTypes\EntityTypeUpdateValidator;
use Cookbook\Eav\Validators\EntityTypes\EntityTypeDeleteValidator;
use Cookbook\Eav\Validators\EntityTypes\EntityTypeFetchValidator;
use Cookbook\Eav\Validators\EntityTypes\EntityTypeGetValidator;

use Cookbook\Eav\Validators\Entities\EntityCreateValidator;
use Cookbook\Eav\Validators\Entities\EntityUpdateValidator;
use Cookbook\Eav\Validators\Entities\EntityDeleteValidator;
use Cookbook\Eav\Validators\Entities\EntityFetchValidator;
use Cookbook\Eav\Validators\Entities\EntityGetValidator;

/**
 * ValidatorsServiceProvider service provider for validators
 * 
 * It will register all validators to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ValidatorsServiceProvider extends ServiceProvider {

	/**
	 * Boot
	 * 
	 * @return void
	 */
	public function boot() {
		$this->mapValidators();
	}


	/**
	 * Register
	 * 
	 * @return void
	 */
	public function register() {
		$this->registerValidators();
	}

	/**
	 * Maps Validators
	 *
	 * @return void
	 */
	public function mapValidators() {
		
		$mappings = [
			// Attributes
			'Cookbook\Eav\Commands\Attributes\AttributeCreateCommand' => 
				'Cookbook\Eav\Validators\Attributes\AttributeCreateValidator@validate',
			'Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand' => 
				'Cookbook\Eav\Validators\Attributes\AttributeUpdateValidator@validate',
			'Cookbook\Eav\Commands\Attributes\AttributeDeleteCommand' => 
				'Cookbook\Eav\Validators\Attributes\AttributeDeleteValidator@validate',
			'Cookbook\Eav\Commands\Attributes\AttributeFetchCommand' => 
				'Cookbook\Eav\Validators\Attributes\AttributeFetchValidator@validate',
			'Cookbook\Eav\Commands\Attributes\AttributeGetCommand' => 
				'Cookbook\Eav\Validators\Attributes\AttributeGetValidator@validate',

			// Attribute sets
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetCreateCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetCreateValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetUpdateCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetUpdateValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetDeleteCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetDeleteValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetFetchCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetFetchValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetGetValidator@validate',

			// Entity types
			'Cookbook\Eav\Commands\EntityTypes\EntityTypeCreateCommand' => 
				'Cookbook\Eav\Validators\EntityTypes\EntityTypeCreateValidator@validate',
			'Cookbook\Eav\Commands\EntityTypes\EntityTypeUpdateCommand' => 
				'Cookbook\Eav\Validators\EntityTypes\EntityTypeUpdateValidator@validate',
			'Cookbook\Eav\Commands\EntityTypes\EntityTypeDeleteCommand' => 
				'Cookbook\Eav\Validators\EntityTypes\EntityTypeDeleteValidator@validate',
			'Cookbook\Eav\Commands\EntityTypes\EntityTypeFetchCommand' => 
				'Cookbook\Eav\Validators\EntityTypes\EntityTypeFetchValidator@validate',
			'Cookbook\Eav\Commands\EntityTypes\EntityTypeGetCommand' => 
				'Cookbook\Eav\Validators\EntityTypes\EntityTypeGetValidator@validate',


			// Entities
			'Cookbook\Eav\Commands\Entities\EntityCreateCommand' => 
				'Cookbook\Eav\Validators\Entities\EntityCreateValidator@validate',
			'Cookbook\Eav\Commands\Entities\EntityUpdateCommand' => 
				'Cookbook\Eav\Validators\Entities\EntityUpdateValidator@validate',
			'Cookbook\Eav\Commands\Entities\EntityDeleteCommand' => 
				'Cookbook\Eav\Validators\Entities\EntityDeleteValidator@validate',
			'Cookbook\Eav\Commands\Entities\EntityFetchCommand' => 
				'Cookbook\Eav\Validators\Entities\EntityFetchValidator@validate',
			'Cookbook\Eav\Commands\Entities\EntityGetCommand' => 
				'Cookbook\Eav\Validators\Entities\EntityGetValidator@validate',
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->mapValidators($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerValidators() {

		// Attributes
		$this->app->bind('Cookbook\Eav\Validators\Attributes\AttributeCreateValidator', function($app){
			return new AttributeCreateValidator(
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\FieldValidatorFactoryContract')
			);
		});

		$this->app->bind('Cookbook\Eav\Validators\Attributes\AttributeUpdateValidator', function($app){
			return new AttributeUpdateValidator(
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});

		$this->app->bind('Cookbook\Eav\Validators\Attributes\AttributeDeleteValidator', function($app){
			return new AttributeDeleteValidator($app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\Attributes\AttributeFetchValidator', function($app){
			return new AttributeFetchValidator($app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\Attributes\AttributeGetValidator', function($app){
			return new AttributeGetValidator();
		});


		// Attribute sets
		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetCreateValidator', function($app){
			return new AttributeSetCreateValidator();
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetUpdateValidator', function($app){
			return new AttributeSetUpdateValidator($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetDeleteValidator', function($app){
			return new AttributeSetDeleteValidator($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetFetchValidator', function($app){
			return new AttributeSetFetchValidator($app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetGetValidator', function($app){
			return new AttributeSetGetValidator();
		});


		// Entity types
		$this->app->bind('Cookbook\Eav\Validators\EntityTypes\EntityTypeCreateValidator', function($app){
			return new EntityTypeCreateValidator();
		});

		$this->app->bind('Cookbook\Eav\Validators\EntityTypes\EntityTypeUpdateValidator', function($app){
			return new EntityTypeUpdateValidator($app->make('Cookbook\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\EntityTypes\EntityTypeDeleteValidator', function($app){
			return new EntityTypeDeleteValidator($app->make('Cookbook\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\EntityTypes\EntityTypeFetchValidator', function($app){
			return new EntityTypeFetchValidator($app->make('Cookbook\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Cookbook\Eav\Validators\EntityTypes\EntityTypeGetValidator', function($app){
			return new EntityTypeGetValidator();
		});


		// Entities
		$this->app->bind('Cookbook\Eav\Validators\Entities\EntityCreateValidator', function($app){
			return new EntityCreateValidator(
				$app->make('Cookbook\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Cookbook\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Cookbook\Eav\Managers\AttributeManager')
			);
		});

		$this->app->bind('Cookbook\Eav\Validators\Entities\EntityUpdateValidator', function($app){
			return new EntityUpdateValidator(
				$app->make('Cookbook\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Cookbook\Contracts\Eav\EntityRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});

		$this->app->bind('Cookbook\Eav\Validators\Entities\EntityDeleteValidator', function($app){
			return new EntityDeleteValidator(
				$app->make('Cookbook\Contracts\Eav\EntityRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Validators\Entities\EntityFetchValidator', function($app){
			return new EntityFetchValidator(
				$app->make('Cookbook\Contracts\Eav\EntityRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Validators\Entities\EntityGetValidator', function($app){
			return new EntityGetValidator(
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Cookbook\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
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
			// Attributes
			'Cookbook\Eav\Validators\Attributes\AttributeCreateValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeUpdateValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeDeleteValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeFetchValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeGetValidator',

			// Attribute sets
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetCreateValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetUpdateValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetDeleteValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetFetchValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetGetValidator',

			// Entity types
			'Cookbook\Eav\Validators\EntityTypes\EntityTypeCreateValidator',
			'Cookbook\Eav\Validators\EntityTypes\EntityTypeUpdateValidator',
			'Cookbook\Eav\Validators\EntityTypes\EntityTypeDeleteValidator',
			'Cookbook\Eav\Validators\EntityTypes\EntityTypeFetchValidator',
			'Cookbook\Eav\Validators\EntityTypes\EntityTypeGetValidator',

			// Entities
			'Cookbook\Eav\Validators\Entities\EntityCreateValidator',
			'Cookbook\Eav\Validators\Entities\EntityUpdateValidator',
			'Cookbook\Eav\Validators\Entities\EntityDeleteValidator',
			'Cookbook\Eav\Validators\Entities\EntityFetchValidator',
			'Cookbook\Eav\Validators\Entities\EntityGetValidator'

		];
	}
}