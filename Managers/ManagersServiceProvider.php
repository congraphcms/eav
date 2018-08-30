<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Managers;

use Illuminate\Support\ServiceProvider;

/**
 * ManagersServiceProvider service provider for managers
 * 
 * It will register all managers to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ManagersServiceProvider extends ServiceProvider {

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
		$this->registerManagers();
	}

	/**
	* Register the AttributeManager
	*
	* @return void
	*/
	public function registerManagers() {
		$this->app->singleton('Congraph\Eav\Managers\AttributeManager', function($app) {
			return new AttributeManager($this->app['config']);
		});
	}

	/**
     * Get the services provided by the provider.
     *
     * @return array
     */
	public function provides()
	{
		return ['Congraph\Eav\Managers\AttributeManager'];
	}
}