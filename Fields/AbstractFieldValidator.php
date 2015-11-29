<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields;

use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\FieldValidatorContract;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;

/**
 * Abstract Field Validator class
 * 
 * Base class for all feild validators
 * 
 * @uses  		Cookbook\Contracts\Eav\FieldValidatorContract
 * @uses  		Cookbook\Eav\Managers\AttributeManager
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
	 * Repository for attributes
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	public $attributeRepository;

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations;

	/**
	 * The database connection to use.
	 *
	 * @var Illuminate\Database\Connection
	 */
	protected $db;

	/**
	 * Attribute value table name
	 * 
	 * @var string
	 */
	protected $table;
	

	/**
	 * Create new AbstractAttributeValidator
	 * 
	 * @param Illuminate\Database\Connection 			$db
	 * @param Cookbook\Eav\Managers\AttributeManager 	$attributeManager
	 * @param string 									$table
	 *  
	 * @return void
	 */
	public function __construct(Connection $db, AttributeManager $attributeManager, AttributeRepositoryContract $attributeRepository)
	{
		// Inject dependencies
		$this->db = $db;
		$this->attributeManager = $attributeManager;
		$this->attributeRepository = $attributeRepository;

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
	public function validateAttributeForInsert(array &$params)
	{
		$attributeSettings = $this->attributeManager->getFieldType($params['field_type']);
		if( ! $attributeSettings['can_have_default_value'] && isset($params['default_value']) && ! is_null($params['default_value']) )
		{
			$this->exception->addErrors(['default_value' => 'This attribute type can\'t have default value.']);
		}
		if( ! $attributeSettings['can_be_unique'] && ! empty($params['unique']) )
		{
			$this->exception->addErrors(['unique' => 'This attribute type can\'t be unique.']);
		}
		if( ! $attributeSettings['can_be_localized'] && ! empty($params['localized']) )
		{
			$this->exception->addErrors(['localized' => 'This attribute type can\'t be localized.']);
		}

		if($this->exception->hasErrors())
		{
			throw $this->exception;
		}
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
		$attributeSettings = $this->attributeManager->getFieldType($attribute->field_type);
		if( ! $attributeSettings['can_have_default_value'] && isset($params['default_value']) && ! is_null($params['default_value']) )
		{
			$this->exception->addErrors(['default_value' => 'This attribute type can\'t have default value.']);
		}
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

		// check if this attribute is required
		if($attribute->required)
		{
			// if it's required and it's empty add an error
			if(empty($value))
			{
				throw new ValidationException(['This field is required.']);
			}
		}

		// check if attribute needs to be unique
		if($attribute->unique)
		{
			// check if this value is unique
			$unique = $this ->uniqueValue($value, $attribute, $entity_id);
			
			// if it's not unique add an error
			if(!$unique)
			{
				throw new ValidationException(['This field needs to have a unique value.']);
			}
		}
	}


	/**
	 * Validate attribute filter
	 * 
	 * @param $filter
	 * @param object $attribute
	 * 
	 * @todo  it should be a valid value nor just operator, but not sure how to check that
	 * 
	 * @return boolean
	 */
	public function validateFilter(&$filter, $attribute)
	{

		if( ! is_array($filter) )
		{
			if( ! in_array('e', $this->availableFilterOperations) )
			{
				$e = new BadRequestException();
				$e->setErrorKey('entities.filter.fields.' . $attribute->code);
				$e->addErrors('Filter operation is not allowed.');

				throw $e;
			}

			return;
		}

		foreach ($filter as $operation => &$value) {
			if( ! in_array($operation, $this->availableFilterOperations) )
			{
				$e = new BadRequestException();
				$e->setErrorKey('entities.filter.fields.' . $attribute->code);
				$e->addErrors('Filter operation is not allowed.');

				throw $e;
			}

			if($operation == 'in' || $operation == 'nin')
			{
				if( ! is_array($value) )
				{
					$value = explode(',', strval($value));
				}
			}
		}
	}

	/**
	 * check if attribute value is unique
	 * 
	 * @param array $value
	 * @param object $attribute
	 * 
	 * @todo  parse value before checking if it's unique
	 * 
	 * @return boolean
	 */
	protected function uniqueValue($value, $attribute, $entity_id)
	{
		// check if value is empty and if it is return true
		// because unique is not checked on empty values 
		if(empty($value))
		{
			return true;
		}

		// check database for same values
		$query = $this 	->db->table( $this->table )
						->where( 'value', '=', $value )
						->where( 'attribute_id', '=', $attribute->id );

		// if enitity_id is defined exclude it from query
		if($entity_id)
		{
			$query = $query->where( 'entity_id', '!=', $entity_id );
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