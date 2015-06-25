<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Repositories;

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
		$this->app->bind('Cookbook\EAV\Repositories\AttributeRepository', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new AttributeRepository(
				$app['db']->connection(),
				$app->make('Cookbook\Contracts\EAV\FieldHandlerFactoryContract'),
				$app->make('Cookbook\EAV\Managers\AttributeManager')
			);
		});

		$this->app->alias(
			'Cookbook\EAV\Repositories\AttributeRepository', 'Cookbook\Contracts\EAV\AttributeRepositoryContract'
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
			'Cookbook\EAV\Repositories\AttributeRepository',
			'Cookbook\Contracts\EAV\AttributeRepositoryContract'
		];
	}


}