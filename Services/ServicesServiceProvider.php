<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Services;

use Illuminate\Support\ServiceProvider;

/**
 * ServicesServiceProvider service provider for services
 * 
 * It will register all services to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ServicesServiceProvider extends ServiceProvider {

	/**
	 * Boot
	 * @return void
	 */
	public function boot()
	{

	}


	/**
	 * Register
	 * 
	 * @return void
	 */
	public function register()
	{
		$this->registerServices();
		
	}

	/**
	 * Register the Attribute Repository
	 *
	 * @return void
	 */
	public function registerServices() {
		$this->app->singleton('Cookbook\Eav\Services\MetaDataService', function($app) {
			// var_dump('Contract for attribute repository resolving...');
			return new MetaDataService(
				$app->make('Cookbook\Contracts\Eav\AttributeRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\AttributeSetRepositoryContract'),
				$app->make('Cookbook\Contracts\Eav\EntityTypeRepositoryContract'),
				$app->make('Cookbook\Contracts\Workflows\WorkflowRepositoryContract'),
				$app->make('Cookbook\Contracts\Workflows\WorkflowPointRepositoryContract'),
				$app->make('Cookbook\Contracts\Locales\LocaleRepositoryContract')
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
			'Cookbook\Eav\Services\MetaDataService'
		];
	}


}