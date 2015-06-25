<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Fields;

use Cookbook\Contracts\EAV\FieldValidatorContract;
use Cookbook\EAV\Managers\AttributeManager;
use Cookbook\Core\Exceptions\ValidationException;

/**
 * Abstract Field Validator class
 * 
 * Base class for all feild validators
 * 
 * @uses  		Cookbook\Contracts\EAV\FieldValidatorContract
 * @uses  		Cookbook\EAV\Managers\AttributeManager
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class AbstractFieldValidator implements FieldValidatorContract
{

	/**
	 * AttributeManager
	 * 
	 * @var AttributeManager
	 */
	public $attributeManager;

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

	/**
	 * Create new AbstractAttributeValidator
	 * 
	 * @param Illuminate\Database\Connection 			$db
	 * @param Cookbook\EAV\Managers\AttributeManager 	$attributeManager
	 * @param string 									$table
	 *  
	 * @return void
	 */
	public function __construct(AttributeManager $attributeManager)
	{
		// Inject dependencies
		$this->attributeManager = $attributeManager;

		$this->exception = new ValidationException();
	}

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
	abstract public function validateAttributeForInsert(array &$params);

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
	abstract public function validateAttributeForUpdate(array &$params);

	/**
	 * Validate attribute value
	 * 
	 * This function can be extended by specific attribute handler
	 * 
	 * @param array $valueParams
	 * @param Eloqunt $attributeDefinition
	 * 
	 * @return boolean
	 */
	public function validateField($valueParams, \stdClass $attributeDefinition)
	{

		// check if this attribute is required
		if($attributeDefinition->required)
		{
			// if it's required and it's empty add an error
			if(empty($valueParams['value']))
			{
				$this->addErrors('This is a required field.');
			}
		}

		// check if attribute needs to be unique
		if($attributeDefinition->unique)
		{
			// check if this value is unique
			$unique = $this ->uniqueValue($valueParams, $attributeDefinition);
			
			// if it's not unique add an error
			if(!$unique)
			{
				$this->addErrors('This needs to be a unique value.');
			}
		}

		// check if attribute is localized
		if($attributeDefinition->localized)
		{
			// if it doesn't have defined language_id add an error
			if(empty($valueParams['language_id']))
			{
				$this->addErrors('You need to specify a language.');
			}
		}

		// if there are any errors
		// return false
		if($this->hasErrors())
		{
			return false;
		}

		// return success
		return true;
	}


	/**
	 * check if attribute value is unique
	 * 
	 * @param array $valueParams
	 * @param Eloqunt $attributeDefinition
	 * 
	 * @return boolean
	 */
	protected function uniqueValue($valueParams, \stdClass $attributeDefinition)
	{
		// check if value is empty and if it is return true
		// because unique is not checked on empty values 
		if(empty($valueParams['value']))
		{
			return true;
		}

		// check database for same values
		$query = $this 	->db->table( $this->table )
						->where( 'value', '=', $valueParams['value'] )
						->where( 'attribute_id', '=', $attributeDefinition->id );

		// if enitity_id is defined exclude it from query
		if($valueParams['entity_id'])
		{
			$query = $query->where( 'entity_id', '!=', $valueParams['entity_id'] );
		}
		
		
		// get values
		$value = $query->first();

		// if there is such a value return false (not unique)
		if($value)
		{
			return false;
		}

		// return succes (is unique)
		return true;
	}


}