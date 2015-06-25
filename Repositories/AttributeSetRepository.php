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

use Illuminate\Database\Connection;

use Cookbook\Core\Repository\AbstractRepository;
use Cookbook\Core\Traits\ValidatorTrait;

use Cookbook\Contracts\EAV\AttributeHandlerFactoryContract;
use Cookbook\EAV\Managers\AttributeManager;


/**
 * AttributeSetRepository class
 * 
 * Repository for attribute set database queries
 * 
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 * @uses   		Cookbook\Contracts\EAV\AttributeHandlerFactoryContract
 * @uses   		Cookbook\EAV\Managers\AttributeManager
 * @uses   		Cookbook\EAV\Repositories\EntityRepository
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetRepository extends AbstractRepository
{
	use ValidatorTrait;

	/**
	 * Factory for attribute handlers,
	 * makes appropriate attriubte handler depending on attribute data type
	 * 
	 * @var Cookbook\Contracts\EAV\AttributeHandlerFactoryContract
	 */
	protected $attributeHandlerFactory;

	/**
	 * Repository for handling entities
	 * 
	 * @var Cookbook\EAV\Repositories\EntityRepository
	 */
	protected $entityRepository;

	

	/**
	 * Array used to create rules for validation for INSERT attribute set
	 * 
	 * @var array
	 */
	protected $attributeSetInsertParamRules;

	/**
	 * Array used to create rules for validation for UPDATE attribute set
	 * 
	 * @var array
	 */
	protected $attributeSetUpdateParamRules;

	/**
	 * Array used to create rules for validation for INSERT/UPDATE attribute group
	 * 
	 * @var array
	 */
	protected $attributeGroupParamRules;

	/**
	 * Array used to create rules for validation for INSERT/UPDATE attribute group translation
	 * 
	 * @var array
	 */
	protected $attributeGroupTranslationParamRules;

	/**
	 * Array used to create rules for validation for INSERT/UPDATE set attribute
	 * 
	 * @var array
	 */
	protected $setAttributeParamRules;


	/**
	 * Create new AttributeSetRepository
	 * 
	 * @param Illuminate\Database\Connection $db
	 * @param Cookbook\EAV\Handlers\AttributeHandlerFactoryContract $attributeHandlerFactory
	 * @param Cookbook\EAV\Repositories\EntityRepository $entityRepository
	 * 
	 * @return void
	 */
	public function __construct(Connection $db,
								AttributeHandlerFactoryContract $attributeHandlerFactory,
								EntityRepository $entityRepository)
	{

		// AbstractRepository constructor
		parent::__construct($db);
		
		// Inject dependencies
		$this->attributeHandlerFactory = $attributeHandlerFactory;
		$this->entityRepository = $entityRepository;

		// set default key for errors
		$this->setErrorKey('attribute_set.errors');

		// Validation Rules for attribute set insert
		$this->attributeSetInsertParamRules = array(
			'entity_type_id'		=> 'exists:entity_types,id',
			'parent_id'				=> 'exists:attribute_sets,id',
			'slug'					=> 'required|unique:attribute_sets,slug',
			'name'					=> 'required',
		);

		// Validation Rules for attribute set update
		$this->attributeSetUpdateParamRules = array(
			'id'					=> 'required|integer|exists:attribute_sets,id',
			'slug'					=> 'required|unique:attribute_sets,slug',
			'name'					=> 'required',
		);

		// Validation Rules for attribute set group insert
		$this->attributeGroupParamRules = array(
			'attribute_set_id'		=> 'required|exists:attribute_sets,id',
			'slug'					=> 'required|between:3,50',
			'admin_label'			=> 'max:250',
			'sort_order'			=> 'required|integer',
		);

		// Validation Rules for attribute set group translations insert
		$this->attributeGroupTranslationParamRules = array(
			'attribute_group_id'	=> 'required|integer|exists:attribute_groups,id',
			'language_id' 			=> 'integer',
			'name'					=> 'max:250',
			'created_at'			=> 'date',
			'updated_at'			=> 'date',
		);

		// Validation Rules for set attribute insert
		$this->setAttributeParamRules = array(
			'attribute_group_id'	=> 'required|integer|exists:attribute_groups,id',
			'attribute_set_id'		=> 'required|integer|exists:attribute_sets,id',
			'attribute_id' 			=> 'required|integer|exists:attributes,id',
			'sort_order'			=> 'required|integer',
			'created_at'			=> 'date',
			'updated_at'			=> 'date',
		);

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

		try 
		{

			$groups = array();
			if(!empty($params['groups']) && is_array($params['groups']))
			{
				$groups = $params['groups'];
			}

			unset($params['groups']);

			$setAsDefault = false;
			if(!empty($params['set_as_default']))
			{
				$setAsDefault = true;
			}

			unset($params['set_as_default']);

			// insert attribute set
			$attributeSetId = $this->insertAttributeSet($params);

			if(!$attributeSetId)
			{
				return false;
			}			

			$attributeSet = $this->db->table('attribute_sets')->find($attributeSetId);

			// set relation to attribute set in all groups
			if($attributeSetId)
			{
				for($i = 0; $i < count($groups); $i++)
				{
					$groups[$i]['attribute_set_id'] = $attributeSetId;
				}
			}

			$this->updateGroups($attributeSet, $groups);

			if($this->hasErrors())
			{
				return false;
			}

			if($setAsDefault)
			{
				$this->setSetAsDefault($attributeSet);
			}

			return $attributeSetId;

		}
		catch(Exception $e)
		{
			$this->addErrors('Failed to save attribute set. There was an error.');
			return false;
		}
		
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
	protected function _update($model)
	{

		try
		{

			// check for ID
			if(empty($params['id']))
			{
				$this->addErrors('Attribute set ID needs to be provided.');
				return false;
			}

			$attributeSet = $this->db->table('attribute_sets')->find($params['id']);

			if(!$attributeSet)
			{
				$this->addErrors('Invalid attribute ID.');
				return false;
			}

			$groups = array();

			if(!empty($params['groups']) && is_array($params['groups']))
			{
				$groups = $params['groups'];
			}

			unset($params['groups']);
			
			$setAsDefault = false;
			if(!empty($params['set_as_default']))
			{
				$setAsDefault = true;
			}

			unset($params['set_as_default']);

			// insert attribute set
			$attributeSetId = $this->updateAttributeSet($params);

			if(!$attributeSetId)
			{
				return false;
			}

			// set relation to attribute in all options
			for($i = 0; $i < count($groups); $i++)
			{
				$groups[$i]['attribute_set_id'] = $attributeSetId;
			}


			$this->updateGroups($attributeSet, $groups);

			if($this->hasErrors())
			{
				return false;
			}

			if($setAsDefault)
			{
				$this->setSetAsDefault($attributeSet);
			}

			return $attributeSetId;

		}
		catch(\Exception $e)
		{
			$this->addErrors('Failed to save attribute set. There was an error.');
			return false;
		}
		
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
	protected function _delete($ids)
	{

		if( ! is_array($ids) )
		{
			$ids = array( intval($ids) );
		}

		try 
		{
			// get the attribute
			$attributeSets = $this->db 	->table('attribute_sets')
										->with('setAttributes.attribute', 'groups')
										->whereIn('id', $ids)
										->get();

			if(empty($attributeSets))
			{
				$this->addErrors('Attribute set ID needs to be provided.');
				return false;
			}

			$ids = array();

			foreach ($attributeSets as $attributeSet)
			{
				$ids[] = $attributeSet->id;
			}

			// delete all related set attributes for this attribute set
			$this->db 	->table('set_attributes')
						->whereIn('attribute_set_id', $ids)
						->delete();

			// delete all group translations
			$this->db 	->table('attribute_group_translations')
						->join('attribute_groups', 'attribute_group_translations.attribute_group_id', '=', 'attribute_groups.id')
						->whereIn('attribute_groups.attribute_set_id', $ids)
						->delete();
			
			// delete all groups
			$this->db 	->table('attribute_groups')
						->whereIn('attribute_set_id', $ids)
						->delete();

			$objectIDs = $this->entityRepository->getObjectIDsFromAttributeSets($ids);

			if(!empty($objectIDs))
			{
				$$this->entityRepository->delete($objectIDs);
			}

			// delete the attribute set
			$this->db 	->table('attribute_sets')
						->whereIn('id', $ids)
						->delete();

			return true;

		}
		catch(\Exception $e)
		{
			$this->addErrors('Failed to delete attribute set. Error: ');
			return false;
		}
	}


	/**
	 * Validate attribute set params and insert in database
	 * using $attributeSetModel
	 * 
	 * @param array $params = attribute set params
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function insertAttributeSet($params)
	{

		// validate attribute params
		$this->validateParams($params, $this->attributeSetInsertParamRules);

		if($this->hasErrors())
		{
			return false;
		}

		// insert attribute set in database
		$attributeSetId = $this->db->table('attribute_sets')->insertGetId($params);
		

		// Fail if there were errors in previous validations
		if($this->hasErrors())
		{
			return false;
		}

		return $attributeSetId;
	}


	/**
	 * Validate attribute set params and update it in database
	 * using $attributeSetModel
	 * 
	 * @param array $params = attribute set params
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function updateAttributeSet($params)
	{

		
		// validate attribute set params
		$this->validateParams($params, $this->attributeSetUpdateParamRules);

		// Fail if there were errors in previous validations
		if($this->hasErrors())
		{
			return false;
		}

		// get attribute set from database
		$attributeSet = $this->db->table('attribute_setss')->find($params['id']);

		if(!$attributeSet)
		{
			$this->addErrors('Invalid attribute set ID.');
			return false;
		}
		
		$attributeSetParams = json_decode(json_encode($attributeSet), true);
		$attributeSetParams = array_merge($attributeSetParams, $params);

		unset($attributeSetParams['id']);

		$attributeSetParams['updated_at'] = date('Y-m-d H:i:s');

		// update attribute set in database
		$this->db->table('attribute_sets')->where('id', '=', $params['id'])->update($attributeSetParams);

		return $attributeSet->getKey();
	}


	/**
	 * Validate and update groups with attributes
	 * 
	 * @param stdClass 	$attributeSet
	 * @param array 	$groups
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function updateGroups($attributeSet, $groups = array())
	{

		// check if $groups is array
		if( ! is_array($groups) )
		{
			$this->addErrors('Groups need to be an array.');
			return false;
		}

		// get all groups ids
		$groupsIDs = $this->db	->table('attribute_groups')
								->where('attribute_set_id', '=', $attributeSet->id)
								->lists('id');
		
		if( ! empty($groupsIDs) )
		{
			// delete all group translations
			$this->db 	->table('attribute_group_translations')
						->whereIn('attribute_group_id', $groupsIDs)
						->delete();
		}
		

		// delete all groups
		$this->db 	->table('attribute_groups')
					->where('attribute_set_id', '=', $attributeSet->id)
					->delete();

		// get old set attributes
		$oldSetAttributes = $this->db 	->table('set_attributes')
										->join('attributes', 'set_attributes.attribute_id', '=', 'attributes.id')
										->where('attribute_set_id', '=', $attributeSet->id)
										->get();
	
		// delete all set attributes
		$this->db 	->table('set_attributes')
					->where('attribute_set_id', '=', $attributeSet->id)
					->delete();
			
		foreach ($groups as $group)
		{

			// separate params array in attribute and attribute translations params
			$attributeGroupTranslations = $group['translations'];
			unset($group['translations']);

			// check if there are any attributes in this group
			if( ! empty( $group['set_attributes'] ) && is_array( $group['set_attributes'] ) )
			{
				// take attributes if there are any
				$attributes = $group['set_attributes'];
				unset($group['set_attributes']);
			}
			else
			{
				$attributes = array();
			}

			$this->validateParams($group, $this->attributeGroupParamRules);

			if($this->hasErrors())
			{
				return false;
			}

			// insert the group and take the ID
			$attributeGroupId = $this->db->table('attribute_groups')->insertGetId($group);

			// validate attribute translations
			for($i = 0; $i < count($attributeGroupTranslations); $i++)
			{
				$attributeGroupTranslations[$i]['attribute_group_id'] = $attributeGroupId;
				$attributeGroupTranslations[$i]['created_at'] = $attributeGroupTranslations[$i]['updated_at'] = date('Y-m-d H:i:s');
				$this->validateParams($attributeGroupTranslations[$i], $this->attributeGroupTranslationParamRules);
			}

			if($this->hasErrors())
			{
				return false;
			}

			// insert translations in database
			$this->db->table('attribute_group_translations')->insert($attributeGroupTranslations);

			
			// validate set attributes
			for($i = 0; $i < count($attributes); $i++)
			{
				$attributes[$i]['attribute_group_id'] = $attributeGroupId;
				$attributes[$i]['attribute_set_id'] = $group['attribute_set_id'];
				$attributes[$i]['created_at'] = $attributes[$i]['updated_at'] = date('Y-m-d H:i:s');
				$this->validateParams($attributes[$i], $this->setAttributeParamRules);
			}

			if($this->hasErrors())
			{
				return false;
			}

			$attributesForSweep = array();
			foreach ($oldSetAttributes as $oldAttribute)
			{
				$delete = true;
				foreach ($attributes as $newAttribute)
				{
					if($newAttribute['attribute_id'] == $oldAttribute->attribute_id)
					{
						$delete = false;
						break;
					}
				}

				if($delete)
				{
					$attributesForSweep[] = $oldAttribute;
				}
			}


			if(!empty($attributesForSweep))
			{
				foreach ($attributesForSweep as $setAttributeForSweep)
				{
					$handler = $this->attributeHandlerFactory->make($setAttributeForSweep->data_type);
					$handler->sweepAfterSetAttribute($setAttributeForSweep);
				}
			}

			if(!empty($attributes))
			{
				// insert set attributes in database
				$this->db->table('set_attributes')->insert( $attributes );
			}
			
		}

		if($this->hasErrors())
		{
			return false;
		}

		return true;
	}


	/**
	 * Set given attribute set as default set for it's entity type
	 * @uses Vizioart\Attributes\Models\EntityType
	 * 
	 * @param Vizioart\Attributes\Models\AttributeSet $set
	 * 
	 * @return boolean
	 * 
	 */
	protected function setSetAsDefault($set){

		return $this->db->table('entity_types')
						->where('id', $set->entity_type_id)
						->update( array( 'default_attribute_set_id' => $set->id ) );
	}

	// ----------------------------------------------------------------------------------------------
	// GETTERS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Get attribute set by ID
	 * using $attributeSetModel
	 * 
	 * @param int $id - ID of attribute set to be fetched
	 * @param array $with - optional relations to be fetched with attribute set
	 * 
	 * @return Model
	 */
	public function fetch($id, $with = array()){
		return $this->attributeSetModel->with($with)->find($id);
	}

	/**
	 * Get all attribute sets
	 * using $attributeSetModel
	 * 
	 * @param array $with - optional relations to be fetched with attribute sets
	 * 
	 * @return Collection
	 */
	public function fetchAll($with = array()){
		return $this->attributeSetModel->with($with)->get();
	}

	/**
	 * Get all attribute sets belonging to type
	 * using $attributeSetModel
	 *
	 * @param string $type - entity type slug 
	 * @param array $with - optional relations to be fetched with attribute sets
	 * 
	 * @return Collection
	 */
	public function fetchByType($type, $with = array()){
		return $this->attributeSetModel
					->with($with)
					->whereHas('entityType', function($q) use ($type){
						$q->where('slug', '=', $type);
					})->get();
	}

	/**
	 * Check if there is an attribute set with that slug
	 * 
	 * @param array $params - (attribute_set_id, value)
	 * 
	 * @return boolean
	 */
	public function uniqueSlug($params){

		if(empty($params['slug'])){
			$this->addErrors(
				array('Invalid params')
			);
			return false;
		}

		if(empty($params['attribute_set_id'])){
			$attributeSetId = 0;
		}else{
			$attributeSetId = intval($params['attribute_set_id']);
		}
		
		$attributeSet = $this->attributeSetModel->where('slug', '=', $params['slug'])->where('id', '!=', $attributeSetId)->first();

		if($attributeSet){
			return false;
		}else{
			return true;
		}
	}
}