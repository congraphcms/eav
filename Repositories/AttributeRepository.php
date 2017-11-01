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

use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\FieldHandlerFactoryContract;
use Cookbook\Core\Exceptions\Exception;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Facades\Trunk;
use Cookbook\Core\Repositories\AbstractRepository;
use Cookbook\Core\Repositories\Collection;
use Cookbook\Core\Repositories\Model;
use Cookbook\Core\Repositories\UsesCache;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;


/**
 * AttributeRepository class
 * 
 * Repository for attribute database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 * @uses 		Illuminate\Contracts\Validation\Factory
 * @uses   		Cookbook\Contracts\Eav\FieldHandlerFactoryContract
 * @uses   		Cookbook\Eav\Managers\AttributeManager
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeRepository extends AbstractRepository implements AttributeRepositoryContract//, UsesCache
{

// ----------------------------------------------------------------------------------------------
// PARAMS
// ----------------------------------------------------------------------------------------------
// 
// 
// 
	/**
	 * Factory for attribute handlers,
	 * makes appropriate attriubte handler depending on attribute data type
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
	 * Array of available field_types for attribute
	 *
	 * @var array
	 */
	protected $availableFieldTypes;

	protected static $counter;

	/**
	 * Create new AttributeRepository
	 * 
	 * @param Illuminate\Database\Connection $db
	 * @param Illuminate\Contracts\Validation\Factory
	 * @param Cookbook\Eav\Handlers\FieldHandlerFactoryContract $fieldHandlerFactory
	 * @param Cookbook\Eav\Managers\AttributeManager $attributeManager
	 * 
	 * @return void
	 */
	public function __construct(Connection $db,
								FieldHandlerFactoryContract $fieldHandlerFactory,
								AttributeManager $attributeManager)
	{
		$this->type = 'attributes';

		// $this->cacheDuration = 60;

		// AbstractRepository constructor
		parent::__construct($db);
		
		// Inject dependencies
		$this->fieldHandlerFactory = $fieldHandlerFactory;
		$this->attributeManager = $attributeManager;

		// get available field types
		$this->availableFieldTypes = $this->attributeManager->getFieldTypes();

		self::$counter = 0;
	}

// ----------------------------------------------------------------------------------------------
// CRUD
// ----------------------------------------------------------------------------------------------
// 
// 
// 


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

		$model['status'] = 'user_defined';

		// insert attribute
		$attribute = $this->insertAttribute($model);

		if(!$attribute)
		{
			throw new \Exception('Failed to insert attribute');
		}

		$optionParams = [];
		// set relation to attribute in all options
		for($i = 0; $i < count($options); $i++)
		{
			$optionParam = [];
			$optionParam['attribute_id'] = $attribute->id;
			$optionParam['value'] = $options[$i]['value'];
			$optionParam['label'] = $options[$i]['label'];
			$optionParam['locale'] = (isset($options[$i]['locale']))?$options[$i]['locale']:0;
			$optionParam['default'] = $options[$i]['default'];
			$optionParam['sort_order'] = $i;
			$optionParams[] = $optionParam;
		}

		// update all options for attribute
		$this->updateOptions($optionParams, $attribute);

		$attribute = $this->fetch($attribute->id);

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
		$attribute = $this->fetch($id);
		
		// extract options from model
		$options = [];
		if(!empty($model['options']) && is_array($model['options']))
		{
			$options = $model['options'];
		}

		// remove options from model for update
		unset($model['options']);

		$optionParams = [];
		// set relation to attribute in all options
		for($i = 0; $i < count($options); $i++)
		{
			$optionParam = [];
			$optionParam['attribute_id'] = $attribute->id;
			$optionParam['value'] = $options[$i]['value'];
			$optionParam['label'] = $options[$i]['label'];
			$optionParam['locale'] = (isset($options[$i]['locale']))?$options[$i]['locale']:0;
			$optionParam['default'] = $options[$i]['default'];
			$optionParam['sort_order'] = $i;
			if( ! empty($options[$i]['id']) )
			{
				$optionParam['id'] = $options[$i]['id'];
			}
			$optionParams[] = $optionParam;
		}

		// update options
		$this->updateOptions($optionParams, $attribute);

		// update attribute
		$id = $this->updateAttribute($id, $model, $attribute);

		Trunk::forgetType('attribute');

		$attribute = $this->fetch($id);

		// and return attrubute
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
		// get the attribute
		$attribute = $this->fetch($id);
		var_dump("delete");
		if(!$attribute)
		{
			throw new NotFoundException(['There is no attribute with that ID.']);
		}

		// delete all related options for this attribute
		$this->db->table('attribute_options')->where('attribute_id', '=', $attribute->id)->delete();
		
		// delete the attribute
		$this->db->table('attributes')->where('id', '=', $attribute->id)->delete();

		Trunk::forgetType('attribute');

		return $attribute;
	}

