<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Repositories;


use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;

use Congraph\Core\Exceptions\Exception;
use Congraph\Core\Exceptions\NotFoundException;
use Congraph\Core\Repositories\AbstractRepository;
use Congraph\Core\Repositories\UsesCache;
use Congraph\Core\Facades\Trunk;
use Congraph\Core\Repositories\Collection;
use Congraph\Core\Repositories\Model;

use Congraph\Contracts\Eav\EntityTypeRepositoryContract;


use Illuminate\Support\Facades\Cache;

use Carbon\Carbon;

use stdClass;


/**
 * EntityTypeRepository class
 * 
 * Repository for entity type database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Congraph\Core\Repository\AbstractRepository
 * @uses   		Congraph\Contracts\Eav\AttributeSetRepositoryContract
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
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

		$model['created_at'] = $model['updated_at'] = Carbon::now('UTC')->toDateTimeString();
		
		// insert entity type in database
		$entityTypeID = $this->db->table('entity_types')->insertGetId($model);

		if( ! $entityTypeID )
		{
			throw new \Exception('Failed to insert entity type.');
		}

		$entityType = $this->fetch($entityTypeID);

		Cache::forget('entityTypes');

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

		$entityTypeParams['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		$this->db->table('entity_types')->where('id', '=', $id)->update($entityTypeParams);

		Trunk::forgetType('entity-type');
		Cache::forget('entityTypes');

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

		Trunk::forgetType('entity-type');
		Cache::forget('entityTypes');

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
	protected function _fetch($id, $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;
		
		if(Trunk::has($params, 'entity-type'))
		{
			$entityType = Trunk::get($id, 'entity-type');
			$entityType->clearIncluded();
			$entityType->load($include);
			$meta = ['id' => $id, 'include' => $include];
			$entityType->setMeta($meta);
			return $entityType;
		}

		if(!is_numeric($id))
		{
			return $this->fetchByCode($id, $include);
		}

		$entityType = $this->db->table('entity_types')->find($id);

		if( empty($entityType) )
		{
			throw new NotFoundException(['There is no entity type with that ID.']);
		}

		$attributeSets = $this->db->table('attribute_sets')
								  ->select($this->db->raw('id as id, "attribute-set" as type'))
								  ->where('entity_type_id', '=', $id)
								  ->get();

		$entityType->attribute_sets = $attributeSets->toArray();

		$workflow = new stdClass();
		$workflow->id = $entityType->workflow_id;
		$workflow->type = 'workflow';
		$point = new stdClass();
		$point->id = $entityType->default_point_id;
		$point->type = 'workflow-point';
		$entityType->workflow = $workflow;
		$entityType->default_point = $point;
		

		$entityType->type = 'entity-type';
		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$entityType->created_at = Carbon::parse($entityType->created_at)->tz($timezone);
		$entityType->updated_at = Carbon::parse($entityType->updated_at)->tz($timezone);


		$result = new Model($entityType);
		
		$result->setParams($params);
		$meta = ['id' => $id, 'include' => $include];
		$result->setMeta($meta);
		$result->load($include);

		return $result;
	}

	/**
	 * Get entity type by Code
	 * 
	 * @param int $code - Unique Code of entity type to be fetched
	 * 
	 * @return stdClass
	 */
	protected function fetchByCode($code, $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;
		
		if(Trunk::has($params, 'entity-type'))
		{
			$entityType = Trunk::get($code, 'entity-type');
			$entityType->clearIncluded();
			$entityType->load($include);
			$meta = ['code' => $code, 'include' => $include];
			$entityType->setMeta($meta);
			return $entityType;
		}

		$entityType = $this->db->table('entity_types')->where('code', '=', $code)->first();

		if( empty($entityType) )
		{
			throw new NotFoundException(['There is no entity type with that Code.']);
		}

		$attributeSets = $this->db->table('attribute_sets')
								  ->select($this->db->raw('id as id, "attribute-set" as type'))
								  ->where('entity_type_id', '=', $entityType->id)
								  ->get();

		$entityType->attribute_sets = $attributeSets->toArray();

		$workflow = new stdClass();
		$workflow->id = $entityType->workflow_id;
		$workflow->type = 'workflow';
		$point = new stdClass();
		$point->id = $entityType->default_point_id;
		$point->type = 'workflow-point';
		$entityType->workflow = $workflow;
		$entityType->default_point = $point;
		

		$entityType->type = 'entity-type';
		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$entityType->created_at = Carbon::parse($entityType->created_at)->tz($timezone);
		$entityType->updated_at = Carbon::parse($entityType->updated_at)->tz($timezone);


		$result = new Model($entityType);
		
		$result->setParams($params);
		$meta = ['code' => $code, 'include' => $include];
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
		$params['function'] = __METHOD__;

		if(Trunk::has($params, 'entity-type'))
		{
			$entityTypes = Trunk::get($params, 'entity-type');
			$entityTypes->clearIncluded();
			$entityTypes->load($include);
			$meta = [
				'include' => $include
			];
			$entityTypes->setMeta($meta);
			return $entityTypes;
		}

		$query = $this->db->table('entity_types');

		$query = $this->parseFilters($query, $filter);

		$total = $query->count();

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$entityTypes = $query->get();

		$entityTypes = $entityTypes->toArray();

		if( ! $entityTypes )
		{
			$entityTypes = [];
		}

		$ids = [];

		foreach ($entityTypes as &$entityType) 
		{
			$ids[] = $entityType->id;
			$entityType->attribute_sets = [];
			$entityType->type = 'entity-type';
			$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
			$entityType->created_at = Carbon::parse($entityType->created_at)->tz($timezone);
			$entityType->updated_at = Carbon::parse($entityType->updated_at)->tz($timezone);

			$workflow = new stdClass();
			$workflow->id = $entityType->workflow_id;
			$workflow->type = 'workflow';
			$point = new stdClass();
			$point->id = $entityType->default_point_id;
			$point->type = 'workflow-point';
			$entityType->workflow = $workflow;
			$entityType->default_point = $point;
		}

		$attributeSets = [];

		if( ! empty($ids) )
		{
			$attributeSets = $this->db->table('attribute_sets')
								  ->select($this->db->raw('id as id, entity_type_id as entity_type_id, "attribute-set" as type'))
								  ->whereIn('entity_type_id', $ids)
								  ->get();
		}
		
		
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
		
		$result = new Collection($entityTypes);
		
		$result->setParams($params);

		$meta = [
			'count' => count($entityTypes), 
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