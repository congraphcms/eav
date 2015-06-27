<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Commands;

use Illuminate\Http\Request;

/**
 * CreateAttributeCommand class
 * 
 * Command for creating attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeCreateCommand
{

	/**
	 * Create Attribute Request
	 * 
	 * @var Illuminate\Http\Request
	 */
	public $request;


	/**
	 * Create new CreateAttributeCommand
	 * 
	 * @param Illuminate\Http\Request 	$request
	 * 
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}
}
