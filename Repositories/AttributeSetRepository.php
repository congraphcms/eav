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

use Cookbook\Contracts\Eav\AttributeHandlerFactoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Core\Exceptions\BadRequestException;
use Cookbook\Core\Exceptions\Exception;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Repositories\AbstractRepository;
use Cookbook\Core\Repositories\Collection;
use Cookbook\Core\Repositories\Model;
use Cookbook\Core\Facades\Trunk;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;
use stdClass;


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

		$attributeSet = $this->fetch($attributeSetId);

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
		
		Trunk::forgetType('attribute-set');
		$attributeSet = $this->fetch($attributeSetId);

		return $attributeSet;
	}


	/**
	 * Delete attribute set and its set attributes
	 * 
	 * @param int $id - ID of attribute set that will be deleted
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException, Exception
	 */
	protected function _delete($id)
	{
		// get the attribute set
		$attributeSet = $this->fetch($id);
		if(!$attributeSet)
		{
			throw new NotFoundException(['There is no attribute with that ID.']);
		}

		$this->deleteSetAttributes($id);

		// delete the attribute set
		$this->db->table('attribute_sets')->where('id', '=', $id)->delete();

		Trunk::forgetType('attribute-set');
		return $attributeSet;
	}

	/**
	 * Delete attribute sets vy entity type ID and its set attributes
	 * 
	 * @param object $entityType
	 * 
	 * @return void
	 */
	public function deleteByEntityType($entityType)
	{
		// get the sets
		$setIDs = $this->db ->table('attribute_sets')
							->where('entity_type_id', '=', $entityType->id)
							->lists('id');

		if( ! empty($setIDs) )
		{
			Trunk::forgetType('attribute-set');
			$this->deleteSetAttributes($setIDs);

			// delete the attribute set
			$this->db->table('attribute_sets')->where('entity_type_id', '=', $entityType->id)->delete();
		}
	}

	/**
	 * Delete set attributes by attribute
	 * 
	 * @param int $entityTypeID - ID of entity type
	 * 
	 * @return int
	 * 
	 * @throws InvalidArgumentException, Exception
	 */
	public function deleteByAttribute($attribute)
	{
		$this->deleteSetAttributesByAttribute($attribute->id);
		Trunk::forgetType('attribute-set');
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
		$params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');
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
	 * Delete set attributes by set
	 * 
	 * @param array 	$attributeSetIds
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function deleteSetAttributes($attributeSetIds)
	{
		if( ! is_array($attributeSetIds) )
		{
			$attributeSetIds = [$attributeSetIds];
		}

		$this->db->table('set_attributes')
				 ->whereIn('attribute_set_id', $attributeSetIds)
				 ->delete();
	}

	/**
	 * Delete set attributes by attributes
	 * 
	 * @param array 	$attributeIds
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function deleteSetAttributesByAttribute($attributeIds)
	{
		if( ! is_array($attributeIds) )
		{
			$attributeIds = [$attributeIds];
		}

		$this->db->table('set_attributes')
				 ->whereIn('attribute_id', $attributeIds)
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
	protected function _fetch($id, $include = [])
	{
		$params = func_get_args();
		
		if(Trunk::has($params, 'attribute-set'))
		{
			$attributeSet = Trunk::get($id, 'attribute-set');
			$attributeSet->clearIncluded();
			$attributeSet->load($include);
			$meta = ['id' => $id, 'include' => $include];
			$attributeSet->setMeta($meta);
			return $attributeSet;
		}

		$attributeSet = $this->db->table('attribute_sets')->find($id);

		if( empty($attributeSet) )
		{
			throw new NotFoundException(['There is no attribute set with that ID.']);
		}

		$setAttributes = $this->db->table('set_attributes')
								  ->select($this->db->raw('attribute_id as id, "attribute" as type'))
								  ->where('attribute_set_id', '=', $id)
								  ->orderBy('sort_order')
								  ->get();

		$attributeSet->attributes = $setAttributes;

		$attributeSet->type = 'attribute-set';

		$attributeSet->entity_type = new stdClass();

		$attributeSet->entity_type->id = $attributeSet->entity_type_id;

		$attributeSet->entity_type->type = 'entity-type';

		$result = new Model($attributeSet);
		
		$result->setParams($params);
		$meta = ['id' => $id, 'include' => $include];
		$result->setMeta($meta);
		$result->load($include);
		return $result;
	}

	/**
	 * Get attribute sets
	 * 
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [])
	{

		$params = func_get_args();

		if(Trunk::has($params, 'attribute-set'))
		{
			$attributeSets = Trunk::get($params, 'attribute-set');
			$attributeSets->clearIncluded();
			$attributeSets->load($include);
			$meta = [
				'include' => $include
			];
			$attributeSets->setMeta($meta);
			return $attributeSets;
		}


		$query = $this->db->table('attribute_sets');

		$query = $this->parseFilters($query, $filter);

		$total = $query->count();

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$attributeSets = $query->get();

		if( ! $attributeSets )
		{
			$attributeSets = [];	
		}

		$ids = [];

		foreach ($attributeSets as &$attributeSet) 
		{
			$ids[] = $attributeSet->id;
			$attributeSet->attributes = [];
			$attributeSet->type = 'attribute-set';
			$attributeSet->entity_type = new stdClass();
			$attributeSet->entity_type->id = $attributeSet->entity_type_id;
			$attributeSet->entity_type->type = 'entity-type';
		}

		$setAttributes = [];

		if( ! empty($ids) )
		{
			$setAttributes = $this->db->table('set_attributes')
								  ->select('attribute_id', 'attribute_set_id')
								  ->whereIn('attribute_set_id', $ids)
								  ->orderBy('sort_order')
								  ->get();
		}
		
		
		foreach ($setAttributes as $setAttribute) 
		{
			foreach ($attributeSets as &$attributeSet)
			{
				if($attributeSet->id == $setAttribute->attribute_set_id)
				{
					$attribute = new stdClass();
					$attribute->id = $setAttribute->attribute_id;
					$attribute->type = 'attribute';

					$attributeSet->attributes[] = $attribute;
					break;
				}
			}
		}
		
		$result = new Collection($attributeSets);
		
		$result->setParams($params);

		$meta = [
			'count' => count($attributeSets), 
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