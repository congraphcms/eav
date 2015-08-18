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

			'Cookbook\Eav\Commands\AttributeSets\AttributeSetCreateCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetCreateValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetUpdateCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetUpdateValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetDeleteCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetDeleteValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetFetchCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetFetchValidator@validate',
			'Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand' => 
				'Cookbook\Eav\Validators\AttributeSets\AttributeSetGetValidator@validate'
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->mapValidators($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerValidators() {
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

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetCreateValidator', function($app){
			return new AttributeSetCreateValidator();
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetUpdateValidator', function($app){
			return new AttributeSetUpdateValidator();
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetDeleteValidator', function($app){
			return new AttributeSetDeleteValidator();
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetFetchValidator', function($app){
			return new AttributeSetFetchValidator();
		});

		$this->app->bind('Cookbook\Eav\Validators\AttributeSets\AttributeSetGetValidator', function($app){
			return new AttributeSetGetValidator();
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
			'Cookbook\Eav\Validators\Attributes\AttributeCreateValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeUpdateValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeDeleteValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeFetchValidator',
			'Cookbook\Eav\Validators\Attributes\AttributeGetValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetCreateValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetUpdateValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetDeleteValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetFetchValidator',
			'Cookbook\Eav\Validators\AttributeSets\AttributeSetGetValidator'
		];
	}
}