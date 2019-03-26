<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields;

use Illuminate\Support\ServiceProvider;

/**
 * FieldsServiceProvider service provider for handlers
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
		$this->app['events']->listen('cb.after.file.delete', 'Congraph\Eav\Fields\Asset\AssetFieldHandler@onFileDelete');
		$this->app['events']->listen('cb.before.entity.update', 'Congraph\Eav\Fields\Compound\CompoundFieldHandler@onBeforeEntityUpdate');
		$this->app['events']->listen('cb.after.entity.update', 'Congraph\Eav\Fields\Compound\CompoundFieldHandler@onAfterEntityUpdate');
		$this->app['events']->listen('cb.before.entity.get', 'Congraph\Eav\Fields\Node\NodeFieldHandler@onBeforeEntityGet');
		$this->app['events']->listen('cb.before.entity.fetch', 'Congraph\Eav\Fields\Node\NodeFieldHandler@onBeforeEntityGet');
	}

	/**
	* Register the AttributeHandlerFactory
	*
	* @return void
	*/
	protected function registerFactories() {
		$this 	->app
				->singleton('Congraph\Eav\Fields\FieldHandlerFactory', function($app){
					return new FieldHandlerFactory(
						$app['app'],
						$app->make('Congraph\Eav\Managers\AttributeManager')
					);
				});

		$this->app->alias(
			'Congraph\Eav\Fields\FieldHandlerFactory', 'Congraph\Contracts\Eav\FieldHandlerFactoryContract'
		);

		$this 	->app
				->singleton('Congraph\Eav\Fields\FieldValidatorFactory', function($app){
					return new FieldValidatorFactory(
						$app['app'],
						$app->make('Congraph\Eav\Managers\AttributeManager')
					);
				});

		$this->app->alias(
			'Congraph\Eav\Fields\FieldValidatorFactory', 'Congraph\Contracts\Eav\FieldValidatorFactoryContract'
		);
	}

	/**
	* Register Field Handlers
	*
	* @return void
	*/
	protected function registerFieldHandlers() {

		$this->app->singleton('Congraph\Eav\Fields\Asset\AssetFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Asset\AssetFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Filesystem\FileRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Boolean\BooleanFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Boolean\BooleanFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Datetime\DatetimeFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Datetime\DatetimeFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Decimal\DecimalFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Decimal\DecimalFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Integer\IntegerFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Integer\IntegerFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Relation\RelationFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Relation\RelationFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Select\SelectFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Select\SelectFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Text\TextFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Text\TextFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Textarea\TextareaFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Textarea\TextareaFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Location\LocationFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Location\LocationFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Compound\CompoundFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Compound\CompoundFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')
			);
		});
		$this->app->singleton('Congraph\Eav\Fields\Node\NodeFieldHandler', function($app) {
			return new \Congraph\Eav\Fields\Node\NodeFieldHandler( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')
			);
		});
	}

	/**
	* Register Field Validators
	*
	* @return void
	*/
	protected function registerFieldValidators() {
		// $field_types = $this->app['config']->get('congraph.field_types');

		// if( ! is_array($field_types) )
		// {
		// 	return;
		// }
		$this->app->bind('Congraph\Eav\Fields\Asset\AssetFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Asset\AssetFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Filesystem\FileRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Boolean\BooleanFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Boolean\BooleanFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Datetime\DatetimeFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Datetime\DatetimeFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Decimal\DecimalFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Decimal\DecimalFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Integer\IntegerFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Integer\IntegerFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Relation\RelationFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Relation\RelationFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Select\SelectFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Select\SelectFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Text\TextFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Text\TextFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Textarea\TextareaFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Textarea\TextareaFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Location\LocationFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Location\LocationFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Compound\CompoundFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Compound\CompoundFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract')
			);
		});
		$this->app->bind('Congraph\Eav\Fields\Node\NodeFieldValidator', function($app) {
			return new \Congraph\Eav\Fields\Node\NodeFieldValidator( 
				$app['db']->connection(),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityRepositoryContract')
			);
		});

		// foreach ($field_types as $type => $settings)
		// {
		// 	if( isset($settings['validator']) )
		// 	{
		// 		$this->app->bind($settings['validator'], function($app) use($settings){

		// 			return new $settings['validator']( 
		// 				$app['db']->connection(),
		// 				$app->make('Congraph\Eav\Managers\AttributeManager'),
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
			'Congraph\Eav\Fields\FieldHandlerFactory',
			'Congraph\Contracts\Eav\FieldHandlerFactoryContract'
		];

		$field_types = $this->app['config']->get('congraph');

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