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
use Cookbook\Core\Repositories\AbstractRepository;
use Cookbook\Core\Repositories\UsesCache;

use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;


/**
 * EntityTypeRepository class
 * 
 * Repository for entity type database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 * @uses   		Cookbook\Contracts\Eav\AttributeSetRepositoryContract
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeRepository extends AbstractRepository implements EntityTypeRepositoryContract//, UsesCache
{

	/**
	 * Create new EntityTypeRepository
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



// ----------------------------------------------------------------------------------------------
// CRUD
// ----------------------------------------------------------------------------------------------
// 
// 
// 
	/**
	 * Create new entity type
	 * 
	 * @param array $model - entity type params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	protected function _create($model)
	{

		$model['created_at'] = $model['updated_at'] = date('Y-m-d H:i:s');
		
		// insert entity type in database
		$entityTypeID = $this->db->table('entity_types')->insertGetId($model);

		if( ! $entityTypeID )
		{
			throw new \Exception('Failed to insert entity type.');
		}

		$entityType = $this->fetch($entityTypeID);

		return $entityType;
		
	}

	/**
	 * Update entity type
	 * 
	 * @param array $model - entity type params
	 * 
	 * @return mixed
	 * 
	 * @throws Exception
	 */
	protected function _update($id, $model)
	{

		// find entity type with that ID
		$entityType = $this->db->table('entity_types')->find($id);

		if( ! $entityType )
		{
			throw new NotFoundException(['There is no entity type with that ID.']);
		}

		$entityTypeParams = json_decode(json_encode($entityType), true);
		$entityTypeParams = array_merge($entityTypeParams, $model);

		unset($entityTypeParams['id']);

		$entityTypeParams['updated_at'] = date('Y-m-d H:i:s');

		$this->db->table('entity_types')->where('id', '=', $id)->update($entityTypeParams);

		return $this->fetch($id);
		
	}

	/**
	 * Delete entity type
	 * 
	 * @param integer | array $ids
	 * 
	 * @return boolean
	 */
	protected function _delete($id)
	{

		
		// get type
		$entityType = $this->db->table('entity_types')->find($id);
		
		if( empty($entityType) )
		{
			throw new NotFoundException(['There is no entity type with that ID.']);
		}
		
		$this->db 	->table('entity_types')
					->where('id', '=', $id)
					->delete();

		return $entityType;
	}




	// ----------------------------------------------------------------------------------------------
	// GETTERS
	// ----------------------------------------------------------------------------------------------



	/**
	 * Get entity type by ID
	 * 
	 * @param int $id - ID of entity type to be fetched
	 * 
	 * @return stdClass
	 */
	protected function _fetch($id){
		$entityType = $this->db->table('entity_types')->find($id);

		if( empty($entityType) )
		{
			throw new NotFoundException(['There is no entity type with that ID.']);
		}

		$attributeSets = $this->db->table('attribute_sets')
								  ->select($this->db->raw('id as id, "attribute-set" as type'))
								  ->where('entity_type_id', '=', $id)
								  ->get();

		$entityType->attribute_sets = $attributeSets;

		$entityType->type = 'entity-type';

		return $entityType;
	}


	/**
	 * Get attribute sets
	 * 
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [])
	{
		$query = $this->db->table('entity_types');

		$query = $this->parseFilters($query, $filter);

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$entityTypes = $query->get();

		if( ! $entityTypes )
		{
			return [];	
		}

		$ids = [];

		foreach ($entityTypes as &$entityType) 
		{
			$ids[] = $entityType->id;
			$entityType->attribute_sets = [];
			$entityType->type = 'entity-type';
		}

		$attributeSets = $this->db->table('attribute_sets')
								  ->select($this->db->raw('id as id, entity_type_id as entity_type_id, "attribute-set" as type'))
								  ->whereIn('entity_type_id', $ids)
								  ->get();
		
		foreach ($attributeSets as $attributeSet) 
		{
			foreach ($entityTypes as &$entityType)
			{
				if($entityType->id == $attributeSet->entity_type_id)
				{
					unset($attributeSet->entity_type_id);
					$entityType->attribute_sets[] = $attributeSet;
					break;
				}
			}
		}
		
		return $entityTypes;
	}







	// /**
	//  * Check if there is an entity type with that slug
	//  * 
	//  * @param array $params - (entity_type_id, value)
	//  * 
	//  * @return boolean
	//  */
	// public function uniqueSlug($params)
	// {

	// 	if( empty( $params['slug'] ) )
	// 	{
	// 		$this->addErrors('Invalid params'));
	// 		return false;
	// 	}

	// 	if( empty( $params['entity_type_id'] ) )
	// 	{
	// 		$entityTypeId = 0;
	// 	}
	// 	else
	// 	{
	// 		$entityTypeId = intval( $params['entity_type_id'] );
	// 	}
		
	// 	$entityType = $this->db ->table('entity_types')
	// 							->where('slug', '=', $params['slug'])
	// 							->where('id', '!=', $entityTypeId)
	// 							->first();

	// 	if( ! $entityType)
	// 	{
	// 		return true;
	// 	}
		
	// 	return false;
	// }
}