<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Select;

use Cookbook\Eav\Fields\AbstractFieldValidator;
use Cookbook\Core\Exceptions\ValidationException;

/**
 * SelectFieldValidator class
 * 
 * Validating fields and values of type select
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class SelectFieldValidator extends AbstractFieldValidator
{
	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations = ['e', 'ne', 'in', 'nin'];

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_integer';

	/**
	 * Validate attribute value
	 * 
	 * Validates if option exist
	 * 
	 * @param array $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function validateValue($value, $attribute, $entity_id = 0)
	{
		parent::validateValue($value, $attribute, $entity_id);

		if( is_null($value) )
		{
			return true;
		}
		
		// check if this attribute option exist
		foreach ($attribute->options as $option)
		{
			if($option->value == $value)
			{
				return true;
			}
		}
		
		throw new ValidationException(['Invalid option value.']);
		
	}

}