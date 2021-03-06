<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields\Integer;

use Congraph\Eav\Fields\AbstractFieldValidator;
use Congraph\Eav\Managers\AttributeManager;

/**
 * IntegerFieldValidator class
 * 
 * Validating fields and values of type integer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class IntegerFieldValidator extends AbstractFieldValidator
{
	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations = ['e', 'ne', 'in', 'nin', 'gt', 'gte', 'lt', 'lte'];

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_integer';
}