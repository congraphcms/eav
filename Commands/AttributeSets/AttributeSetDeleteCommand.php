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
 * AttributeSetDeleteCommand class
 * 
 * Command for deleting attribute set
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetDeleteCommand
{

	/**
	 * Attribute set ID
	 * 
	 * @var int
	 */
	public $id;


	/**
	 * Create new AttributeSetDeleteCommand
	 *
	 * @param int 	$id
	 * 
	 * @return void
	 */
	public function __construct($id)
	{
		$this->id = intval($id);
	}
}
