<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Boolean;

use Cookbook\Eav\Fields\AbstractFieldValidator;
use Cookbook\Eav\Managers\AttributeManager;

/**
 * BooleanFieldValidator class
 * 
 * Validating fields and values of type boolean
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class BooleanFieldValidator extends AbstractFieldValidator
{
	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations = ['e', 'ne'];

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_integer';
}