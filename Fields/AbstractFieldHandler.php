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

use Cookbook\Contracts\Eav\FieldHandlerContract;
use Cookbook\Core\Traits\ErrorManagerTrait;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;

/**
 * Abstract Field Handler class
 * 
 * Base class for all feild handlers
 * 
 * @uses  		Cookbook\Core\Traits\ErrorManagerTrait
 * @uses  		Cookbook\Eav\Managers\AttributeManager
 * @uses 		Illuminate\Database\Connection
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class AbstractFieldHandler implements FieldHandlerContract
{
	use ErrorManagerTrait;

	/**
	 * The database connection to use.
	 *
	 * @var Illuminate\Database\Connection
	 */
	protected $db;

	/**
	 * AttributeManager
	 * 
	 * @var AttributeManager
	 */
	public $attributeManager;

	/**
	 * Attribute value table name
	 * 
	 * @var string
	 */
	protected $table;


	/**
	 * Create new AbstractAttributeHandler
	 * 
	 * @param Illuminate\Database\Connection 			$db
	 * @param Cookbook\Eav\Managers\AttributeManager 	$attributeManager
	 * @param string 									$table
	 *  
	 * @return void
	 */
	public function __construct(Connection $db,
								AttributeManager $attributeManager, 
								$table)
	{
		// Inject dependencies
		$this->db = $db;
		$this->attributeManager = $attributeManager;
		$this->table = $table;

		// Init empty MessagBag object for errors
		$this->setErrors();
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
	abstract public function checkAttributeForInsert(array &$params);

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
	abstract public function checkAttributeForUpdate(array &$params);

	/**
	 * Make changes to attribute before handing it to application
	 * 
	 * @param stdClass $attribute
	 * 
	 * @return object
	 */
	abstract public function transformAttribute(\stdClass $attribute);


	/**
	 * Clean all related values and set entries for given attribute
	 * 
	 * Takes attribute that needs to be deleted,
	 * and deletes all related values and set entries
	 * 
	 * @param stdClass $attribute
	 * 
	 * @return boolean
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function sweepAfterAttribute(\stdClass $attribute)
	{
		// check if attribute has an ID
		if(empty($attribute->id))
		{
			throw new \InvalidArgumentException('Can\'t sweep after attribute that hasn\'t got ID.');
		}

		// delete all attribute values associated with provided attribute
		$this->db->table($this->table)->where('attribute_id', '=', $attribute->id)->delete();
		
		return true;
	}



	/**
	 * Clean all related values for given attribute option
	 * 
	 * Takes attribute option that needs to be deleted,
	 * and deletes all related values
	 * 
	 * @param stdClass $option
	 * 
	 * @return boolean
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function sweepAfterOption(\stdClass $option)
	{
		// check if option has an ID
		if( empty($option->id) )
		{
			throw new \InvalidArgumentException('Can\'t sweep after option that hasn\'t got ID.');
		}

		// check if option has attribute ID
		if( empty($option->attribute_id) )
		{
			throw new \InvalidArgumentException('Can\'t sweep after option that hasn\'t got attribute ID.');
		}

		// delete all attribute values that are associated with provided attribute option
		$this	->db->table($this->table)
				->where('attribute_id', '=', $option->attribute_id)
				->where('value', '=', $option->id)
				->delete();
		
		return true;
	}

	/**
	 * Clean all related values for given set attribute
	 * 
	 * Takes set attribute that needs to be deleted,
	 * and deletes all related values
	 * 
	 * @param stdClass $setAttribute
	 * 
	 * @return boolean
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function sweepAfterSetAttribute(\stdClass $setAttribute)
	{
		// check if setAttribute has attribute set ID
		if( empty($setAttribute->attribute_set_id) )
		{
			throw new \InvalidArgumentException('Can\'t sweep after set attribute that hasn\'t got attribute set ID.');
		}

		// check if setAttribute has attribute ID
		if( empty($setAttribute->attribute_id) )
		{
			throw new \InvalidArgumentException('Can\'t sweep after set attribute that hasn\'t got attribute ID.');
		}

		// delete attribute values that are associated with provided set attribute
		$this	->db->table( $this->table )
				->join( 'entities', $this->table . '.entity_id', '=', 'entities.id' )
				->where( 'entities.attribute_set_id', '=', $setAttribute->attribute_set_id )
				->where( $this->table . '.attribute_id', '=', $setAttribute->attribute_id )
				->delete();
		return true;
	}

	/**
	 * Clean all related values for given entity
	 * 
	 * Takes entity ID or array of IDs and attribute ID
	 * and deletes all related values for given attribute
	 * 
	 * @param integer | array 	$entityIDs
	 * @param integer 			$attributeID
	 * 
	 * @return boolean
	 */
	public function sweepAfterEntities($entityIDs, $attributeID)
	{
		// check if entity IDs are provided
		if( empty($entityIDs) )
		{
			throw new \InvalidArgumentException('You have to provide at least one entity ID for entity sweep.');
		}

		// make sure that attribute ID is an integer
		$attributeID = intval($attributeID);

		// check if attribute ID is provided
		if( empty($attributeID) )
		{
			throw new \InvalidArgumentException('You have to provide attribute ID for entity sweep.');
		}
		
		// check if entityIDs is an array and if not make it
		if(! is_array($entityIDs) )
		{
			$entityIDs = array( intval($entityIDs) );
		}

		// delete all attribute values associated with provided entities and attribute
		$success = $this	->db->table( $this->table )
							->whereIn( 'entity_id', $entityIDs )
							->where( 'attribute_id', '=', $attributeID )
							->delete();
		
		// return success of delete
		return !!$success;
	}

	/**
	 * Take attribute value and transform it for output (management API use)
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	abstract public function transformManagementValue($value, $attribute, $options);

	/**
	 * Take attribute value and transform it for frontend output
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	abstract public function transformValue($value, $attribute, $options);

	/**
	 * Take attribute values and bulk transform them for frontend output
	 * 
	 * @param $values
	 * @param $with
	 * 
	 * @return mixed
	 */
	abstract public function bulkTransformValues($values, $lang_id, $with);

	/**
	 * Provide default value for attribute
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	public function getDefaultValue($attribute, $options = [])
	{
		// check if attribute has a default value
		if(! is_null($attribute->default_value) )
		{
			return $attribute->default_value;
		}

		// check if there is a default option
		if(! empty($options) )
		{
			foreach ($options as $option)
			{
				if(! empty($option->default) && ! empty($option->id) )
				{
					return $option->id;
				}
			}
		}

		// return null if there is no default value
		return null;
	}

	/**
	 * Perform validation and preparation, and 
	 * update attribute value in database
	 * 
	 * Takes attribute value params and attribute definition
	 * 
	 * @param array $valueParams
	 * @param stdClass $attributeDefinition
	 * 
	 * @return boolean
	 */
	public function updateValue($valueParams, \stdClass $attributeDefinition)
	{
		// validate params for attribute value
		$this->validateAttributeValue($valueParams, $attributeDefinition);

		// if there was an error in params validation
		// stop and return false
		if($this->hasErrors())
		{
			return false;
		}

		// delete all values for provided entity, attribute and language
		$this	->db->table( $this->table )
				->where( 'attribute_id', '=', $valueParams['attribute_id'] )
				->where( 'entity_id', '=', $valueParams['entity_id'] )
				->where( 'language_id', '=', $valueParams['language_id'] )
				->delete();

		$success = true;

		// prepare value for database
		$valueParams = $this->prepareValue($valueParams, $attributeDefinition);

		// get settings for attribute from config
		$attributeSettings = $this->attributeManager->getFieldType($attributeDefinition->data_type);
		
		// if this field type has multiple values and 
		// value is array give each value a sort_order
		// and then insert them separately into database
		if( $attributeSettings['has_multiple_values'] && is_array($valueParams['value']) )
		{
			// sort_order counter
			$sort_order = 0;

			// go through values
			foreach ($valueParams['value'] as $value)
			{
				$singleValueParams = $valueParams;

				// override value with one item from array
				$singleValueParams['value'] = $value;

				// give it a sort_order
				$singleValueParams['sort_order'] = $sort_order++;

				// and insert it into database
				$success = $this->db->table($this->table)->insert($singleValueParams) && $success;
			}
		}
		// otherwise just insert value into database
		else
		{
			$success = $this->db->table($this->table)->insert($valueParams) && $success;
		}

		// check if there were any errors
		if(! $success || $this->hasErrors() )
		{
			return false;
		}

		// return success
		return true;
	}


	/**
	 * Prepare attribute value for database (serialize...)
	 * 
	 * This function should be overriden by specific attribute handler
	 * 
	 * @param array 	$valueParams
	 * @param stdClass	$attribute
	 * 
	 * @return array
	 */
	abstract public function prepareValue($valueParams, \stdClass $attribute);

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
	protected function validateAttributeValue($valueParams, \stdClass $attributeDefinition)
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