<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Repositories;

use Illuminate\Database\Connection;

use Cookbook\Core\Repository\AbstractRepository;
use Cookbook\Core\Traits\ValidatorTrait;

use Cookbook\Contracts\Eav\AttributeHandlerFactoryContract;
use Cookbook\Eav\Managers\AttributeManager;


/**
 * AttributeValueRepository class
 * 
 * Repository for attribute database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 * @uses   		Cookbook\Contracts\Eav\AttributeHandlerFactoryContract
 * @uses   		Cookbook\Eav\Managers\AttributeManager
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeValueRepository extends AbstractRepository
{
	use ValidatorTrait;

	/**
	 * Factory for attribute handlers,
	 * makes appropriate attriubte handler depending on attribute data type
	 * 
	 * @var AttributeHandlerFactoryInterface
	 */
	protected $attributeHandlerFactory;

	/**
	 * Helper for attributes
	 * 
	 * @var Vizioart/Attributes/Manager/AttributeManager
	 */
	protected $attributeManager;

	/**
	 * Array of available data_types for attribute
	 *
	 * @var array
	 */
	protected $availableFieldTypes;

	/**
	 * Array used to create rules for validation for INSERT attribute value
	 * 
	 * @var array
	 */
	protected $attributeValueInsertParamRules;


	/**
	 * Create new AttributeValueRepository
	 * 
	 * @param Illuminate\Database\Connection $db
	 * @param Cookbook\Eav\Handlers\AttributeHandlerFactoryContract $attributeHandlerFactory
	 * @param Cookbook\Eav\Managers\AttributeManager $attributeManager
	 * 
	 * @return void
	 */
	public function __construct(Connection $db,
								AttributeHandlerFactoryContract $attributeHandlerFactory,
								AttributeManager $attributeManager)
	{

		// AbstractRepository constructor
		parent::__construct($db);
		
		// Inject dependencies
		$this->attributeHandlerFactory = $attributeHandlerFactory;
		$this->attributeManager = $attributeManager;

		// get available field types
		$this->availableFieldTypes = $this->attributeManager->getFieldTypes();

		// get all value tables
		$this->valueTables = $this->attributeManager->getValueTables();

		// set default key for errors
		$this->setErrorKey('attribute_value.errors');

		// Validation Rules for attribute value insert
		$this->attributeValueInsertParamRules = array(
			'attribute_id'		=> 'required|exists:attributes,id',
			'entity_id'			=> 'required|exists:entities,id',
			'entity_type_id'	=> 'required|exists:entity_types,id',
			'language_id'		=> 'integer',
			'value'				=> '',
		);

	}

	/**
	 * Create new or update existing attribute value through proxy
	 * 
	 * @param array $model - attribute value params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	public function createOrUpdate($model)
	{
		// arguments for private method 
		$args = func_get_args();

		// proxy call
		$result = $this->proxy('_createOrUpdate', $args);

		return $result;
	}


	/**
	 * Create new or update existing attribute value
	 * 
	 * @param array $model - attribute value params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 * 
	 * @todo  fetch attribute definition through AttributeRepository
	 */
	protected function _createOrUpdate($model)
	{

		$this->validateParams($model, $this->attributeValueInsertParamRules);

		if( $this->hasErrors() )
		{
			return false;
		}


		// fetch this through AttributeRepository
		$attrDefinition = $this->db ->table('attributes')
									->where('code', '=', $attribute_code)
									->first();

		if( ! $attrDefinition )
		{
			$this->addErrors('Invalid attribute.');
			return false;
		}

		$attributeHandler = $this	->attributeHandlerFactory
									->make($attrDefinition->data_type);	
		
		$attrDefinition = $attributeHandler->transformAttribute($attrDefinition);
		$success = $attributeHandler->updateValue($attribute, $attrDefinition);
		
		if( ! $success )
		{
			
			$this->addErrors($attributeHandler->getErrors());

			return false;
		}

		return true;
	}

	/**
	 * Mandatory abstract function implementation
	 */
	protected function _create($model)
	{
		throw new \Exception('Not implemented. Use "createOrUpdate" method instead.');
	}

	/**
	 * Mandatory abstract function implementation
	 */
	protected function _update($model)
	{
		throw new \Exception('Not implemented. Use "createOrUpdate" method instead.');
	}

	/**
	 * Mandatory abstract function implementation
	 */
	protected function _delete($id)
	{
		throw new \Exception('Not implemented. Use "deleteByEntity" method instead.');
	}

	/**
	 * Delete all values for entity
	 * 
	 * @param integer | array $ids - ID of entity that will be deleted
	 * 
	 * @return boolean
	 */
	public function deleteByEntity($entityIDs)
	{
		if( ! is_array($entityIDs) )
		{
			$entityIDs = [$entityIDs];
		}
		// array of value queries
		$unionQueries = [];

		// create a query builder for each value table
		foreach ($this->valueTables as $valueTable)
		{
			$query = $this->db->table($valueTable)->whereIn('entity_id', $entityIDs);
			$unionQueries[] = $query;
		}

		// no value tables - no data to be deleted
		if( empty($unionQueries) )
		{
			return true;
		}

		// make a single query from unions of all values tables
		$query = null;
		for($i = 1; $i < count($unionQueries); $i++)
		{
			if($i == 1)
			{
				$query = $unionQueries[$i - 1]->union( $unionQueries[$i] );
			}
			else
			{
				$query = $query->union( $unionQueries[$i] );
			}
		}

		// get results from sql
		$values = $query->get();

		// if no values - no data to be deleted
		if( empty($values) )
		{
			return true;
		}

		// get all attributeIDs
		$attributeIDs = [];
		foreach($values as $value)
		{
			if( ! in_array($value->attribute_id, $attributeIDs) )
			{
				$attributeIDs[] = $value->attribute_id;
			}
		}

		// get attributes
		$attributes = $this->db ->table('attributes')
								->whereIn('id', $attributeIDs)
								->get();
		// call handler
		foreach ($values as $value)
		{
			// find appropriate attribute for value
			$attribute = null;
			foreach ($attributes as $attr)
			{
				if($attr->id == $value->attribute_id)
				{
					$attribute = $attr;
					break;
				}
			}

			if($attribute == null)
			{
				continue;
			}

			$attributeHandler = $this->attributeHandlerFactory->make($attribute->data_type);

			$attributeHandler->onValueDelete($value, $attribute);
		}

		foreach ($this->valueTables as $valueTable)
		{
			$this->db 	->table($valueTable)
						->whereIn('entity_id', $entityIDs)
						->delete();
		}

		return true;
	}
}