<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Fields;

use Illuminate\Support\ServiceProvider;

use Cookbook\EAV\Fields\Text\TextFieldHandler;

/**
 * FieldsServiceProvider service provider for handlers
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
class FieldsServiceProvider extends ServiceProvider {

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
		$this->registerFactories();
		$this->registerFieldHandlers();
		$this->registerFieldValidators();
	}

	/**
	* Register the AttributeHandlerFactory
	*
	* @return void
	*/
	public function registerFactories() {
		$this 	->app
				->singleton('Cookbook\EAV\Fields\FieldHandlerFactory', function($app){
					return new FieldHandlerFactory(
						$app['app'],
						$app->make('Cookbook\EAV\Managers\AttributeManager')
					);
				});

		$this->app->alias(
			'Cookbook\EAV\Fields\FieldHandlerFactory', 'Cookbook\Contracts\EAV\FieldHandlerFactoryContract'
		);

		$this 	->app
				->singleton('Cookbook\EAV\Fields\FieldValidatorFactory', function($app){
					return new FieldValidatorFactory(
						$app['app'],
						$app->make('Cookbook\EAV\Managers\AttributeManager')
					);
				});

		$this->app->alias(
			'Cookbook\EAV\Fields\FieldValidatorFactory', 'Cookbook\Contracts\EAV\FieldValidatorFactoryContract'
		);
	}

	/**
	* Register Field Handlers
	*
	* @return void
	*/
	public function registerFieldHandlers() {
		$field_types = $this->app['config']->get('eav.field_types');

		if( ! is_array($field_types) )
		{
			return;
		}

		foreach ($field_types as $type => $settings)
		{
			if( isset($settings['handler']) )
			{
				$this->app->bind($settings['handler'], function($app) use($settings){

					return new $settings['handler'](
						$app['db']->connection(),
						$app->make('Cookbook\EAV\Managers\AttributeManager'),
						$settings['table']
					);
				});
			}
		}
		
	}

	/**
	* Register Field Validators
	*
	* @return void
	*/
	public function registerFieldValidators() {
		$field_types = $this->app['config']->get('eav.field_types');

		if( ! is_array($field_types) )
		{
			return;
		}

		foreach ($field_types as $type => $settings)
		{
			if( isset($settings['validator']) )
			{
				$this->app->bind($settings['validator'], function($app) use($settings){

					return new $settings['validator']( $app->make('Cookbook\EAV\Managers\AttributeManager') );
				});
			}
			
		}
		
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		$provides = [
			'Cookbook\EAV\Fields\FieldHandlerFactory',
			'Cookbook\Contracts\EAV\FieldHandlerFactoryContract'
		];

		$field_types = $this->app['config']->get('eav');

		if( ! is_array($field_types) )
		{
			return $provides;
		}

		foreach ($field_types as $type => $settings)
		{
			if( isset($settings['handler']) )
			{
				$provides[] = $settings['handler'];
			}

			if( isset($settings['validator']) )
			{
				$provides[] = $settings['validator'];
			}
		}


		return $provides;
	}
}