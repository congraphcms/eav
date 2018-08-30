<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Validators;

use Illuminate\Support\ServiceProvider;

use Congraph\Eav\Validators\Attributes\AttributeCreateValidator;
use Congraph\Eav\Validators\Attributes\AttributeUpdateValidator;
use Congraph\Eav\Validators\Attributes\AttributeDeleteValidator;
use Congraph\Eav\Validators\Attributes\AttributeFetchValidator;
use Congraph\Eav\Validators\Attributes\AttributeGetValidator;

use Congraph\Eav\Validators\AttributeSets\AttributeSetCreateValidator;
use Congraph\Eav\Validators\AttributeSets\AttributeSetUpdateValidator;
use Congraph\Eav\Validators\AttributeSets\AttributeSetDeleteValidator;
use Congraph\Eav\Validators\AttributeSets\AttributeSetFetchValidator;
use Congraph\Eav\Validators\AttributeSets\AttributeSetGetValidator;

use Congraph\Eav\Validators\EntityTypes\EntityTypeCreateValidator;
use Congraph\Eav\Validators\EntityTypes\EntityTypeUpdateValidator;
use Congraph\Eav\Validators\EntityTypes\EntityTypeDeleteValidator;
use Congraph\Eav\Validators\EntityTypes\EntityTypeFetchValidator;
use Congraph\Eav\Validators\EntityTypes\EntityTypeGetValidator;

use Congraph\Eav\Validators\Entities\EntityCreateValidator;
use Congraph\Eav\Validators\Entities\EntityUpdateValidator;
use Congraph\Eav\Validators\Entities\EntityDeleteValidator;
use Congraph\Eav\Validators\Entities\EntityFetchValidator;
use Congraph\Eav\Validators\Entities\EntityGetValidator;

