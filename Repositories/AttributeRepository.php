<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Repositories;

use Cookbook\Core\Exceptions\Exception;
use Illuminate\Database\Connection;

use Cookbook\Core\Repositories\AbstractRepository;
use Cookbook\Core\Traits\ValidatorTrait;

use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Cookbook\Contracts\EAV\FieldHandlerFactoryContract;
use Cookbook\Contracts\EAV\AttributeRepositoryContract;
use Cookbook\EAV\Managers\AttributeManager;


/**
 * AttributeRepository class
 * 
 * Repository for attribute database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 * @uses 		Illuminate\Contracts\Validation\Factory
 * @uses   		Cookbook\Contracts\EAV\FieldHandlerFactoryContract
 * @uses   		Cookbook\EAV\Managers\AttributeManager
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeRepository extends AbstractRepository implements AttributeRepositoryContract
{

	/**
	 * Factory for attribute handlers,
	 * makes appropriate attriubte handler depending on attribute data type
	 * 
	 * @var Cookbook\Contracts\EAV\FieldHandlerFactoryContract
	 */
	protected $fieldHandlerFactory;

	/**
	 * Helper for attributes
	 * 
	 * @var Vizioart/Attributes/Manager/AttributeManager
	 */
	protected $attributeManager;

	/**
	 * Array of available field_types for attribute
	 *
	 * @var array
	 */
	protected $availableFieldTypes;

	/**
	 * Create new AttributeRepository
	 * 
	 * @param Illuminate\Database\Connection $db
	 * @param Illuminate\Contracts\Validation\Factory
	 * @param Cookbook\EAV\Handlers\FieldHandlerFactoryContract $fieldHandlerFactory
	 * @param Cookbook\EAV\Managers\AttributeManager $attributeManager
	 * 
	 * @return void
	 */
	public function __construct(Connection $db,
								FieldHandlerFactoryContract $fieldHandlerFactory,
								AttributeManager $attributeManager)
	{

		// AbstractRepository constructor
		parent::__construct($db);
		
		// Inject dependencies
		$this->fieldHandlerFactory = $fieldHandlerFactory;
		$this->attributeManager = $attributeManager;

		// get available field types
		$this->availableFieldTypes = $this->attributeManager->getFieldTypes();

	}


	/**
	 * Create new attribute
	 * 
	 * @param array $model - attribute params (field_type, is_required...)
	 * options are also included in $model
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	protected function _create($model)
	{
		// get options from model if there are any
		$options = [];
		if(!empty($model['options']) && is_array($model['options']))
		{
			$options = $model['options'];
		}
		
		// unset options from model 
		// for attribute insertation
		unset($model['options']);

		// insert attribute
		$attribute = $this->insertAttribute($model);

		if(!$attribute)
		{
			throw new \Exception('Failed to insert attribute');
		}

		// set relation to attribute in all options
		for($i = 0; $i < count($options); $i++)
		{
			$options[$i]['attribute_id'] = $attribute->id;
		}

		// update all options for attribute
		$this->updateOptions($options, null, $attribute);

		// and return newly created attribute
		return $attribute;
		
	}

	/**
	 * Update attribute and its options
	 * 
	 * @param array $model - attribute params (code, is_required...)
	 * options are also included in model
	 *
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	protected function _update($id, $model)
	{

		// find attribute with that ID
		$attribute = $this->db->table('attributes')->find($id);

		if( ! $attribute )
		{
			throw new Exception(['There is no attribute with that ID.'], 400);
		}

		// extract options from model
		$options = [];
		if(!empty($model['options']) && is_array($model['options']))
		{
			$options = $model['options'];
		}

		// remove options from model for update
		unset($model['options']);

		// update attribute
		$this->updateAttribute($id, $model, $attribute);

		// set relation to attribute in all options
		for($i = 0; $i < count($options); $i++)
		{
			$options[$i]['attribute_id'] = $id;
		}

		// get all options from database
		$oldOptions = $this->db->table('attribute_options')->where('attribute_id', '=', $id)->get();

		$keyedOptions = [];

		foreach ($oldOptions as $option)
		{
			$keyedOptions[$option->id] = $option;
		}

		// update options
		$this->updateOptions($options, $keyedOptions, $attribute);

		// and return ID
		return $attribute;
	}

	/**
	 * Delete attribute and its options
	 * 
	 * @param integer $id - ID of attribute that will be deleted
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function _delete($id)
	{

		try
		{
			// get the attribute
			$attribute = $this->db->table('attributes')->find($id);
			if(!$attribute)
			{
				$this->addErrors('Attribute ID needs to be provided.');
				return false;
			}

			// init attribute handler
			$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);

			// delete related values for this attribute
			$fieldHandler->sweepAfterAttribute($attribute);
			// delete all related attributes in sets for this attribute
			$this->db->table('set_attributes')->where('attribute_id', '=', $attribute->id)->delete();
			// delete all related options for this attribute
			$this->db->table('attribute_options')->where('attribute_id', '=', $attribute->id)->delete();
			// delete attribute translations
			$this->db->table('attribute_translations')->where('attribute_id', '=', $attribute->id)->delete();
			// delete the attribute
			$this->db->table('attributes')->where('id', '=', $attribute->id)->delete();

			return true;

		}
		catch(Exception $e)
		{
			$this->addErrors('Failed to delete attribute. There was an error.');
			return false;
		}
	}

	/**
	 * Validate attribute params and insert in database
	 * 
	 * @param array $params = attribute params (field_type, is_required...)
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 * 
	 * @todo translations timestamps are not entered because of mass insert
	 */
	protected function insertAttribute($params)
	{

		// separate params array in attribute and attribute translations params
		if(!empty($params['translations']))
		{
			$attributeTranslations = $params['translations'];
		}
		else
		{
			$attributeTranslations = [];
		}
		
		unset($params['translations']);

		if(isset($params['data']))
		{
			$params['data'] = json_encode($params['data']);
		}

		$params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');

		// insert attribute in database
		$attribute_id = $this->db->table('attributes')->insertGetId($params);

		// populate translations with attribute data
		for($i = 0; $i < count($attributeTranslations); $i++)
		{
			$attributeTranslations[$i]['attribute_id'] = $attribute_id;
		}

		if(!empty($attributeTranslations))
		{
			// insert new translations in database
			$this->db->table('attribute_translations')->insert($attributeTranslations);
		}

		return $this->db->table('attributes')->find($attribute_id);
	}

	/**
	 * Validate attribute params and update it in database
	 * 
	 * @param array $params = attribute params (field_type, is_required...)
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 * 
	 * @todo translations timestamps are not entered because of mass insert
	 */
	protected function updateAttribute($id, $params, $attribute)
	{

		// separate params array in attribute and attribute translations params
		if( ! empty($params['translations']) )
		{
			$attributeTranslations = $params['translations'];
		}
		else
		{
			$attributeTranslations = [];
		}

		unset($params['translations']);

		// validate attribute translations
		for( $i = 0; $i < count($attributeTranslations); $i++ )
		{
			$attributeTranslations[$i]['attribute_id'] = $id;
		}

		if( isset($params['data']) )
		{
			$params['data'] = json_encode($params['data']);
		}

		

		$attributeParams = json_decode(json_encode($attribute), true);
		$attributeParams = array_merge($attributeParams, $params);

		unset($attributeParams['id']);

		$attributeParams['updated_at'] = date('Y-m-d H:i:s');

		$this->db->table('attributes')->where('id', '=', $id)->update($attributeParams);

		// delete all existing translations from database
		$this->db->table('attribute_translations')->where('attribute_id', '=', $id)->delete();
		
		if( ! empty($attributeTranslations) )
		{
			// insert new translations in database
			$this->db->table('attribute_translations')->insert($attributeTranslations);
		}
	}

	/**
	 * Updates all options in array
	 * 
	 * @param array $options - new params for attribute options
	 * @param array $oldOptions (optional) - old version of attribute options
	 * @param $attribute - attribute model
	 * 
	 * @return boolean
	 */
	protected function updateOptions(array $options, array $oldOptions = null, $attribute)
	{
		// fabricate attribute handler for this attribute type 
		// (needed for options update)
		$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);

		$success = true;
		$optionIDs = [];
		foreach ($options as $option)
		{

			// if option is alreay in database get its old version
			if( ! empty($option['id']) && ! empty($oldOptions) )
			{
				$oldOption = $oldOptions[$option['id']];
				$optionIDs[] = $option['id'];
			}
			else
			{
				$oldOption = null;
			}

			$this->updateOption($option, $oldOption);
		}

		// if there are old options and there were no error
		// delete options that don't exist anymore.
		if( ! empty($oldOptions) )
		{
			foreach ($oldOptions as $optionID => $option)
			{
				if( ! in_array($optionID, $optionIDs) )
				{
					continue;
				}

				if( ! empty($fieldHandler) )
				{
					$fieldHandler->sweepAfterOption($option);
				}

				$this->db->table('attribute_options')
						 ->where('id', '=', $option->id)
						 ->delete();
			}
		}
	}


	/**
	 * Validate option params and insert in database
	 * 
	 * @param array $params - option params (label, value...)
	 * @param stdClass $oldOption (optional) - old option value
	 * 
	 * @return boolean
	 */
	protected function updateOption($params, $oldOption)
	{
		
		if( ! empty($oldOption) )
		{
			// if option is already in database - update
			$optionParams = json_decode(json_encode($option), true);
			$optionParams = array_merge($optionParams, $params);
			
			unset($optionParams['id']);

			$this->db->table('attribute_options')
					 ->where('id', '=', $params['id'])
					 ->update($optionParams);

			return $params['id'];
		}
		else
		{
			// if option is new - insert
			$option_id = $this->db->table('attribute_options')->insertGetId($params);
			return $option_id;
		}
		
	}


	/**
	 * Check if the value of the attribute is unique
	 * 
	 * @param array $params - (attribute_id, value)
	 * 
	 * @return boolean
	 */
	public function uniqueValue($params)
	{
		// var_dump($params);

		if(empty($params['attribute_id']))
		{
			$this->addErrors('Invalid params');
			return false;
		}
		
		$attributeDefinition = $this->db->table('attributes')->find($params['attribute_id']);
		if(!$attributeDefinition)
		{
			$this->addErrors('Invalid params');
			return false;
		}

		$fieldHandler = $this->fieldHandlerFactory->make($attributeDefinition->field_type);

		$unique = $fieldHandler->uniqueValue($params, $attributeDefinition);

		return $unique;
	}

	/**
	 * Check if there is an attribute with that code
	 * 
	 * @param array $params - (attribute_id, value)
	 * 
	 * @return boolean
	 */
	public function uniqueCode($params)
	{
		// var_dump($params);

		if(empty($params['code']))
		{
			$this->addErrors('Invalid params');
			return false;
		}

		if(empty($params['attribute_id']))
		{
			$attributeId = 0;
		}
		else
		{
			$attributeId = intval($params['attribute_id']);
		}
		
		$attribute = $this->db->table('attributes')->where('code', '=', $params['code'])->where('id', '!=', $attributeId)->first();

		if($attribute)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	// ----------------------------------------------------------------------------------------------
	// GETTERS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Get attribute by ID
	 * 
	 * @param int $id - ID of attribute to be fetched
	 * 
	 * @return array
	 */
	public function fetch($id)
	{
		$attribute = $this->db->table('attributes')->find($id);
		if($attribute)
		{
			$fieldHandler = $this->fieldHandlerFactory->make($attribute['field_type']);
			$attribute = $fieldHandler->transformAttribute($attribute);
		}
		return $attribute;
	}

	/**
	 * Get all attributes
	 * 
	 * @return array
	 */
	public function fetchAll()
	{
		return $this->db->table('attributes')->get();
	}

	/**
	 * Get attributes as filters for entities
	 * 
	 * @param array $filters - criteria for attributes
	 * 
	 * @return array
	 */
	public function getFilters($filters = [], $language_id = 0)
	{
		
		$query = $this->db->table('attributes')
					->select
					(
						'attributes.id as attribute_id',
						'attributes.code as code',
						'attributes.is_filterable as is_filterable',
						'attribute_translations.label as label',
						'attribute_translations.description as description',
						'attribute_options.id as option_id',
						'attribute_options.value as option_value',
						'attribute_options.label as option_label',
						'attribute_options.sort_order as option_sort_order'
					)
					->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
					->join('set_attributes', 'set_attributes.attribute_id', '=', 'attributes.id')
					->join('attribute_sets', 'attribute_sets.id', '=', 'set_attributes.attribute_set_id')
					->join('entity_types', 'entity_types.id', '=', 'attribute_sets.entity_type_id')
					->leftJoin('attribute_options', 'attribute_options.attribute_id', '=', 'attributes.id')
					->where('attributes.is_filterable', '=', true)
					->where('attribute_translations.language_id', '=', $language_id)
					->where
					(
						function($query) use ($language_id)
						{
							$query	->where('attribute_options.language_id', '=', $language_id)
									->orWhere('attribute_options.language_id', '=', 0);
						}
					)
					->orderBy('attributes.id')
					->orderBy('attribute_options.sort_order', 'desc')
					->groupBy('attributes.id')
					->groupBy('attribute_options.id');


		if(array_key_exists('for', $filters))
		{
			$query->where('entity_types.slug', '=', $filters['for']);
		}

		if(array_key_exists('set', $filters))
		{
			$query->where('attribute_sets.name', '=', $filters['set']);
		}

		if(array_key_exists('code', $filters))
		{
			$query->where('attributes.code', '=', $filters['code']);
		}


		$filterResults = $query->get();
		$attributesKeyed = [];
		$attributes = [];

		foreach ($filterResults as $filterResult)
		{

			if(!$filterResult->option_id)
			{
				continue;
			}

			if(!array_key_exists($filterResult->code, $attributesKeyed))
			{
				$thisfilter = 
				[
					'attribute_id' => $filterResult->attribute_id,
					'is_filterable' => $filterResult->is_filterable,
					'code' => $filterResult->code,
					'label' => $filterResult->label,
					'description' => $filterResult->description,
					'options' => []
				];

				$attributesKeyed[$filterResult->code] = $thisfilter;
				$attributes[] = $thisfilter;
			}

			

			$option = 
			[
				'id' => $filterResult->option_id,
				'filter_value' => $filterResult->option_id,
				'value' => $filterResult->option_value,
				'label' => $filterResult->option_label,
				'sort_order' => $filterResult->option_sort_order,
			];

			$attributes[count($attributes) - 1]['options'][] = $option;
		}

		if(empty($attributes))
		{
			return false;
		}

		return $attributes;
	}
}