// ----------------------------------------------------------------------------------------------
// CRUD - HELPERS
// ----------------------------------------------------------------------------------------------
// 
// 
// 
	/**
	 * Validate attribute params and insert in database
	 * 
	 * @param array $params = attribute params (field_type, is_required...)
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function insertAttribute($params)
	{

		// // separate params array in attribute and attribute translations params
		// if(!empty($params['translations']))
		// {
		// 	$attributeTranslations = $params['translations'];
		// }
		// else
		// {
		// 	$attributeTranslations = [];
		// }
		
		// unset($params['translations']);

		if(isset($params['data']))
		{
			$params['data'] = json_encode($params['data']);
		}

		$params['created_at'] = $params['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		$table = $this->availableFieldTypes[$params['field_type']]['table'];

		$params['table'] = $table;

		// insert attribute in database
		$attribute_id = $this->db->table('attributes')->insertGetId($params);

		// // populate translations with attribute data
		// for($i = 0; $i < count($attributeTranslations); $i++)
		// {
		// 	$attributeTranslations[$i]['attribute_id'] = $attribute_id;
		// }

		// if(!empty($attributeTranslations))
		// {
		// 	// insert new translations in database
		// 	$this->db->table('attribute_translations')->insert($attributeTranslations);
		// }

		return $this->fetch($attribute_id);
	}

	/**
	 * Validate attribute params and update it in database
	 * 
	 * @param array $params = attribute params (field_type, is_required...)
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function updateAttribute($id, $params, $attribute)
	{

		// // separate params array in attribute and attribute translations params
		// if( ! empty($params['translations']) )
		// {
		// 	$attributeTranslations = $params['translations'];
		// }
		// else
		// {
		// 	$attributeTranslations = [];
		// }

		// unset($params['translations']);

		// // validate attribute translations
		// for( $i = 0; $i < count($attributeTranslations); $i++ )
		// {
		// 	$attributeTranslations[$i]['attribute_id'] = $id;
		// }

		if( isset($params['data']) )
		{
			$params['data'] = json_encode($params['data']);
		}

		

		$attributeParams = json_decode(json_encode($attribute), true);
		$attributeParams = array_merge($attributeParams, $params);

		unset($attributeParams['id']);

		$attributeParams['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		$this->db->table('attributes')->where('id', '=', $id)->update($attributeParams);

		// // delete all existing translations from database
		// $this->db->table('attribute_translations')->where('attribute_id', '=', $id)->delete();
		
		// if( ! empty($attributeTranslations) )
		// {
		// 	// insert new translations in database
		// 	$this->db->table('attribute_translations')->insert($attributeTranslations);
		// }

		return $id;
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
	protected function updateOptions(array $options, $attribute)
	{
		// fabricate attribute handler for this attribute type 
		// (needed for options update)
		$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);

		$success = true;
		$optionIDs = [];
		foreach ($options as $option)
		{
			$oldOption = null;
			// if option is alreay in database get its old version
			if( ! empty($option['id']) )
			{
				foreach ($attribute->options as $opt)
				{
					if($opt->id == $option['id'])
					{
						$oldOption = $opt;
						$optionIDs[] = $option['id'];
					}
				}

			}

			if( ! $oldOption )
			{
				$oldOption = null;
			}

			$this->updateOption($option, $oldOption);
		}

		// if there are old options and there were no error
		// delete options that don't exist anymore.
		$optionsForDelete = [];
		if( ! empty($attribute->options) )
		{
			foreach ($attribute->options as $option)
			{
				if( ! in_array($option->id, $optionIDs) )
				{
					if( ! empty($fieldHandler) )
					{
						$fieldHandler->deleteByOption($option);
					}

					$this->db->table('attribute_options')
							 ->where('id', '=', $option->id)
							 ->delete();
				}
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
			$optionParams = json_decode(json_encode($oldOption), true);
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


// ----------------------------------------------------------------------------------------------
// GETTERS
// ----------------------------------------------------------------------------------------------
// 
// 
// 

	/**
	 * Get attribute by ID
	 * 
	 * @param int $id - ID of attribute to be fetched
	 * 
	 * @return array
	 */
	protected function _fetch($id, $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;
		
		// if(Trunk::has($params, 'attribute'))
		// {
		// 	$attribute = Trunk::get($id, 'attribute');
		// 	$attribute->clearIncluded();
		// 	$attribute->load($include);
		// 	$meta = ['id' => $id, 'include' => $include];
		// 	$attribute->setMeta($meta);
		// 	return $attribute;
		// }

		$attribute = $this->db->table('attributes')->find($id);
		
		if( ! $attribute )
		{
			throw new NotFoundException(['There is no attribute with that ID.']);
		}

		$options = $this->db->table('attribute_options')
							->where('attribute_id', '=', $id)
							->orderBy('sort_order')
							->orderBy('id')
							->get();
		
		$attribute->options = [];
		foreach ($options as $option) 
		{
			if($option->default)
			{
				$attribute->default_value = $option->value;
			}
			
			$attribute->options[] = $option;
		}

		if( isset($attribute->data) )
		{
			$attribute->data = json_decode($attribute->data);
		}
		else
		{
			$attribute->data = null;
		}

		unset($attribute->table);

		// $translations = $this->db->table('attribute_translations')
		// 						 ->where('attribute_id', '=', $id)
		// 						 ->get();

		// $attribute->translations = $translations;
		
		$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
		$attribute = $fieldHandler->formatAttribute($attribute);
		$attribute->type = 'attribute';

		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$attribute->created_at = Carbon::parse($attribute->created_at)->tz($timezone);
		$attribute->updated_at = Carbon::parse($attribute->updated_at)->tz($timezone);

		$result = new Model($attribute);
		
		$result->setParams($params);
		$meta = ['id' => $id, 'include' => $include];
		$result->setMeta($meta);
		$result->load($include);
		return $result;
	}

	/**
	 * Get attributes
	 * 
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;

		if(Trunk::has($params, 'attribute'))
		{
			$attributes = Trunk::get($params, 'attribute');
			$attributes->clearIncluded();
			$attributes->load($include);
			$meta = [
				'include' => $include
			];
			$attributes->setMeta($meta);
			return $attributes;
		}

		$query = $this->db->table('attributes');

		$query = $this->parseFilters($query, $filter);

		$total = $query->count();

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$attributes = $query->get();

		if( ! $attributes )
		{
			$attributes = [];	
		}

		$ids = [];

		foreach ($attributes as &$attribute) 
		{
			$fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
			$attribute = $fieldHandler->formatAttribute($attribute);

			$ids[] = $attribute->id;
			$attribute->options = [];
			unset($attribute->table);
			$attribute->type = 'attribute';
			$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
			$attribute->created_at = Carbon::parse($attribute->created_at)->tz($timezone);
			$attribute->updated_at = Carbon::parse($attribute->updated_at)->tz($timezone);

			if( isset($attribute->data) )
			{
				$attribute->data = json_decode($attribute->data);
			}
			else
			{
				$attribute->data = null;
			}
			// $attribute->translations = [];
		}

		$options = [];
		
		if( ! empty($ids) )
		{
			$options = $this->db->table('attribute_options')
								->whereIn('attribute_id', $ids)
								->orderBy('sort_order')
								->get();
		}
		
		
		foreach ($options as $option) 
		{
			foreach ($attributes as &$attribute)
			{
				if($attribute->id == $option->attribute_id)
				{
					if($option->default)
					{
						$attribute->default_value = $option->value;
					}

					$attribute->options[] = $option;
					break;
				}
			}
		}

		$result = new Collection($attributes);
		
		$result->setParams($params);

		$meta = [
			'count' => count($attributes), 
			'offset' => $offset, 
			'limit' => $limit, 
			'total' => $total, 
			'filter' => $filter, 
			'sort' => $sort, 
			'include' => $include
		];
		$result->setMeta($meta);

		$result->load($include);
		
		return $result;
	}

}