/**
 * ValidatorsServiceProvider service provider for validators
 * 
 * It will register all validators to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
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
			'Congraph\Eav\Commands\Attributes\AttributeCreateCommand' => 
				'Congraph\Eav\Validators\Attributes\AttributeCreateValidator@validate',
			'Congraph\Eav\Commands\Attributes\AttributeUpdateCommand' => 
				'Congraph\Eav\Validators\Attributes\AttributeUpdateValidator@validate',
			'Congraph\Eav\Commands\Attributes\AttributeDeleteCommand' => 
				'Congraph\Eav\Validators\Attributes\AttributeDeleteValidator@validate',
			'Congraph\Eav\Commands\Attributes\AttributeFetchCommand' => 
				'Congraph\Eav\Validators\Attributes\AttributeFetchValidator@validate',
			'Congraph\Eav\Commands\Attributes\AttributeGetCommand' => 
				'Congraph\Eav\Validators\Attributes\AttributeGetValidator@validate',

			// Attribute sets
			'Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand' => 
				'Congraph\Eav\Validators\AttributeSets\AttributeSetCreateValidator@validate',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetUpdateCommand' => 
				'Congraph\Eav\Validators\AttributeSets\AttributeSetUpdateValidator@validate',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetDeleteCommand' => 
				'Congraph\Eav\Validators\AttributeSets\AttributeSetDeleteValidator@validate',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetFetchCommand' => 
				'Congraph\Eav\Validators\AttributeSets\AttributeSetFetchValidator@validate',
			'Congraph\Eav\Commands\AttributeSets\AttributeSetGetCommand' => 
				'Congraph\Eav\Validators\AttributeSets\AttributeSetGetValidator@validate',

			// Entity types
			'Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand' => 
				'Congraph\Eav\Validators\EntityTypes\EntityTypeCreateValidator@validate',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeUpdateCommand' => 
				'Congraph\Eav\Validators\EntityTypes\EntityTypeUpdateValidator@validate',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeDeleteCommand' => 
				'Congraph\Eav\Validators\EntityTypes\EntityTypeDeleteValidator@validate',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeFetchCommand' => 
				'Congraph\Eav\Validators\EntityTypes\EntityTypeFetchValidator@validate',
			'Congraph\Eav\Commands\EntityTypes\EntityTypeGetCommand' => 
				'Congraph\Eav\Validators\EntityTypes\EntityTypeGetValidator@validate',


			// Entities
			'Congraph\Eav\Commands\Entities\EntityCreateCommand' => 
				'Congraph\Eav\Validators\Entities\EntityCreateValidator@validate',
			'Congraph\Eav\Commands\Entities\EntityUpdateCommand' => 
				'Congraph\Eav\Validators\Entities\EntityUpdateValidator@validate',
			'Congraph\Eav\Commands\Entities\EntityDeleteCommand' => 
				'Congraph\Eav\Validators\Entities\EntityDeleteValidator@validate',
			'Congraph\Eav\Commands\Entities\EntityFetchCommand' => 
				'Congraph\Eav\Validators\Entities\EntityFetchValidator@validate',
			'Congraph\Eav\Commands\Entities\EntityGetCommand' => 
				'Congraph\Eav\Validators\Entities\EntityGetValidator@validate',
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
		$this->app->bind('Congraph\Eav\Validators\Attributes\AttributeCreateValidator', function($app){
			return new AttributeCreateValidator(
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\FieldValidatorFactoryContract')
			);
		});

		$this->app->bind('Congraph\Eav\Validators\Attributes\AttributeUpdateValidator', function($app){
			return new AttributeUpdateValidator(
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});

		$this->app->bind('Congraph\Eav\Validators\Attributes\AttributeDeleteValidator', function($app){
			return new AttributeDeleteValidator($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Validators\Attributes\AttributeFetchValidator', function($app){
			return new AttributeFetchValidator($app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Validators\Attributes\AttributeGetValidator', function($app){
			return new AttributeGetValidator();
		});


		// Attribute sets
		$this->app->bind('Congraph\Eav\Validators\AttributeSets\AttributeSetCreateValidator', function($app){
			return new AttributeSetCreateValidator(
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});

		$this->app->bind('Congraph\Eav\Validators\AttributeSets\AttributeSetUpdateValidator', function($app){
			return new AttributeSetUpdateValidator(
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});

		$this->app->bind('Congraph\Eav\Validators\AttributeSets\AttributeSetDeleteValidator', function($app){
			return new AttributeSetDeleteValidator($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Validators\AttributeSets\AttributeSetFetchValidator', function($app){
			return new AttributeSetFetchValidator($app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Validators\AttributeSets\AttributeSetGetValidator', function($app){
			return new AttributeSetGetValidator();
		});


		// Entity types
		$this->app->bind('Congraph\Eav\Validators\EntityTypes\EntityTypeCreateValidator', function($app){
			return new EntityTypeCreateValidator();
		});

		$this->app->bind('Congraph\Eav\Validators\EntityTypes\EntityTypeUpdateValidator', function($app){
			return new EntityTypeUpdateValidator($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Validators\EntityTypes\EntityTypeDeleteValidator', function($app){
			return new EntityTypeDeleteValidator($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Validators\EntityTypes\EntityTypeFetchValidator', function($app){
			return new EntityTypeFetchValidator($app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'));
		});

		$this->app->bind('Congraph\Eav\Validators\EntityTypes\EntityTypeGetValidator', function($app){
			return new EntityTypeGetValidator();
		});


		// Entities
		$this->app->bind('Congraph\Eav\Validators\Entities\EntityCreateValidator', function($app){
			return new EntityCreateValidator(
				$app->make('Congraph\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Locales\LocaleRepositoryContract'),
				$app->make('Congraph\Contracts\Workflows\WorkflowPointRepositoryContract'),
				$app->make('Congraph\Eav\Managers\AttributeManager')
			);
		});

		$this->app->bind('Congraph\Eav\Validators\Entities\EntityUpdateValidator', function($app){
			return new EntityUpdateValidator(
				$app->make('Congraph\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Locales\LocaleRepositoryContract'),
				$app->make('Congraph\Contracts\Workflows\WorkflowPointRepositoryContract'),
				$app->make('Congraph\Eav\Managers\AttributeManager')
			);
		});

		$this->app->bind('Congraph\Eav\Validators\Entities\EntityDeleteValidator', function($app){
			return new EntityDeleteValidator(
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Validators\Entities\EntityFetchValidator', function($app){
			return new EntityFetchValidator(
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract'),
				$app->make('Congraph\Contracts\Locales\LocaleRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Validators\Entities\EntityGetValidator', function($app){
			return new EntityGetValidator(
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\FieldValidatorFactoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Locales\LocaleRepositoryContract')
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
			'Congraph\Eav\Validators\Attributes\AttributeCreateValidator',
			'Congraph\Eav\Validators\Attributes\AttributeUpdateValidator',
			'Congraph\Eav\Validators\Attributes\AttributeDeleteValidator',
			'Congraph\Eav\Validators\Attributes\AttributeFetchValidator',
			'Congraph\Eav\Validators\Attributes\AttributeGetValidator',

			// Attribute sets
			'Congraph\Eav\Validators\AttributeSets\AttributeSetCreateValidator',
			'Congraph\Eav\Validators\AttributeSets\AttributeSetUpdateValidator',
			'Congraph\Eav\Validators\AttributeSets\AttributeSetDeleteValidator',
			'Congraph\Eav\Validators\AttributeSets\AttributeSetFetchValidator',
			'Congraph\Eav\Validators\AttributeSets\AttributeSetGetValidator',

			// Entity types
			'Congraph\Eav\Validators\EntityTypes\EntityTypeCreateValidator',
			'Congraph\Eav\Validators\EntityTypes\EntityTypeUpdateValidator',
			'Congraph\Eav\Validators\EntityTypes\EntityTypeDeleteValidator',
			'Congraph\Eav\Validators\EntityTypes\EntityTypeFetchValidator',
			'Congraph\Eav\Validators\EntityTypes\EntityTypeGetValidator',

			// Entities
			'Congraph\Eav\Validators\Entities\EntityCreateValidator',
			'Congraph\Eav\Validators\Entities\EntityUpdateValidator',
			'Congraph\Eav\Validators\Entities\EntityDeleteValidator',
			'Congraph\Eav\Validators\Entities\EntityFetchValidator',
			'Congraph\Eav\Validators\Entities\EntityGetValidator'

		];
	}
}