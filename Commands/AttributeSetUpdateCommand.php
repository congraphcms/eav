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
 * AttributeSetUpdateCommand class
 * 
 * Command for updating attribute set
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetUpdateCommand
{

	/**
	 * Attribute Set ID
	 * 
	 * @var int
	 */
	public $id;

	/**
	 * Attribute Set Update Request
	 * 
	 * @var Illuminate\Http\Request
	 */
	public $request;


	/**
	 * Create new AttributeSetUpdateCommand
	 * 
	 * @param Illuminate\Http\Request 	$request
	 * 
	 * @return void
	 */
	public function __construct($id, Request $request)
	{
		$this->id = intval($id);
		$this->request = $request;
	}
}
