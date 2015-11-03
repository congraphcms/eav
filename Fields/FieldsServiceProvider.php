<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields;

use Illuminate\Support\ServiceProvider;

use Cookbook\Eav\Fields\Text\TextFieldHandler;

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
	 * Boot
	 * @return void
	 */
	public function boot()
	{
		$this->registerListeners();
	}

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
	* Register Event Listeners
	*
	* @return void
	*/
	protected function registerListeners()
	{
		$this->app['events']->listen('cb.after.file.delete', 'Cookbook\Eav\Fields\Asset\AssetFieldHandler@onFileDelete');
	}

	/**
	* Register the AttributeHandlerFactory
	*
	* @return void
	*/
	protected function registerFactories() {
		$this 	->app
				->singleton('Cookbook\Eav\Fields\FieldHandlerFactory', function($app){
					return new FieldHandlerFactory(
						$app['app'],
						$app->make('Cookbook\Eav\Managers\AttributeManager')
					);
				});

		$this->app->alias(
			'Cookbook\Eav\Fields\FieldHandlerFactory', 'Cookbook\Contracts\Eav\FieldHandlerFactoryContract'
		);

		$this 	->app
				->singleton('Cookbook\Eav\Fields\FieldValidatorFactory', function($app){
					return new FieldValidatorFactory(
						$app['app'],
						$app->make('Cookbook\Eav\Managers\AttributeManager')
					);
				});

		$this->app->alias(
			'Cookbook\Eav\Fields\FieldValidatorFactory', 'Cookbook\Contracts\Eav\FieldValidatorFactoryContract'
		);
	}

	/**
	* Register Field Handlers
	*
	* @return void
	*/
	protected function registerFieldHandlers() {
		$field_types = $this->app['config']->get('cookbook.field_types');

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
						$app->make('Cookbook\Eav\Managers\AttributeManager'),
						$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'),
						$settings['table']
					);
				});
			}
		}


		$this->app->bind('Cookbook\Eav\Fields\Asset\AssetFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Asset\AssetFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Cookbook\Contracts\Filesystem\FileRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Datetime\DatetimeFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Datetime\DatetimeFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Decimal\DecimalFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Decimal\DecimalFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Integer\IntegerFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Integer\IntegerFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Relation\RelationFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Relation\RelationFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\EntityRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Select\SelectFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Select\SelectFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Text\TextFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Text\TextFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Textarea\TextareaFieldHandler', function($app) {
			return new \Cookbook\Eav\Fields\Textarea\TextareaFieldHandler( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		
	}

	/**
	* Register Field Validators
	*
	* @return void
	*/
	protected function registerFieldValidators() {
		// $field_types = $this->app['config']->get('cookbook.field_types');

		// if( ! is_array($field_types) )
		// {
		// 	return;
		// }
		$this->app->bind('Cookbook\Eav\Fields\Asset\AssetFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Asset\AssetFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Cookbook\Contracts\Filesystem\FileRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Datetime\DatetimeFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Datetime\DatetimeFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Decimal\DecimalFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Decimal\DecimalFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Integer\IntegerFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Integer\IntegerFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Relation\RelationFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Relation\RelationFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\EntityRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Select\SelectFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Select\SelectFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Text\TextFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Text\TextFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Cookbook\Eav\Fields\Textarea\TextareaFieldValidator', function($app) {
			return new \Cookbook\Eav\Fields\Textarea\TextareaFieldValidator( 
				$app['db']->connection(),
				$app->make('Cookbook\Eav\Managers\AttributeManager'),
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract')
			);
		});

		// foreach ($field_types as $type => $settings)
		// {
		// 	if( isset($settings['validator']) )
		// 	{
		// 		$this->app->bind($settings['validator'], function($app) use($settings){

		// 			return new $settings['validator']( 
		// 				$app['db']->connection(),
		// 				$app->make('Cookbook\Eav\Managers\AttributeManager'),
		// 				$settings['table']
		// 			);
		// 		});
		// 	}
			
		// }
		
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		$provides = [
			'Cookbook\Eav\Fields\FieldHandlerFactory',
			'Cookbook\Contracts\Eav\FieldHandlerFactoryContract'
		];

		$field_types = $this->app['config']->get('cookbook');

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