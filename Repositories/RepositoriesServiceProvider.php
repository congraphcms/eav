<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Repositories;

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
 * @package 	cookbook/eav
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
	 * Register
	 * 
	 * @return void
	 */
	public function register() {
		$this->registerRepositories();
	}

	/**
	 * Register the Attribute Repository
	 *
	 * @return void
	 */
	public function registerRepositories() {
		$this->app->bind('Cookbook\Eav\Repositories\AttributeRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new AttributeRepository(
				$app['db']->connection(),
				$app->make('Cookbook\Contracts\Eav\FieldHandlerFactoryContract'),
				$app->make('Cookbook\Eav\Managers\AttributeManager')
			);
		});

		$this->app->alias(
			'Cookbook\Eav\Repositories\AttributeRepository', 'Cookbook\Contracts\Eav\AttributeRepositoryContract'
		);



		$this->app->bind('Cookbook\Eav\Repositories\AttributeSetRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new AttributeSetRepository( $app['db']->connection() );
		});

		$this->app->alias(
			'Cookbook\Eav\Repositories\AttributeSetRepository', 'Cookbook\Contracts\Eav\AttributeSetRepositoryContract'
		);

		$this->app->bind('Cookbook\Eav\Repositories\EntityTypeRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new EntityTypeRepository(
				$app['db']->connection(),
				$app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract')
			);
		});

		$this->app->alias(
			'Cookbook\Eav\Repositories\EntityTypeRepository', 'Cookbook\Contracts\Eav\EntityTypeRepositoryContract'
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'Cookbook\Eav\Repositories\AttributeRepository',
			'Cookbook\Contracts\Eav\AttributeRepositoryContract',
			'Cookbook\Eav\Repositories\AttributeSetRepository',
			'Cookbook\Contracts\Eav\AttributeSetRepositoryContract',
			'Cookbook\Eav\Repositories\EntityTypeRepository',
			'Cookbook\Contracts\Eav\EntityTypeRepositoryContract'
		];
	}


}