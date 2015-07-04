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

/**
 * EntityTypeRepository class
 * 
 * Repository for entity type database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeRepository extends AbstractRepository
{
	use ValidatorTrait;

	/**
	 * Repository for handling attribute sets
	 * 
	 * @var Cookbook\Eav\Repositories\AttributeSetRepository
	 */
	protected $attributeSetRepository;

	/**
	 * Array used to create rules for validation for INSERT entity type
	 * 
	 * @var array
	 */
	protected $entityTypeInsertParamRules;
	
	/**
	 * Array used to create rules for validation for UPDATE entity type
	 * 
	 * @var array
	 */
	protected $entityTypeUpdateParamRules;

	/**
	 * Create new EntityTypeRepository
	 * 
	 * @param Illuminate\Database\Connection $db
	 * 
	 * @return void
	 */
	public function __construct(Connection $db, AttributeSetRepository $attributeSetRepository)
	{

		// AbstractRepository constructor
		parent::__construct($db);

		// Inject dependencies
		$this->attributeSetRepository = $attributeSetRepository;

		// set default key for errors
		$this->setErrorKey('entity_type.errors');

		// Validation Rules for entity type insert
		$this->entityTypeInsertParamRules = array(
			'slug'						=> 'required|unique:entity_types,slug|between:2,250',
			'name'						=> 'required|between:3,250',
			'plural_name'				=> 'max:250',
			'table'						=> 'required|between:3,250',
			'parent_type'				=> 'required|max:50',
			'archive_parent'			=> 'required|boolean',
			'multiple_sets'				=> 'required|boolean',
			'default_attribute_set_id'	=> 'required|integer',
		);

		// Validation Rules for entity type update
		$this->entityTypeUpdateParamRules = array(
			'id'						=> 'required|integer|exists:entity_types,id',
			'slug'						=> 'required|unique:entity_types,slug|between:2,250',
			'name'						=> 'required|between:3,250',
			'plural_name'				=> 'max:250',
			'archive_parent'			=> 'required|boolean',
			'multiple_sets'				=> 'required|boolean',
			'default_attribute_set_id'	=> 'required|integer',
		);
	}

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

		try
		{
			
			$this->validateParams($model, $this->entityTypeInsertParamRules);

			if( $this->hasErrors() )
			{
				return false;
			}

			// insert entity type in database
			$entityTypeID = $this->db->table('entity_types')->insertGetId($model);

			return $entityTypeID;

		}
		catch(\Exception $e)
		{
			$this->addErrors('Failed to insert entity type.');
			return false;
		}
		
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
	protected function _update($model)
	{

		try
		{

			// validate entity type params
			$this->validateParams($model, $this->entityTypeUpdateParamRules);

		
			if( $this->hasErrors() )
			{
				$this->addErrors('Invalid params.');
				return false;
			}

			// get entity type from database
			$entityType = $this->db->table('entity_types')->find($model['id']);

			if( ! $entityType )
			{
				$this->addErrors('Invalid entity type ID.');
				return false;
			}

			$entityTypeID = $entityType->id;
		
			$entityTypeParams = json_decode(json_encode($entityType), true);
			$entityTypeParams = array_merge($entityTypeParams, $model);

			unset($entityTypeParams['id']);

			$entityTypeParams['updated_at'] = date('Y-m-d H:i:s');

			$this->db 	->table('entity_types')
						->where('id', '=', $entityTypeID)
						->update($entityTypeParams);



			return $entityTypeID;

		}
		catch(\Exception $e)
		{
			$this->addErrors('Failed to update entity type.');
			return false;
		}
		
	}

	/**
	 * Delete entity type
	 * 
	 * @param integer | array $ids
	 * 
	 * @return boolean
	 */
	protected function _delete($ids)
	{

		if( ! is_array($ids) )
		{
			$ids = array($ids);
		}

		try
		{
			// get the types
			$types = $this->db 	->table('entity_types')
								->whereIn('id', $ids)
								->get();
			
			if( empty($types) )
			{
				$this->addErrors('Invalid entity type ID.');
				return false;
			}

			// get the sets
			$setIDs = $this->db ->table('attribute_sets')
								->whereIn('entity_type_id', $ids)
								->lists('id');
			
			if( ! empty($setIDs) )
			{

				if( ! $this->attributeSetRepository->delete($setIDs) )
				{
					$this->addErrors('Couldn\'t delete attribute sets.');
					return false;
				}
			}
			

			$this->db 	->table('entity_types')
						->whereIn('id', $ids)
						->delete();

			return true;

		}
		catch(\Exception $e)
		{
			$this->addErrors('There was an error, couldn\'t delete entity type');
			return false;
		}
	}

	/**
	 * Check if there is an entity type with that slug
	 * 
	 * @param array $params - (entity_type_id, value)
	 * 
	 * @return boolean
	 */
	public function uniqueSlug($params)
	{

		if( empty( $params['slug'] ) )
		{
			$this->addErrors('Invalid params'));
			return false;
		}

		if( empty( $params['entity_type_id'] ) )
		{
			$entityTypeId = 0;
		}
		else
		{
			$entityTypeId = intval( $params['entity_type_id'] );
		}
		
		$entityType = $this->db ->table('entity_types')
								->where('slug', '=', $params['slug'])
								->where('id', '!=', $entityTypeId)
								->first();

		if( ! $entityType)
		{
			return true;
		}
		
		return false;
	}


	// ----------------------------------------------------------------------------------------------
	// GETTERS
	// ----------------------------------------------------------------------------------------------



	/**
	 * Get all entity types
	 * using $entityTypeModel
	 * 
	 * @param array $with - optional relations to be fetched with entity types
	 * 
	 * @return Model
	 */
	public function fetchTypes($with = array()){
		return $this->entityTypeModel->with($with)->get();
	}

	/**
	 * Get entity type by slug
	 * using $entityTypeModel
	 * 
	 * @param array $with - optional relations to be fetched with entity types
	 * 
	 * @return Model
	 */
	public function fetchTypeBySlug($slug, $with = array()){
		return $this->entityTypeModel
					->where('slug', '=', $slug)
					->with($with)
					->first();
	}

	/**
	 * Get entity type by ID
	 * using $entityTypeModel
	 * 
	 * @param array $with - optional relations to be fetched with entity types
	 * 
	 * @return Model
	 */
	public function fetchTypeById($id, $with = array()){
		return $this->entityTypeModel
					->where('id', '=', $id)
					->with($with)
					->first();
	}

}