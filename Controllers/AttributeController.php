<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Controllers;

use Cookbook\Core\Controllers\BaseManagementController;
use Illuminate\Http\Request;

/**
 * AttributeController class
 * 
 * RESTful Controller for attribute resource
 * 
 * @uses  		Cookbook\Core\Controller\BaseManagementController
 * @uses  		Illuminate\Http\Request
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeController extends BaseManagementController
{

	public function store(Request $request)
	{
		$result = $this->bus->dispatchFrom(
			'Cookbook\EAV\Commands\CreateAttributeCommand', 
			$request
		);

		return $this->response->json($result);
	}
}