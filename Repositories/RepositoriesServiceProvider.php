<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Repositories;

use Illuminate\Support\ServiceProvider;

/**
 * RepositoriesServiceProvider service provider for managers
 * 
 * It will register all manager to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RepositoriesServiceProvider extends ServiceProvider {

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
		$this->mapObjectResolvers();
	}


	/**
	 * Register
	 * 
	 * @return void
	 */
	public function register()
	{
		$this->registerRepositories();
		$this->registerListeners();
		
	}

	/**
	 * Register Event Listeners
	 *
	 * @return void
	 */
	protected function registerListeners()
	{
		// $this->app['events']->listen('cb.after.entity.type.create', 'Congraph\Eav\Repositories\EntityElasticRepository@onEntityTypeCreated');
		// $this->app['events']->listen('cb.before.entity.type.update', 'Congraph\Eav\Repositories\EntityElasticRepository@onBeforeEntityTypeUpdated');
		// $this->app['events']->listen('cb.after.entity.type.update', 'Congraph\Eav\Repositories\EntityElasticRepository@onEntityTypeUpdated');
		// $this->app['events']->listen('cb.before.entity.type.delete', 'Congraph\Eav\Repositories\EntityElasticRepository@onBeforeEntityTypeDeleted');
		// $this->app['events']->listen('cb.after.entity.type.delete', 'Congraph\Eav\Repositories\EntityElasticRepository@onEntityTypeDeleted');
	}

	/**
	 * Register the Attribute Repository
	 *
	 * @return void
	 */
	public function registerRepositories() {
		$this->app->singleton('Congraph\Eav\Repositories\AttributeRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new AttributeRepository(
				$app['db']->connection(),
				$app->make('Congraph\Contracts\Eav\FieldHandlerFactoryContract'),
				$app->make('Congraph\Eav\Managers\AttributeManager')
			);
		});

		$this->app->alias(
			'Congraph\Eav\Repositories\AttributeRepository', 'Congraph\Contracts\Eav\AttributeRepositoryContract'
		);



		$this->app->singleton('Congraph\Eav\Repositories\AttributeSetRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new AttributeSetRepository( 
				$app['db']->connection()
			);
		});

		$this->app->alias(
			'Congraph\Eav\Repositories\AttributeSetRepository', 'Congraph\Contracts\Eav\AttributeSetRepositoryContract'
		);

		$this->app->singleton('Congraph\Eav\Repositories\EntityTypeRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new EntityTypeRepository(
				$app['db']->connection()
			);
		});

		$this->app->alias(
			'Congraph\Eav\Repositories\EntityTypeRepository', 'Congraph\Contracts\Eav\EntityTypeRepositoryContract'
		);

		$this->app->singleton('Congraph\Eav\Repositories\EntityRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new EntityRepository(
				$app['db']->connection(),
				$app->make('Congraph\Contracts\Eav\FieldHandlerFactoryContract'),
				$app->make('Congraph\Eav\Managers\AttributeManager'),
				$app->make('Congraph\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Congraph\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Congraph\Contracts\Workflows\WorkflowPointRepositoryContract'),
				$app->make('Congraph\Contracts\Locales\LocaleRepositoryContract')
			);
		});

		$this->app->alias(
			'Congraph\Eav\Repositories\EntityRepository', 'Congraph\Contracts\Eav\EntityRepositoryContract'
		);
	}

	/**
	 * Map repositories to object resolver
	 *
	 * @return void
	 */
	public function mapObjectResolvers() {
		$mappings = [
			'attribute' => 'Congraph\Eav\Repositories\AttributeRepository',
			'attribute-set' => 'Congraph\Eav\Repositories\AttributeSetRepository',
			'entity-type' => 'Congraph\Eav\Repositories\EntityTypeRepository',
			'entity' => 'Congraph\Eav\Repositories\EntityRepository',
		];

		$this->app->make('Congraph\Contracts\Core\ObjectResolverContract')->maps($mappings);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'Congraph\Eav\Repositories\AttributeRepository',
			'Congraph\Contracts\Eav\AttributeRepositoryContract',
			'Congraph\Eav\Repositories\AttributeSetRepository',
			'Congraph\Contracts\Eav\AttributeSetRepositoryContract',
			'Congraph\Eav\Repositories\EntityTypeRepository',
			'Congraph\Contracts\Eav\EntityTypeRepositoryContract',
			'Congraph\Eav\Repositories\EntityRepository',
			'Congraph\Contracts\Eav\EntityRepositoryContract'
		];
	}


}