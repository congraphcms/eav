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
	 * Check for specific rules and validation on attribute insert
	 * 
	 * Called after standard attribute validation with referenced attribute params
	 * depending on boolean value returned by this function attribute insert will continue or stop the execution
	 * 
	 * @param array $params
	 * 
	 * @return boolean
	 */
	public function validateAttributeForInsert(array &$params)
	{
		if( ! empty($params['data']) )
		{
			$this->exception->addErrors(array('data' => 'Select field attributes can\'t have data'));
			throw $this->exception;
			
		}

		return true;
	}

	/**
	 * Check for specific rules and validation on attribute update
	 * 
	 * Called after standard attribute validation with referenced attribute params
	 * depending on boolean value returned by this function attribute update will continue or stop the execution
	 * 
	 * @param array $params
	 * 
	 * @return boolean
	 */
	public function validateAttributeForUpdate(array &$params)
	{
		if( ! empty($params['data']) )
		{
			$this->exception->addErrors(array('data' => 'Select field attributes can\'t have data'));
			throw $this->exception;
		}

		return true;
	}

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