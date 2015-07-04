<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Commands\AttributeSets;

use Illuminate\Http\Request;

/**
 * AttributeSetFetchCommand class
 * 
 * Command for fetching attribute set by ID
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetFetchCommand
{

	/**
	 * Attribute set ID
	 * 
	 * @var int
	 */
	public $id;

	/**
	 * Fetch Attribute set request
	 * 
	 * @var Illuminate\Http\Request
	 */
	public $request;


	/**
	 * Create new AttributeSetFetchCommand
	 *
	 * @param int 						$id
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
