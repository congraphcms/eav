<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields\Compound;

use Congraph\Eav\Fields\AbstractFieldValidator;
use Congraph\Eav\Managers\AttributeManager;
use Congraph\Eav\Facades\MetaData;

/**
 * CompoundFieldValidator class
 * 
 * Validating fields and values of text type
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CompoundFieldValidator extends AbstractFieldValidator
{
	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations = ['e', 'ne', 'in', 'nin', 'm'];

	/**
	 * Every compound is calculated by series of inputs
	 * this is a list of available inputs that can be handled
	 *
	 * @var array
	 */
	protected $availableInputs = ['field', 'literal', 'operator'];

	/**
	 * Compund calculation can't handle all types of fields
	 * this is a list of field types that can be handled
	 *
	 * @var array
	 */
	protected $availableFieldTypes = ['text'];

	/**
	 * Compund calculation merges values with operations
	 * this is a list of operations that can be handled
	 *
	 * @var array
	 */
	protected $availableOperators = ['CONCAT'];

	/**
	 * Compund calculation merges values with operations
	 * this is a list of operations that can be handled
	 *
	 * @var array
	 */
	protected $availableExpectedValues = ['string'];

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_text';

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
		$attributeSettings = $this->attributeManager->getFieldType($params['field_type']);

		if( ! $attributeSettings['can_be_unique'] && ! empty($params['unique']) )
		{
			$this->exception->addErrors(['unique' => 'This attribute type can\'t be unique.']);
		}

		if( ! $attributeSettings['has_options'] && ! empty($params['options']) )
		{
			$this->exception->addErrors(['options' => 'This attribute type can\'t have options.']);
		}

		if(!isset($params['data']) || !isset($params['data']['expected_value']))
		{
			$this->exception->addErrors(['data.expected_value' => 'You need to specify an expected value type.']);
		} 

		if(!isset($params['data']) || !isset($params['data']['inputs']))
		{
			$this->exception->addErrors(['data.inputs' => 'You need to specify inputs for compound field.']);
		}

		if($this->exception->hasErrors())
		{
			throw $this->exception;
		}
		
		$this->validateExpectedValue($params['data']['expected_value']);

		$this->validateCalculation($params['data']['inputs']);

		if($this->exception->hasErrors())
		{
			throw $this->exception;
		}

		// set localization of this attribute
		foreach ($params['data']['inputs'] as $input)
		{
			if($input['type'] !== 'field')
			{
				continue;
			}
			
			$attribute = MetaData::getAttributeById($input['value']);
			if($attribute->localized)
			{
				$params['localized'] = 1;
			}
		}
	}

	protected function validateCalculation(&$inputs)
	{
		if(!is_array($inputs)){
			$this->exception->addErrors(['data.inputs' => 'Inputs data needs to be an array.']);
			return;
		}

		$inputs = array_values($inputs);

		if(empty($inputs))
		{
			$this->exception->addErrors(['data.inputs' => 'You need to have at least one input.']);
			return;
		}

		
		$invalidInputs = false;
		
		foreach ($inputs as $input)
		{
			if(!$this->validateInput($input))
			{
				$invalidInputs = true;
				continue;
			}
		}

		if($invalidInputs)
		{
			return;
		}

		if($inputs[0]['type'] == 'operator' || $inputs[count($inputs) - 1]['type'] == 'operator')
		{
			$this->exception->addErrors(['data.inputs' => 'Inputs can\'t start or finish with operator.']);
			return;
		}

		$odd = false;
		foreach ($inputs as $input)
		{
			if((!$odd && $input['type'] == 'operator') || ($odd && $input['type'] != 'operator'))
			{
				$this->exception->addErrors(['data.inputs' => 'Can\'t have two value inputs or two operators together.']);
				break;
			}

			$odd = !$odd;
		}

		
	}

	protected function validateExpectedValue($expectedValue)
	{
		if(!in_array(strval($expectedValue), $this->availableExpectedValues))
		{
			$this->exception->addErrors(['data.expected_value' => 'Invalid expected_value type: \''.strval($expectedValue).'\'.']);
		}
	}

	protected function validateInput($input)
	{
		if(!is_array($input)){
			$this->exception->addErrors(['data.inputs' => 'Every inputs needs to be defined as an array.']);
			return false;
		}

		if(empty($input['type']))
		{
			$this->exception->addErrors(['data.inputs' => 'Input needs to have a type.']);
			return false;
		}
		else
		{
			if(!in_array(strval($input['type']), $this->availableInputs))
			{
				$this->exception->addErrors(['data.inputs.type' => 'Invalid input type: \''.strval($input['type']).'\'.']);

				return false;
			}
		}
		

		if(empty($input['value']))
		{
			$this->exception->addErrors(['data.inputs' => 'Input needs to have a value.']);
			return false;
		}
		else
		{
			switch ($input['type'])
			{
				case 'field':
					$attribute = MetaData::getAttributeById($input['value']);
					if(!$attribute)
					{
						$this->exception->addErrors(['data.inputs.value' => 'Invalid input value (unknown field id).']);
						return false;
					}
					break;
				case 'operator':
					if(!in_array($input['value'], $this->availableOperators))
					{
						$this->exception->addErrors(['data.inputs.value' => 'Invalid input value (unknown operator).']);
						return false;
					}
					break;
				case 'literal':
				default:
					break;
			}
		}
		return true;
	}

	/**
	 * Check for specific rules and validation on attribute update
	 * 
	 * Called after standard attribute validation with referenced attribute params
	 * depending on boolean value returned by this function 
	 * attribute update will continue or stop the execution
	 * 
	 * @param array $params
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function validateAttributeForUpdate(array &$params, $attribute)
	{
		unset($params['data']);

		$attributeSettings = $this->attributeManager->getFieldType($attribute->field_type);

		if( ! $attributeSettings['can_be_unique'] && ! empty($params['unique']) )
		{
			$this->exception->addErrors(['unique' => 'This attribute type can\'t be unique.']);
		}
		if( ! $attributeSettings['can_be_localized'] && ! empty($params['localized']) )
		{
			$this->exception->addErrors(['localized' => 'This attribute type can\'t be localized.']);
		}

		if( ! $attributeSettings['has_options'] && ! empty($params['options']) )
		{
			$this->exception->addErrors(['options' => 'This attribute type can\'t have options.']);
		}

		if($this->exception->hasErrors())
		{
			throw $this->exception;
		}
	}

	/**
	 * Validate attribute value
	 * 
	 * This function can be extended by specific attribute handler
	 * 
	 * @param array $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function validateValue($value, $attribute, $entity_id = 0)
	{
	}


}