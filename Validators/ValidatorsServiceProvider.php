<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Validators;

use Illuminate\Support\ServiceProvider;

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
			'Cookbook\EAV\Commands\AttributeCreateCommand' => 'Cookbook\EAV\Validators\AttributeCreateValidator@validate',
			'Cookbook\EAV\Commands\AttributeUpdateCommand' => 'Cookbook\EAV\Validators\AttributeUpdateValidator@validate',
			'Cookbook\EAV\Commands\AttributeDeleteCommand' => 'Cookbook\EAV\Validators\AttributeDeleteValidator@validate',
			'Cookbook\EAV\Commands\AttributeFetchCommand' => 'Cookbook\EAV\Validators\AttributeFetchValidator@validate',
			'Cookbook\EAV\Commands\AttributeGetCommand' => 'Cookbook\EAV\Validators\AttributeGetValidator@validate'
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->mapValidators($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerValidators() {
		$this->app->bind('Cookbook\EAV\Validators\AttributeCreateValidator', function($app){
			return new AttributeCreateValidator(
				$app->make('Cookbook\EAV\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\EAV\FieldValidatorFactoryContract')
			);
		});

		$this->app->bind('Cookbook\EAV\Validators\AttributeUpdateValidator', function($app){
			return new AttributeUpdateValidator(
				$app->make('Cookbook\EAV\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\EAV\FieldValidatorFactoryContract')
			);
		});

		$this->app->bind('Cookbook\EAV\Validators\AttributeDeleteValidator', function($app){
			return new AttributeDeleteValidator();
		});

		$this->app->bind('Cookbook\EAV\Validators\AttributeFetchValidator', function($app){
			return new AttributeFetchValidator();
		});

		$this->app->bind('Cookbook\EAV\Validators\AttributeGetValidator', function($app){
			return new AttributeGetValidator();
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
			'Cookbook\EAV\Validators\AttributeCreateValidator',
			'Cookbook\EAV\Validators\AttributeUpdateValidator',
			'Cookbook\EAV\Validators\AttributeDeleteValidator',
			'Cookbook\EAV\Validators\AttributeFetchValidator',
			'Cookbook\EAV\Validators\AttributeGetValidator'
		];
	}
}