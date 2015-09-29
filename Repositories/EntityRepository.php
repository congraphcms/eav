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

use stdClass;
use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Contracts\Eav\FieldHandlerFactoryContract;
use Cookbook\Core\Exceptions\Exception;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Repositories\AbstractRepository;
use Cookbook\Core\Repositories\UsesCache;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;


/**
 * EntityRepository class
 * 
 * Repository for entity database queries
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
class EntityRepository extends AbstractRepository implements EntityRepositoryContract//, UsesCache
{
	/**
	 * Factory for field handlers,
	 * makes appropriate field handler depending on attribute data type
	 * 
	 * @var Cookbook\Contracts\Eav\FieldHandlerFactoryContract
	 */
	protected $fieldHandlerFactory;


	/**
	 * Helper for attributes
	 * 
	 * @var Vizioart/Attributes/Manager/AttributeManager
	 */
	protected $attributeManager;
	
	/**
	 * Repository for handling attribute sets
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for handling attributes
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;



	/**
	 * Create new EntityRepository
	 * 
	 * @param Illuminate\Database\Connection $db
	 * @param Cookbook\Eav\Handlers\AttributeHandlerFactoryContract $attributeHandlerFactory
	 * @param Cookbook\Eav\Managers\AttributeManager $attributeManager
	 * 
	 * @return void
	 */
	public function __construct(Connection $db,
								FieldHandlerFactoryContract $fieldHandlerFactory,
								AttributeManager $attributeManager, 
								AttributeSetRepositoryContract $attributeSetRepository, 
								AttributeRepositoryContract $attributeRepository)
	{

		// AbstractRepository constructor
		parent::__construct($db);

		// Inject dependencies
		$this->fieldHandlerFactory = $fieldHandlerFactory;
		$this->attributeManager = $attributeManager;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->attributeRepository = $attributeRepository;

		// Validation Rules for entity insert
		$this->entityInsertParamRules = array(
			'object_id'				=> 'required|integer',
			'entity_type_id'		=> 'required|exists:entity_types,id',
			'attribute_set_id'		=> 'required|exists:attribute_sets,id',
		);

		
		
	}


	/**
	 * Create new entity
	 * 
	 * @param array $model - entity params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	protected function _create($model)
	{
		
		$fields = array();
		if( ! empty( $model['fields'] ) && is_array( $model['fields'] ) )
		{
			$fields = $model['fields'];
		}

		$locale_id = $model['locale_id'];

		$fieldsForInsert = [];
		$attributes = [];

		if( ! empty($fields) )
		{
			$attributes = $this->attributeRepository->get(['code' => ['in' => array_keys($fields)]]);
		}

		unset($model['fields']);
		unset($model['locale_id']);

		// insert entity
		$entityID = $this->insertEntity($model);

		foreach ($attributes as $attribute)
		{
			$fieldForInsert = [];
			$fieldForInsert['entity_id'] = $entityID;
			$fieldForInsert['entity_type_id'] = $model['entity_type_id'];
			$fieldForInsert['attribute_set_id'] = $model['attribute_set_id'];
			$fieldForInsert['attribute_id'] = $attribute->id;
			$fieldForInsert['locale_id'] = ($attribute->localized)?$locale_id:0;
			$fieldForInsert['value'] = (isset($fields[$attribute->code]))?$fields[$attribute->code]:$attribute->default_value;

			$fieldsForInsert[] = $fieldForInsert;
		}

		$this->insertFields($fieldsForInsert, $attributes);

		$entity = $this->fetch($entityID);

		return $entity;
		
	}


	/**
	 * Update entity and its fields
	 *
	 * @param int $id - entity ID
	 * @param array $model - entity params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 * 
	 * @todo enable attribute set change for entity
	 */
	protected function _update($id, $model)
	{

		$entity = $this->fetch($id);

		$fields = array();
		
		if( ! empty( $model['fields'] ) && is_array( $model['fields'] ) )
		{
			$fields = $model['fields'];
		}

		$fieldsForUpdate = [];
		$attributes = [];

		if( ! empty($fields) )
		{
			$attributes = $this->attributeRepository->get(['code' => ['in' => array_keys($fields)]]);
		}

		foreach ($attributes as $attribute)
		{
			$fieldForUpdate = [];
			$fieldForUpdate['entity_id'] = $id;
			$fieldForUpdate['entity_type_id'] = $model['entity_type_id'];
			$fieldForUpdate['attribute_id'] = $attribute->id;
			$fieldForUpdate['locale_id'] = ($attribute->localized)?$model['locale_id']:0;
			$fieldForUpdate['value'] = $fields[$attribute->code];

			$fieldsForUpdate[] = $fieldForUpdate;
		}

		

		$this->updateFields($fieldsForUpdate, $attributes);

		$this->updateEntity($id);

		$entity = $this->fetch($id);

		return $entity;
		
	}

	/**
	 * Delete entity and its attributes
	 * 
	 * @param integer | array $ids - ID of entity that will be deleted
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException, Exception
	 */
	protected function _delete($id)
	{
		// get the entity
		$entity = $this->fetch($id);

		$this->deleteFields($entity);

		$this->db->table('entities')->where('id', '=', $id)->delete();

		return $entity;
	}

	/**
	 * Delete all entities for attribute set
	 * 
	 * @param object $attributeSet
	 * 
	 * @return void
	 */
	public function deleteByAttribute($attribute)
	{
		$this->deleteFieldsByAttribute($attribute);
	}

	/**
	 * Delete all entities for attribute set
	 * 
	 * @param object $attributeSet
	 * 
	 * @return void
	 */
	public function deleteByAttributeSet($attributeSet)
	{
		$this->deleteFieldsByAttributeSet($attributeSet);

		$entities = $this->db 	->table('entities')
								->where('attribute_set_id', '=', $attributeSet->id)
								->delete();
	}

	/**
	 * Delete all entities for entity type
	 * 
	 * @param object $entityType
	 * 
	 * @return void
	 */
	public function deleteByEntityType($entityType)
	{
		$this->deleteFieldsByEntityType($entityType);

		$entities = $this->db 	->table('entities')
								->where('entity_type_id', '=', $entityType->id)
								->delete();
	}


	/**
	 * insert entity in database
	 * 
	 * @param array $params = entity params
	 * 
	 * @return boolean
	 */
	protected function insertEntity($params)
	{

		$params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');

		// insert entity in database
		$entityId = $this->db->table('entities')->insertGetId($params);

		if( ! $entityId )
		{
			throw new \Exception('Failed to insert entity.');
		}

		return $entityId;

		
	}

	/**
	 * update entity updated_at in database
	 * 
	 * @param int $id
	 */
	protected function updateEntity($id)
	{

		$params['updated_at'] = date('Y-m-d H:i:s');

		$this->db->table('entities')->where('id', '=', $id)->update($params);
	}

	/**
	 * Insert attribute values in entity
	 * 
	 * @param array $fields - field values
	 * @param array $attributes - attribute definitions
	 * 
	 * @return void
	 */
	protected function insertFields(array $fields, array $attributes)
	{
		for ($i = 0; $i < count($attributes); $i++)
		{
			$fieldHandler = $this->fieldHandlerFactory->make($attributes[$i]->field_type);
			$fieldHandler->insert($fields[$i], $attributes[$i]);
		}
	}

	/**
	 * Update attribute values in entity
	 * 
	 * @param array $fields - field values
	 * @param array $attributes - attribute definitions
	 * 
	 * @return boolean
	 */
	protected function updateFields(array $fields, array $attributes)
	{
		for ($i = 0; $i < count($attributes); $i++)
		{
			$fieldHandler = $this->fieldHandlerFactory->make($attributes[$i]->field_type);
			$fieldHandler->update($fields[$i], $attributes[$i]);
		}
	}

	/**
	 * Delete attribute values for entity
	 * 
	 * @param stdClass $entity
	 * 
	 * @return void
	 */
	protected function deleteFields($entity)
	{
		$attributes = [];
		if( ! empty($entity->fields) )
		{
			$attributes = $this->attributeRepository->get(['code' => ['in' => array_keys(get_object_vars($entity->fields))]]);
		}
		foreach ($attributes as $attribute)
		{
			$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
			$fieldHandler->deleteByEntity($entity, $attribute);
		}
		
	}

	/**
	 * Delete attribute values for attribute 
	 * 
	 * @param stdClass $attribute
	 * 
	 * @return void
	 */
	protected function deleteFieldsByAttribute($attribute)
	{
		$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
		$fieldHandler->deleteByAttribute($attribute);
	}

	/**
	 * Delete attribute values for attribute set
	 * 
	 * @param stdClass $attributeSet
	 * 
	 * @return void
	 */
	protected function deleteFieldsByAttributeSet($attributeSet)
	{
		$attributeSettings = $this->attributeManager->getFieldTypes();

		foreach ($attributeSettings as $fieldType => $settings)
		{
			$fieldHandler = $this->fieldHandlerFactory->make($fieldType);
			$fieldHandler->deleteByAttributeSet($attributeSet);
		}
		
	}

	/**
	 * Delete attribute values for attribute set
	 * 
	 * @param stdClass $entityType
	 * 
	 * @return void
	 */
	protected function deleteFieldsByEntityType($entityType)
	{
		$attributeSettings = $this->attributeManager->getFieldTypes();

		foreach ($attributeSettings as $fieldType => $settings)
		{
			$fieldHandler = $this->fieldHandlerFactory->make($fieldType);
			$fieldHandler->deleteByEntityType($entityType);
		}
		
	}


	


	// ----------------------------------------------------------------------------------------------
	// GETTERS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Get entity by ID
	 * 
	 * @param int $id - ID of attribute to be fetched
	 * 
	 * @return array
	 */
	protected function _fetch($id, $locale = null)
	{
		$entity = $this->db->table('entities')
						->select(
							'entities.id',  
							'entities.entity_type_id', 
							'entities.attribute_set_id',
							'entity_types.code as type',
							'entities.created_at as created_at',
							'entities.updated_at as updated_at'
						)
						->where('entities.id', '=', $id)
						->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id')
						->first();
		
		if( ! $entity )
		{
			throw new NotFoundException(['There is no entity with that ID.']);
		}

		$fields = $this->getFieldsForEntities($id, $locale);

		$entity->fields = $fields[$id];

		return $entity;
	}

	/**
	 * Get attributes
	 * 
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $locale = 0)
	{
		$query =  $this->db->table('entities')
						->select(
							'entities.id as id',  
							'entities.entity_type_id as entity_type_id', 
							'entities.attribute_set_id as attribute_set_id',
							'entity_types.code as type',
							'entities.created_at as created_at',
							'entities.updated_at as updated_at'
						)
						->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id');

		$query = $this->parseFilters($query, $filter);

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);

		$query->groupBy('entities.id');
		
		$entities = $query->get();

		if( ! $entities )
		{
			return [];	
		}

		$ids = [];

		foreach ($entities as &$entity) 
		{
			$ids[] = $entity->id;
		}

		$fields = $this->getFieldsForEntities($ids, $locale);

		foreach ($entities as &$entity) 
		{
			$entity->fields = $fields[$entity->id];
		}
		
		
		return $entities;
	}

	protected function parseFilters($query, $filters)
	{
		$fieldFilters = [];
		foreach ($filters as $key => $filter)
		{
			if(substr( $key, 0, 7 ) === "fields.")
			{
				$code = substr($key, 7);
				$fieldFilters[$code] = $filter;
				continue;
			}

			if( ! is_array($filter) )
			{
				$query = $query->where('entities.' . $key, '=', $filter);
				continue;
			}

			$query = $this->parseFilterOperator($query, 'entities.' . $key, $filter);
		}
		if( ! empty($fieldFilters) )
		{
			$attributes = $this->attributeRepository->get(['code' => ['in'=>array_keys($fieldFilters)]]);

			foreach ($attributes as $attribute)
			{
				$query = $this->parseFieldFilter($query, $attribute, $fieldFilters[$attribute->code]);
			}
		}
		

		

		return $query;
	}

	protected function parseFilterOperator($query, $key, $filter)
	{
		foreach ($filter as $operator => $value) {
			switch ($operator) 
			{
				case 'e':
					$query = $query->where($key, '=', $value);
					break;
				case 'ne':
					$query = $query->where($key, '!=', $value);
					break;
				case 'lt':
					$query = $query->where($key, '<', $value);
					break;
				case 'lte':
					$query = $query->where($key, '<=', $value);
					break;
				case 'gt':
					$query = $query->where($key, '>', $value);
					break;
				case 'gte':
					$query = $query->where($key, '>=', $value);
					break;
				case 'in':
					$query = $query->whereIn($key, $value);
					break;
				case 'nin':
					$query = $query->whereNotIn($key, $value);
					break;
				
				default:
					throw new BadRequestException(['Filter operator not supported.']);
					break;
			}
		}

		return $query;
	}

	protected function parseFieldFilter($query, $attribute, $filter)
	{
		$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
		$query = $fieldHandler->filterEntities($query, $attribute, $filter);

		return $query;
	}

	protected function parseSorting($query, $sort)
	{
		if( ! empty($sort) )
		{
			
			$sort = (is_array($sort))? $sort: [$sort];
			$fieldSorting = [];
			foreach ($sort as $sortCriteria)
			{
				if($sortCriteria[0] === '-')
				{
					$sortCriteria = substr($sortCriteria, 1);
				}

				if(substr( $sortCriteria, 0, 7 ) === "fields.")
				{
					$code = substr($sortCriteria, 7);
					$fieldSorting[] = $code;
				}
			}
			$attributes = [];
			if( ! empty($fieldSorting) )
			{
				$attributes = $this->attributeRepository->get(['code' => ['in'=>$fieldSorting]]);
			}


			foreach ($sort as $sortCriteria)
			{

				$sortDirection = 'asc';

				if($sortCriteria[0] === '-')
				{
					$sortCriteria = substr($sortCriteria, 1);
					$sortDirection = 'desc';
				}

				if(substr( $sortCriteria, 0, 7 ) === "fields.")
				{
					$code = substr($sortCriteria, 7);
					foreach ($attributes as $attribute)
					{
						if($attribute->code == $code)
						{
							$query = $this->parseFieldSorting($query, $attribute, $sortDirection);
						}
					}
					continue;
				}

				$query = $query->orderBy('entities.' . $sortCriteria, $sortDirection);
			}
		}

		return $query;
	}

	protected function parseFieldSorting($query, $attribute, $direction)
	{
		$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
		$query = $fieldHandler->sortEntities($query, $attribute, $direction);

		return $query;
	}

	/**
	 * Get entity fields by entity IDs
	 * 
	 * @param $entityIds
	 */
	protected function getFieldsForEntities($entityIds, $locale = null){
		
		if(!is_array($entityIds)){
			$entityIds = array($entityIds);
		}

		$attributeIds = [];
		$attributes = [];

		$values = $this->getValuesAll($entityIds, $locale);

		// 

		// get distinct attribute ids from values
		foreach ($values as $value)
		{
			if( ! in_array($value->attribute_id, $attributeIds) )
			{
				$attributeIds[] = $value->attribute_id;
			}
		}

		// get attributes
		if( ! empty($attributeIds) )
		{
			$attributes = $this->attributeRepository->get(['id' => ['in' => $attributeIds]]);
		}

		$attributesById = [];
		foreach ($attributes as $attribute)
		{
			$attributesById[$attribute->id] = $attribute;
		}

		$attributeSettings = $this->attributeManager->getFieldTypes();
		$fieldHandlers = [];
		$fields = [];
		foreach ($entityIds as $entityId)
		{
			$fields[$entityId] = new stdClass();
		}

		foreach ($values as $value) {

			$attribute = $attributesById[$value->attribute_id];
			$handlerName = $attributeSettings[$attribute->field_type]['handler'];
			$hasMultipleValues = $attributeSettings[$attribute->field_type]['has_multiple_values'];
			
			if( ! array_key_exists($handlerName, $fieldHandlers) )
			{
				$fieldHandlers[$handlerName] = $this->fieldHandlerFactory->make($attribute->field_type);
			}

			$fieldHandler = $fieldHandlers[$handlerName];

			$formattedValue = $fieldHandler->formatValue($value->value, $attribute);

			if($hasMultipleValues)
			{
				$formattedValue = [$formattedValue];
			}
			
			if( isset($fields[$value->entity_id]->{$attribute->code}) && $hasMultipleValues )
			{
				$fields[$value->entity_id]->{$attribute->code} = array_merge($fields[$value->entity_id]->{$attribute->code}, $formattedValue);
				continue;
			}

			$fields[$value->entity_id]->{$attribute->code} = $formattedValue;
		}
		



		// finally return all values formatted
		return $fields;
	}

	protected function getValuesAll($entityIds, $locale)
	{
		// get values from various tables
		$valuesDatetime = $this->getValuesDatetime($entityIds, $locale);
		$valuesDecimal = $this->getValuesDecimal($entityIds, $locale);
		$valuesInteger = $this->getValuesInteger($entityIds, $locale);
		$valuesText = $this->getValuesText($entityIds, $locale);
		$valuesVarchar = $this->getValuesVarchar($entityIds, $locale);

		// get all values
		$values = array_merge(
			$valuesDatetime, 
			$valuesDecimal, 
			$valuesInteger, 
			$valuesText, 
			$valuesVarchar
		);

		return $values;
	}

	protected function getValuesDatetime($entityIds, $locale)
	{
		return $this->getValues('attribute_values_datetime', $entityIds, $locale);
	}

	protected function getValuesDecimal($entityIds, $locale)
	{
		return $this->getValues('attribute_values_decimal', $entityIds, $locale);
	}
	
	protected function getValuesInteger($entityIds, $locale)
	{
		return $this->getValues('attribute_values_integer', $entityIds, $locale);
	}
	
	protected function getValuesText($entityIds, $locale)
	{
		return $this->getValues('attribute_values_text', $entityIds, $locale);
	}
	
	protected function getValuesVarchar($entityIds, $locale)
	{
		return $this->getValues('attribute_values_varchar', $entityIds, $locale);
	}
	
	protected function getValues($table, $entityIds, $locale)
	{
		// values query
		$values = $this->db->table('entities')
					->select(
						'entities.id as entity_id',
						'attributes.id as attribute_id',
						$table . '.value'
					)
					->join('attribute_sets', 'entities.attribute_set_id', '=', 'attribute_sets.id')
					->leftJoin('set_attributes', 'attribute_sets.id', '=', 'set_attributes.attribute_set_id')
					->leftJoin('attributes', 'attributes.id', '=', 'set_attributes.attribute_id')
					->leftJoin($table, function($join) use ($table){
						$join 	->on($table . '.attribute_id', '=', 'attributes.id')
								->on($table . '.entity_id', '=', 'entities.id');
					})
					->whereIn('entities.id', $entityIds)
					->where('attributes.table', '=', $table)
					->where(function($q) use ($locale, $table){
						$q	->where($table . '.locale_id', '=', 0);

						if( ! empty($locale) )
						{
							$q->orWhere($table . '.locale_id', '=', $locale);
						}
					})
					->orderBy('entities.id', 'set_attributes.sort_order', $table . '.sort_order')
					->get();

		return $values;
	}



	/**
	 * Get entity  with attribute set and values 
	 * by object ID and entity type
	 * 
	 * @param int 		$objectID - ID of a object that is related to this entity
	 * @param string 	$entityType - entity type of that object (slug)
	 * @param array 	$with - optional relations to be fetched with entity types
	 * 
	 * @return Model
	 */
	public function fetchByObject($objectID, $entityType, $with = array()){

		/* 	
			select entity from database with it's type
			query by entity.object_id and entity_type.slug
			this query should be smaller for performance reasons
		*/
	
		$entity = DB::table('entities')
						->select(
							'entities.id', 
							'entities.object_id', 
							'entities.entity_type_id', 
							'entities.attribute_set_id',
							'entity_types.slug', 
							'entity_types.name',
							'entity_types.plural_name',
							'entity_types.parent_type',
							'entity_types.multiple_sets',
							'entity_types.archive_parent',
							'entity_types.default_attribute_set_id'
						)
						->where('entities.object_id', '=', $objectID)
						->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id')
						->where('entity_types.slug', '=', $entityType)
						->first();


		// if there is no entity with those params return false
		if(!$entity){
			$this->addErrors(array('No such entity'));
			return false;
		}

		/* 	
			create queries for each value table
			query them by entity ID and include all languages
		*/
	
		// datetime values query
		$valuesDatetimeQuery = DB::table('attribute_values_datetime')->where('entity_id', '=', $entity->id);
		// decimal values query
		$valuesDecimalQuery = DB::table('attribute_values_decimal')->where('entity_id', '=', $entity->id);
		// integer values query
		$valuesIntegerQuery = DB::table('attribute_values_integer')->where('entity_id', '=', $entity->id);
		// text values query
		$valuesTextQuery = DB::table('attribute_values_text')->where('entity_id', '=', $entity->id);
		// varchar values query
		$valuesVarcharQuery = DB::table('attribute_values_varchar')->where('entity_id', '=', $entity->id);
		// relations values query
		$valuesRelationQuery = DB::table('attribute_values_relations')->where('entity_id', '=', $entity->id);
		// assets values query
		$valuesAssetsQuery = DB::table('attribute_values_assets')->where('entity_id', '=', $entity->id);

		/* 
			make a union from all values queries to get single table like result
			all values will be returned as strings,
			but we will know what to expect with attribute handlers
		*/
	
		$valuesQuery = $valuesDatetimeQuery
						->union($valuesDecimalQuery)
						->union($valuesIntegerQuery)
						->union($valuesTextQuery)
						->union($valuesVarcharQuery)
						->union($valuesRelationQuery)
						->union($valuesAssetsQuery);

		// get all values
		$values = $valuesQuery->get();


		// extract distinct attribute ID's
		$attributeIDs = array();
		foreach ($values as $value) {
			if(!in_array($value->attribute_id, $attributeIDs)){
				$attributeIDs[] = $value->attribute_id;
			}
		}

		// if there are any attributes get them by ID
		if(!empty($attributeIDs)){
			$attributes = DB::table('attributes')->whereIn('id', $attributeIDs)->get();
		}else{
			$attributes = array();
		}

		$attributeSet = $this->getAttributeSet($entity->attribute_set_id);

		// put set in entity
		$entity->attribute_set = $attributeSet;
		
		$attributeValues = $this->getDefaultAttributeValues($attributeSet, $entity->id);
		$entity_values = $attributeValues;


		// combine values and attributes, so every value has attribute info on it self.
		foreach ($values as $value) {
			foreach ($attributes as &$attribute) {
				// attribute match for this value
				if($value->attribute_id == $attribute->id){

					// get options for this attribute if there are any
					if(!isset($attribute->options)){
						$attributeSettings = $this->attributeManager->getDataType($attribute->data_type);

						if($attributeSettings['has_options']){
							$options = DB::table('attribute_options')->where('attribute_id', '=', $attribute->id)->get();
							$attribute->options = $options;
						}else{
							$attribute->options = array();
						}
					}

					// let attribute handle process value
					$attributeHandler = $this->attributeHandlerFactory->make($attribute->data_type);
					$value = $attributeHandler->getValue($value, $attribute, $attribute->options);

					if($attributeSettings['has_multiple_values']){
						$value->value = array($value->value);
					}
					// get attribute code
					$value->code = $attribute->code;

					if(!array_key_exists($value->code, $entity_values)){
						$entity_values[$value->code] = array();
					}

					if(!array_key_exists($value->language_id, $entity_values[$value->code])){
						$entity_values[$value->code][$value->language_id] = $value;
					}else{
						if($attributeSettings['has_multiple_values']){
							$entity_values[$value->code][$value->language_id]->value = array_merge($entity_values[$value->code][$value->language_id]->value, $value->value);
						}else{
							$entity_values[$value->code][$value->language_id]->value = $value->value;
						}
						
					}
				}
			}
		}

		// put values in entity
		$entity->attribute_values = $entity_values;

		


		// use entity transformer to transform all object as wanted
		$entityTransformer = new EntityTransformer;
		$entity = $entityTransformer->transform($entity);

		
		// finally return entity
		return $entity;
		
	}

	/**
	 * Get attribute set with its groups and attributes
	 * @uses Illuminate\Support\Facades\DB
	 *
	 * @param int $attributeSetID
	 * 
	 * @return Model
	 */
	protected function getAttributeSet($attributeSetID){

		// get attribute set for this entity
		$attributeSetData = DB::table('attribute_sets')
							->select(
								'attribute_sets.id as id',
								'attribute_sets.entity_type_id as entity_type_id',
								'attribute_sets.slug as slug',
								'attribute_sets.name as name',
								'attribute_groups.id as group_id',
								'attribute_groups.slug as group_code',
								'attribute_groups.admin_label as group_admin_label',
								'attribute_groups.sort_order as group_sort_order', 
								'set_attributes.sort_order as attribute_sort_order',
								'set_attributes.id as set_attribute_id',
								'attributes.id as attribute_id',
								'attributes.code as attribute_code',
								'attributes.admin_label as attribute_admin_label',
								'attributes.admin_notice as attribute_admin_notice',
								'attributes.data_type as data_type',
								'attributes.default_value as default_value',
								'attributes.is_unique as is_unique',
								'attributes.is_required as is_required',
								'attributes.visibility as visibility',
								'attributes.status as status',
								'attributes.language_dependable as language_dependable',
								'attributes.data as data',
								'attribute_options.id as option_id',
								'attribute_options.language_id as language_id',
								'attribute_options.label as option_label',
								'attribute_options.value as option_value',
								'attribute_options.is_default as is_default',
								'attribute_options.sort_order as option_sort_order'
							)
							->leftJoin('attribute_groups', 'attribute_groups.attribute_set_id', '=', 'attribute_sets.id')
							->leftJoin('set_attributes', function($join){
								$join 	->on('set_attributes.attribute_set_id', '=', 'attribute_sets.id')
										->on('set_attributes.attribute_group_id', '=', 'attribute_groups.id');
							})
							->leftJoin('attributes', 'attributes.id', '=', 'set_attributes.attribute_id')
							->leftJoin('attribute_options', 'attribute_options.attribute_id', '=', 'attributes.id')
							->where('attribute_sets.id', '=', $attributeSetID)
							->orderBy(
								'attribute_sets.id', 
								'attribute_groups.sort_order', 
								'attribute_groups.id', 
								'set_attributes.sort_order',
								'set_attributes.id',
								'attribute_options.sort_order',
								'attribute_options.id'
							)
							->get();

		if(empty($attributeSetData)){
			return false;
		}

		$attributeSet = new \stdClass();

		foreach ($attributeSetData as $data) {
			if(!isset($attributeSet->id)){
				$attributeSet->id = $data->id;
				$attributeSet->entity_type_id = $data->entity_type_id;
				$attributeSet->slug = $data->slug;
				$attributeSet->name = $data->name;
				$attributeSet->groups = array();
			}

			if(empty($data->group_id)){
				continue;
			}

			if(!empty($attributeSet->groups)){
				$lastGroup = $attributeSet->groups[count($attributeSet->groups) - 1];
			}

			if(empty($attributeSet->groups) || $lastGroup->id != $data->group_id){
				$group = new \stdClass();
				$group->id = $data->group_id;
				$group->slug = $data->group_code;
				$group->admin_label = $data->group_admin_label;
				$group->sort_order = $data->group_sort_order;
				$group->set_attributes = array();
				$attributeSet->groups[] = $group;
				$lastGroup = $attributeSet->groups[count($attributeSet->groups) - 1];
			}

			if(empty($data->attribute_id)){
				continue;
			}
			if(!empty($lastGroup->set_attributes)){
				$lastAttribute = $lastGroup->set_attributes[count($lastGroup->set_attributes) - 1];
			}
			if(empty($lastGroup->set_attributes) || $lastAttribute->id != $data->attribute_id){
				$attribute = new \stdClass();
				$attribute->id = $data->attribute_id;
				$attribute->attribute_id = $data->attribute_id;
				$attribute->code = $data->attribute_code;
				$attribute->admin_label = $data->attribute_admin_label;
				$attribute->admin_notice = $data->attribute_admin_notice;
				$attribute->data_type = $data->data_type;
				$attribute->default_value = $data->default_value;
				$attribute->is_unique = $data->is_unique;
				$attribute->is_required = $data->is_required;
				$attribute->visibility = $data->visibility;
				$attribute->status = $data->status;
				$attribute->language_dependable = $data->language_dependable;
				$attribute->data = json_decode($data->data);
				$attribute->sort_order = $data->attribute_sort_order;
				$attribute->options = array();

				$attributeSet->groups[count($attributeSet->groups) - 1]->set_attributes[] = $attribute;

				$lastAttribute = $attributeSet->groups[count($attributeSet->groups) - 1]->set_attributes[count($attributeSet->groups[count($attributeSet->groups) - 1]->set_attributes) - 1];
			}

			if(!empty($data->option_id)){
				$option = new \stdClass();
				$option->id = $data->option_id;
				$option->language_id = $data->language_id;
				$option->label = $data->option_label;
				$option->value = $data->option_value;
				$option->is_default = $data->is_default;
				$option->sort_order = $data->option_sort_order;

				$attributeSet->groups[count($attributeSet->groups) - 1]->set_attributes[count($attributeSet->groups[count($attributeSet->groups) - 1]->set_attributes) - 1]->options[] = $option;
			}
		}

		return $attributeSet;
		
	}


	/**
	 * Get attribute values for attribute set
	 *
	 * @param object $attributeSet
	 * 
	 * @return object
	 */
	protected function getDefaultAttributeValues($attributeSet, $entityID = null, $lang_id = null){
		$attributeValues = array();

		if(empty($attributeSet->groups)){
			return $attributeValues;
		}

		foreach ($attributeSet->groups as $group) {
			if(empty($group->set_attributes)){
				continue;
			}

			foreach ($group->set_attributes as $attribute) {
				$attributeValue = new \stdClass();
				$attributeValue->attribute_id = $attribute->id;
				$attributeValue->entity_id = $entityID;
				$attributeValue->entity_type_id = $attributeSet->entity_type_id;

				$attributeHandler = $this->attributeHandlerFactory->make($attribute->data_type);

				$attributeValue->value = $attributeHandler->getDefaultValue($attribute, $attribute->options);

				if(!$attribute->language_dependable){
					$attributeValue->language_id = 0;
					$attributeValues[$attribute->code][0] = $attributeValue;
				}elseif($lang_id){
					$attributeValue->language_id = $lang_id;
					$attributeValues[$attribute->code][$lang_id] = $attributeValue;
				}else{
					$langIDs = DB::table('languages')->lists('id');
					foreach($langIDs as $langID){
						$attrVal = clone($attributeValue);
						$attrVal->language_id = $langID;
						$attributeValues[$attribute->code][$langID] = $attrVal;
					}
				}
			}
		}

		return $attributeValues;
	}

	/**
	 * Get entities by entity IDs
	 * @uses Illuminate\Support\Facades\DB
	 *
	 * @param array | int $entityIDs
	 * @param int $lang_id
	 * @param array $with - optional objects to be fetched with entities (relations, assets)
	 * 
	 * @return Model
	 *
	 * @todo some queries should be more performant
	 */
	public function getByID($entityIDs, $lang_id, $with = array()){
		/* 	
			select entities from database with it's type
			query by entity.object_id and entity_type.slug
			this query should be smaller for performance reasons
		*/
		
		if(!is_array($entityIDs)){
			$entityIDs = array($entityIDs);
		}

		$entities = DB::table('entities')
					->select(
						'entities.id', 
						'entities.object_id', 
						'entities.entity_type_id', 
						'entities.attribute_set_id',
						'entity_types.slug', 
						'entity_types.name',
						'entity_types.plural_name',
						'entity_types.parent_type',
						'entity_types.multiple_sets',
						'entity_types.archive_parent',
						'entity_types.default_attribute_set_id'
					)
					->whereIn('entities.id', $entityIDs)
					->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id')
					->get();

		// if there is no entity with those params return false
		if(!$entities){
			$this->addErrors(array('No such entities'));
			return false;
		}

		if(in_array('attributes', $with)){
			$attributes = $this->getAttributesForEntities($entityIDs, $lang_id, $with);
		}

		foreach ($entities as &$entity) {
			if($attributes && is_array($attributes) && $attributes[$entity->id]){
				$entity->fields = $attributes[$entity->id];
			}
		}

		return $entities;
	}

	/**
	 * Get entities by object IDs
	 * @uses Illuminate\Support\Facades\DB
	 *
	 * @param array | int $objectIDs
	 * @param int $lang_id
	 * @param array $with - optional objects to be fetched with entities (relations, assets)
	 * 
	 * @return Model
	 *
	 * @todo some queries should be more performant
	 */
	public function getByObjectID($objectIDs, $lang_id, $with = array()){
		/* 	
			select entities from database with it's type
			query by entity.object_id and entity_type.slug
			this query should be smaller for performance reasons
		*/
		
		if(!is_array($objectIDs)){
			$objectIDs = array($objectIDs);
		}

		$entities = DB::table('entities')
					->select(
						'entities.id', 
						'entities.object_id', 
						'entities.entity_type_id', 
						'entities.attribute_set_id',
						'entity_types.slug', 
						'entity_types.name',
						'entity_types.plural_name',
						'entity_types.parent_type',
						'entity_types.multiple_sets',
						'entity_types.archive_parent',
						'entity_types.default_attribute_set_id',
						'attribute_sets.name as attribute_set_name',
						'attribute_sets.slug as attribute_set_slug'
					)
					->whereIn('entities.object_id', $objectIDs)
					->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id')
					->join('attribute_sets', 'attribute_sets.id', '=', 'entities.attribute_set_id')
					->get();



		// if there is no entity with those params return false
		if(!$entities){
			$this->addErrors(array('No such entities'));
			return false;
		}

		$entityIDs = array();
		foreach ($entities as $entity) {
			$entityIDs[] = $entity->id;
		}

		// $attributeSet = $this->getAttributeSet($entity->attribute_set_id);

		// put set in entity
		// $entity->attribute_set = $attributeSet;
		
		// $defaultValues = $this->getDefaultAttributeValues($attributeSet, $entity->id);
		// $entity_values = $attributeValues;

		if(in_array('attributes', $with)){
			$attributes = $this->getAttributesForEntities($entityIDs, $lang_id, $with);
		}

		$keyedEntities = array();

		foreach ($entities as &$entity) {
			if($attributes && is_array($attributes) && isset($attributes[$entity->id])){
				$entity->fields = $attributes[$entity->id];
			}else{
				$entity->fields = array();
			}
			$keyedEntities[$entity->object_id] = $entity;
		}

		return $keyedEntities;
	}


	/**
	 * Get entity attributes by entity IDs
	 * @uses Illuminate\Support\Facades\DB
	 * 
	 * @param array $with - optional objects to be fetched with attributes (relations, assets)
	 * 
	 * @return Model
	 *
	 * @todo some queries should be more performant
	 */
	public function getAttributesForEntities($entityIDs, $lang_id, $with = array(), $defaultValues = array()){
		
		if(!is_array($entityIDs)){
			$entityIDs = array($entityIDs);
		}

		/* 	create queries for each value table
			query them by entity ID and language ID,
			also include values with language_id = 0 - that are language independable values
		*/
	
		// datetime values query
		$valuesDatetimeQuery = $this->createValuesQuery('attribute_values_datetime', $entityIDs, $lang_id);

		// decimal values query
		$valuesDecimalQuery = $this->createValuesQuery('attribute_values_decimal', $entityIDs, $lang_id);

		// integer values query
		$valuesIntegerQuery = $this->createValuesQuery('attribute_values_integer', $entityIDs, $lang_id);

		// text values query
		$valuesTextQuery = $this->createValuesQuery('attribute_values_text', $entityIDs, $lang_id);

		// varchar values query
		$valuesVarcharQuery = $this->createValuesQuery('attribute_values_varchar', $entityIDs, $lang_id);

		// relations values query
		$valuesRelatioinsQuery = $this->createValuesQuery('attribute_values_relations', $entityIDs, $lang_id);
		
		// assets values query
		$valuesAssetsQuery = $this->createValuesQuery('attribute_values_assets', $entityIDs, $lang_id);

		// make a union from all values queries to get single table like result
		// all values will be returned as strings,
		// but we will know what to expect with attribute handlers
		$valuesDatetime = $valuesDatetimeQuery->get();
		$valuesDecimal = $valuesDecimalQuery->get();
		$valuesInteger = $valuesIntegerQuery->get();
		$valuesText = $valuesTextQuery->get();
		$valuesVarchar = $valuesVarcharQuery->get();
		$valuesRelations = $valuesRelatioinsQuery->get();
		$valuesAssets = $valuesAssetsQuery->get();

						// ->unionAll($valuesDecimalQuery);
						// ->union($valuesIntegerQuery)
						// ->union($valuesTextQuery);
						// ->union($valuesVarcharQuery)
						// ->union($valuesRelatioinsQuery);

		// get all values
		$values = array_merge(
			$valuesDatetime, 
			$valuesDecimal, 
			$valuesInteger, 
			$valuesText, 
			$valuesVarchar, 
			$valuesRelations, 
			$valuesAssets
		);

		$attributeSettings = $this->attributeManager->getDataTypes();
		
		$fieldsByHandler = array();
		foreach ($values as $value) {

			$handler = $attributeSettings[$value->data_type]['handler_name'];

			if(!array_key_exists($handler, $fieldsByHandler) || !is_array($fieldsByHandler[$handler])){
				$fieldsByHandler[$handler] = array();
			}
			$entity_id = intval($value->entity_id);
			if(!array_key_exists($entity_id, $fieldsByHandler[$handler])){
				$fieldsByHandler[$handler][$entity_id] = array();
			}
			
			if(!array_key_exists($value->code, $fieldsByHandler[$handler][$entity_id])){
				$field = new \stdClass();
				$field->value = $value->value;
				$field->entity_id = $value->entity_id;

				$attribute = new \stdClass();
				$attribute->id = $value->attribute_id;
				$attribute->code = $value->code;
				$attribute->label = $value->label;
				$attribute->description = $value->description;
				$attribute->data_type = $value->data_type;
				$attribute->data = json_decode($value->data);
				$attribute->visibility = $value->visibility;
				$attribute->is_filterable = $value->is_filterable;
				$attribute->sort_order = $value->attribute_sort_order;
				$field->attribute = $attribute;

				$group = new \stdClass();
				$group->id = $value->group_id;
				$group->code = $value->group_code;
				$group->name = $value->group_name;
				$group->sort_order = $value->group_sort_order;
				$field->group = $group;

				if($attributeSettings[$value->data_type]['has_multiple_values']){
					$field->value = array($field->value);
				}
				
				$fieldsByHandler[$handler][$entity_id][$value->code] = $field;
				
			}elseif($attributeSettings[$value->data_type]['has_multiple_values']){
				$fieldsByHandler[$handler][$entity_id][$value->code]->value[] = $value->value;
			}else{
				$fieldsByHandler[$handler][$entity_id][$value->code]->value = $value->value;
			}
		}
		
		$fields = array();

		// if(!empty($defaultValues) && is_array($defaultValues)){
		// 	$fields = $defaultValues;
		// }
		
		foreach ($fieldsByHandler as $handlerName => $attributes) {
			$handler = App::make($handlerName);
			$attributes = $handler->fetchValues($attributes, $lang_id, $with);

			foreach ($attributes as $entity_id => $entityAttributes) {
				foreach ($entityAttributes as $code => $attribute) {
					if(!isset($fields[$entity_id]) || !is_array($fields[$entity_id])){
						$fields[$entity_id] = array();
					}
					$fields[$entity_id][$code] = $attribute;
				}
				
				
			}
		}

		foreach ($fields as $entity_id => &$values) {
			uasort($values, array($this, 'compareFields'));
		}
		

		// finally return all values formatted
		return $fields;
	}

	protected function compareFields($a, $b){
		if($a->group->sort_order == $b->group->sort_order){
			if($a->attribute->sort_order == $b->attribute->sort_order){
				return 0;
			}else{
				return ($a->attribute->sort_order < $b->attribute->sort_order) ? -1 : 1;
			}
		}else{
			return ($a->group->sort_order < $b->group->sort_order) ? -1 : 1;
		}
	}

	protected function createValuesQuery($table, $entityIDs, $lang_id){
		// datetime values query
		$valuesQuery = DB::table($table)
					->select(
						$table . '.attribute_id',
						$table . '.entity_id',
						$table . '.language_id', 
						$table . '.sort_order as value_sort_order',
						$table . '.value',
						'attributes.code',
						'attributes.data_type',
						'attributes.data',
						'attributes.visibility',
						'attributes.is_filterable',
						'attribute_translations.label',
						'attribute_translations.description',
						'attribute_groups.id as group_id',
						'attribute_groups.slug as group_code',
						'attribute_group_translations.name as group_name',
						'attribute_groups.sort_order as group_sort_order',
						'set_attributes.sort_order as attribute_sort_order'
					)
					->join('attributes', $table . '.attribute_id', '=', 'attributes.id')
					->leftJoin('attribute_translations', function($join) use ($lang_id){
						$join	->on('attributes.id', '=', 'attribute_translations.attribute_id')
								->where('attribute_translations.language_id', '=', $lang_id);
					})
					->join('entities', $table . '.entity_id', '=', 'entities.id')
					->leftJoin('set_attributes', function($join){
						$join 	->on('attributes.id', '=', 'set_attributes.attribute_id')
								->on('entities.attribute_set_id', '=', 'set_attributes.attribute_set_id');
					})
					->leftJoin('attribute_groups', 'set_attributes.attribute_group_id', '=', 'attribute_groups.id')
					->leftJoin('attribute_group_translations', function($join) use($lang_id){
						$join 	->on('attribute_groups.id', '=', 'attribute_group_translations.attribute_group_id')
								->where('attribute_group_translations.language_id', '=', $lang_id);
					})
					->whereIn('entity_id', $entityIDs)
					->where(function($q) use ($lang_id, $table){
						$q	->where($table . '.language_id', '=', $lang_id)
							->orWhere($table . '.language_id', '=', 0);
					})
					->orderBy('set_attributes.sort_order', 'attributes.id', $table . '.sort_order')
					->groupBy($table . '.id');

		return $valuesQuery;
	}

	/**
	 * Get object IDs from entity IDs
	 * 
	 * @param array $entityIDs
	 * 
	 * @return Model
	 */
	public function getObjectIDs($entityIDs = array()){
		if(!is_array($entityIDs)){
			$entityIDs = array($entityIDs);
		}

		$entities = DB::table('entities')
						->whereIn('id', $entityIDs)
						->lists('object_id', 'id');

		return $entities;
	}

	/**
	 * Get object IDs from attribute sets
	 * 
	 * @param array $entityIDs
	 * 
	 * @return Model
	 */
	public function getObjectIDsFromAttributeSets($attributeSetIDs = array()){
		if(!is_array($attributeSetIDs)){
			$attributeSetIDs = array($attributeSetIDs);
		}

		$entities = DB::table('entities')
						->whereIn('attribute_set_id', $attributeSetIDs)
						->lists('object_id');

		return $entities;
	}


	

}