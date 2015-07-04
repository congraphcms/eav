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

use Cookbook\Core\Exceptions\Exception;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Exceptions\BadRequestException;
use Cookbook\Core\Repositories\AbstractRepository;

use Cookbook\Contracts\Eav\AttributeHandlerFactoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Eav\Managers\AttributeManager;


/**
 * AttributeSetRepository class
 * 
 * Repository for attribute set database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 * @uses   		Cookbook\Contracts\Eav\AttributeHandlerFactoryContract
 * @uses   		Cookbook\Eav\Managers\AttributeManager
 * @uses   		Cookbook\Eav\Repositories\EntityRepository
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetRepository extends AbstractRepository implements AttributeSetRepositoryContract
{
	/**
	 * Create new AttributeSetRepository
	 * 
	 * @param Illuminate\Database\Connection $db
	 * 
	 * @return void
	 */
	public function __construct(Connection $db)
	{

		// AbstractRepository constructor
		parent::__construct($db);

	}


	/**
	 * Create new attribute set
	 * 
	 * @param array $model - attribute set params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	protected function _create($model)
	{
		$attributes = [];
		if( isset($model['attributes']) )
		{
			$attributes = $model['attributes'];
			unset($model['attributes']);
		}

		// insert attribute set
		$attributeSetId = $this->insertAttributeSet($model);

		if(!$attributeSetId)
		{
			throw new \Exception('Failed to insert attribute set.');
		}

		// set relation to attribute set in all groups
		for($i = 0; $i < count($attributes); $i++)
		{
			$attributes[$i]['attribute_id'] = $attributes[$i]['id'];
			unset($attributes[$i]['id']);
			$attributes[$i]['attribute_set_id'] = $attributeSetId;
			$attributes[$i]['sort_order'] = $i;
		}

		$this->insertSetAttributes($attributes);

		$attributeSet = $this->fetchById($attributeSetId, true);

		return $attributeSet;
		
	}

	/**
	 * Update attribute set and its groups
	 * 
	 * @param array $model - attribute set params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	protected function _update($id, $model)
	{
		// extract attributes from model
		$attributes = null;
		if( isset($model['attributes']) )
		{
			$attributes = $model['attributes'];
			unset($model['attributes']);
		}

		// insert attribute set
		$attributeSetId = $this->updateAttributeSet($id, $model);

		if(!$attributeSetId)
		{
			throw new \Exception('Failed to update attribute set.');
		}

		if( $attributes !== null)
		{
			// set relation to attribute set in all attributes
			for($i = 0; $i < count($attributes); $i++)
			{
				$attributes[$i]['attribute_id'] = $attributes[$i]['id'];
				unset($attributes[$i]['id']);
				$attributes[$i]['attribute_set_id'] = $attributeSetId;
				$attributes[$i]['sort_order'] = $i;
			}

			$this->deleteSetAttributes($attributeSetId);
			$this->insertSetAttributes($attributes);
		}
		

		$attributeSet = $this->fetchById($attributeSetId, [], true);

		return $attributeSet;
	}


	/**
	 * Delete attribute set and its groups
	 * 
	 * @param integer $id - ID of attribute set that will be deleted
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException, Exception
	 */
	protected function _delete($id)
	{
		// get the attribute set
		$attributeSet = $this->db->table('attribute_sets')->find($id);
		if(!$attributeSet)
		{
			throw new NotFoundException(['There is no attribute with that ID.']);
		}

		$this->deleteSetAttributes($id);

		// delete the attribute set
		$this->db ->table('attribute_sets')->where('id', '=', $id)->delete();

		return $id;
	}


	/**
	 * Insert attribute set in database
	 * 
	 * @param array $params = attribute set params
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function insertAttributeSet($params)
	{

		// insert attribute set in database
		$attributeSetId = $this->db->table('attribute_sets')->insertGetId($params);

		return $attributeSetId;
	}


	/**
	 * Validate attribute set params and update it in database
	 * using $attributeSetModel
	 *
	 * @param int 	$id
	 * @param array $params = attribute set params
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function updateAttributeSet($id, $params)
	{

		// find attribute set with that ID
		$attributeSet = $this->db->table('attribute_sets')->find($id);

		if( ! $attributeSet )
		{
			throw new NotFoundException(['There is no attribute set with that ID.']);
		}
		
		$attributeSetParams = json_decode(json_encode($attributeSet), true);
		$attributeSetParams = array_merge($attributeSetParams, $params);

		unset($attributeSetParams['id']);

		$attributeSetParams['updated_at'] = date('Y-m-d H:i:s');

		// update attribute set in database
		$this->db->table('attribute_sets')->where('id', '=', $id)->update($attributeSetParams);

		return $attributeSet->id;
	}


	/**
	 * Insert set attributes
	 * 
	 * @param array 	$attributes
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function insertSetAttributes($attributes)
	{
		if(!empty($attributes))
		{
			// insert set attributes in database
			$this->db->table('set_attributes')->insert( $attributes );
		}
	}

	/**
	 * Update set attributes
	 * 
	 * @param array 	$attributes
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function deleteSetAttributes($attributeSetId)
	{
		$this->db->table('set_attributes')
				 ->where('attribute_set_id', '=', $attributeSetId)
				 ->delete();
	}


	// ----------------------------------------------------------------------------------------------
	// GETTERS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Get attribute set by ID
	 * 
	 * @param int $id - ID of attribute set to be fetched
	 * @param array $with - optional relations to be fetched with attribute set
	 * 
	 * @return Model
	 */
	protected function _fetchById($id){
		$attributeSet = $this->db->table('attribute_sets')->find($id);

		if( empty($attributeSet) )
		{
			throw new NotFoundException(['There is no attribute set with that ID.']);
		}

		$setAttributes = $this->db->table('set_attributes')
								  ->select($this->db->raw('attribute_id as id, "attributes" as type'))
								  ->where('attribute_set_id', '=', $id)
								  ->orderBy('sort_order')
								  ->get();

		$attributeSet->attributes = $setAttributes;

		return $attributeSet;
	}

	/**
	 * Get attribute sets
	 * 
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [])
	{
		$query = $this->db->table('attribute_sets');

		$query = $this->parseFilters($query, $filter);

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$attributeSets = $query->get();

		if( ! $attributeSets )
		{
			return [];	
		}

		$ids = [];

		foreach ($attributeSets as &$attributeSet) 
		{
			$ids[] = $attributeSet->id;
			$attributeSet->attributes = [];
		}

		$setAttributes = $this->db->table('set_attributes')
								  ->select('attribute_id')
								  ->whereIn('attribute_set_id', $ids)
								  ->orderBy('sort_order')
								  ->get();
		
		foreach ($setAttributes as $setAttribute) 
		{
			foreach ($attributeSets as &$attributeSet)
			{
				if($attributeSet->id == $setAttribute->attribute_set_id)
				{
					$attributeSet->attributes[] = $setAttribute;
					break;
				}
			}
		}
		
		return $attributes;
	}

	

	// /**
	//  * Get all attribute sets
	//  * using $attributeSetModel
	//  * 
	//  * @param array $with - optional relations to be fetched with attribute sets
	//  * 
	//  * @return Collection
	//  */
	// public function fetchAll($with = array()){
	// 	return $this->attributeSetModel->with($with)->get();
	// }

	// /**
	//  * Get all attribute sets belonging to type
	//  * using $attributeSetModel
	//  *
	//  * @param string $type - entity type slug 
	//  * @param array $with - optional relations to be fetched with attribute sets
	//  * 
	//  * @return Collection
	//  */
	// public function fetchByType($type, $with = array()){
	// 	return $this->attributeSetModel
	// 				->with($with)
	// 				->whereHas('entityType', function($q) use ($type){
	// 					$q->where('slug', '=', $type);
	// 				})->get();
	// }

	// /**
	//  * Check if there is an attribute set with that slug
	//  * 
	//  * @param array $params - (attribute_set_id, value)
	//  * 
	//  * @return boolean
	//  */
	// public function uniqueSlug($params){

	// 	if(empty($params['slug'])){
	// 		$this->addErrors(
	// 			array('Invalid params')
	// 		);
	// 		return false;
	// 	}

	// 	if(empty($params['attribute_set_id'])){
	// 		$attributeSetId = 0;
	// 	}else{
	// 		$attributeSetId = intval($params['attribute_set_id']);
	// 	}
		
	// 	$attributeSet = $this->attributeSetModel->where('slug', '=', $params['slug'])->where('id', '!=', $attributeSetId)->first();

	// 	if($attributeSet){
	// 		return false;
	// 	}else{
	// 		return true;
	// 	}
	// }
}