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

use Carbon\Carbon;
use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;
use Cookbook\Contracts\Eav\FieldHandlerFactoryContract;
use Cookbook\Contracts\Locales\LocaleRepositoryContract;
use Cookbook\Contracts\Workflows\WorkflowPointRepositoryContract;
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
use stdClass;

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
     * @var \Cookbook\Contracts\Eav\FieldHandlerFactoryContract
     */
    protected $fieldHandlerFactory;


    /**
     * Helper for attributes
     *
     * @var \Cookbook\Eav\Managers\AttributeManager
     */
    protected $attributeManager;
    
    /**
     * Repository for handling attribute sets
     *
     * @var \Cookbook\Contracts\Eav\AttributeSetRepositoryContract
     */
    protected $attributeSetRepository;

    /**
     * Repository for handling attributes
     *
     * @var \Cookbook\Contracts\Eav\AttributeRepositoryContract
     */
    protected $attributeRepository;

    /**
     * Repository for handling entity types
     *
     * @var \Cookbook\Contracts\Eav\AttributeRepositoryContract
     */
    protected $entityTypeRepository;

    /**
     * Repository for handling locales
     *
     * @var \Cookbook\Contracts\Workflows\WorkflowPointRepositoryContract
     */
    protected $workflowPointRepository;

    /**
     * Repository for handling locales
     *
     * @var \Cookbook\Contracts\Locales\LocaleRepositoryContract
     */
    protected $localeRepository;



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
                                AttributeRepositoryContract $attributeRepository,
                                EntityTypeRepositoryContract $entityTypeRepository,
                                WorkflowPointRepositoryContract $workflowPointRepository,
                                LocaleRepositoryContract $localeRepository)
    {

        // AbstractRepository constructor
        parent::__construct($db);

        // Inject dependencies
        $this->fieldHandlerFactory = $fieldHandlerFactory;
        $this->attributeManager = $attributeManager;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeRepository = $attributeRepository;
        $this->entityTypeRepository = $entityTypeRepository;
        $this->workflowPointRepository = $workflowPointRepository;
        $this->localeRepository = $localeRepository;
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
        if (! empty($model['fields']) && is_array($model['fields'])) {
            $fields = $model['fields'];
        }

        $locale = false;
        $locale_id = null;

        if (! empty($model['locale'])) {
            $locale = $this->localeRepository->fetch($model['locale']);
            $locale_id = $locale->id;
        }
        $locales = $this->localeRepository->get();
        

        $fieldsForInsert = [];
        $attributes = [];

        if (! empty($fields)) {
            $attributes = $this->attributeRepository->get(['code' => ['in' => array_keys($fields)]]);
        }

        $status = null;
        if (! empty($model['status'])) {
            $status = $model['status'];
        }

        unset($model['fields']);
        unset($model['locale']);
        unset($model['status']);

        // insert entity
        $entityID = $this->insertEntity($model);

        foreach ($attributes as $attribute) {
            $fieldForInsert = [];
            $fieldForInsert['entity_id'] = $entityID;
            $fieldForInsert['entity_type_id'] = $model['entity_type_id'];
            $fieldForInsert['attribute_set_id'] = $model['attribute_set_id'];
            $fieldForInsert['attribute_id'] = $attribute->id;
            
            if ($locale || ! $attribute->localized) {
                $fieldForInsert['locale_id'] = ($attribute->localized)?$locale->id:0;
                $fieldForInsert['value'] = (isset($fields[$attribute->code]))?$fields[$attribute->code]:$attribute->default_value;
                $fieldsForInsert[] = $fieldForInsert;
            } else {
                foreach ($locales as $l) {
                    $localizedFieldForInsert = $fieldForInsert;
                    $localizedFieldForInsert['locale_id'] = $l->id;

                    if (isset($fields[$attribute->code]) && isset($fields[$attribute->code][$l->code])) {
                        $localizedFieldForInsert['value'] = $fields[$attribute->code][$l->code];
                        $fieldsForInsert[] = $localizedFieldForInsert;
                        continue;
                    }
                    
                    $localizedFieldForInsert['value'] = $attribute->default_value;
                    $fieldsForInsert[] = $localizedFieldForInsert;
                }
            }
        }

        $this->insertFields($fieldsForInsert, $attributes);

        $entityType = $this->entityTypeRepository->fetch($model['entity_type_id']);

        $locale_ids = [];
        if (! $entityType->localized_workflow) {
            $locale_ids[] = 0;
        } else {
            if (empty($locale_id)) {
                foreach ($locales as $l) {
                    $locale_ids[] = $l->id;
                }
            } else {
                $locale_ids[] = $locale_id;
            }
        }

        if (isset($status)) {
            $point = $this->workflowPointRepository->get(['status' => $status, 'workflow_id' => $entityType->workflow->id]);
            $this->insertStatus($entityID, $point[0]->id, $locale_ids);
        } else {
            $this->insertStatus($entityID, $entityType->default_point->id, $locale_ids);
        }

        $entity = $this->fetch($entityID, [], $locale_id);

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
        $locale = false;
        $locale_id = null;

        if (! empty($model['locale'])) {
            $locale = $this->localeRepository->fetch($model['locale']);
            $locale_id = $locale->id;
        }

        $entity = $this->fetch($id, [], $locale_id);

        $fields = array();
        
        if (! empty($model['fields']) && is_array($model['fields'])) {
            $fields = $model['fields'];
        }
        

        $fieldsForUpdate = [];
        $attributes = [];

        if (! empty($fields)) {
            $attributes = $this->attributeRepository->get(['code' => ['in' => array_keys($fields)]]);
        }

        foreach ($attributes as $attribute) {
            $fieldForUpdate = [];
            $fieldForUpdate['entity_id'] = $entity->id;
            $fieldForUpdate['entity_type_id'] = $entity->entity_type_id;
            $fieldForUpdate['attribute_set_id'] = $entity->attribute_set_id;
            $fieldForUpdate['attribute_id'] = $attribute->id;
            
            if ($locale || ! $attribute->localized) {
                $fieldForUpdate['locale_id'] = ($attribute->localized)?$locale->id:0;
                $fieldForUpdate['value'] = (isset($fields[$attribute->code]))?$fields[$attribute->code]:$attribute->default_value;
                $fieldsForUpdate[] = $fieldForUpdate;
            } else {
                $locales = $this->localeRepository->get();

                foreach ($fields[$attribute->code] as $lcode => $value) {
                    $localizedFieldForUpdate = $fieldForUpdate;
                    foreach ($locales as $l) {
                        if ($l->code == $lcode) {
                            $localizedFieldForUpdate['locale_id'] = $l->id;
                            $localizedFieldForUpdate['value'] = $value;
                            $fieldsForUpdate[] = $localizedFieldForUpdate;
                        }
                    }
                }
            }
        }

        $this->updateFields($fieldsForUpdate, $attributes);

        $entityType = $this->entityTypeRepository->fetch($entity->entity_type_id);

        $locale_ids = [];
        if (! $entityType->localized_workflow) {
            $locale_ids[] = 0;
        } else {
            if (empty($locale_id)) {
                foreach ($locales as $l) {
                    $locale_ids[] = $l->id;
                }
            } else {
                $locale_ids[] = $locale_id;
            }
        }

        if (isset($status)) {
            $point = $this->workflowPointRepository->get(['status' => $status, 'workflow_id' => $entityType->workflow->id]);
            $this->updateStatus($entityID, $point->id, $locale_ids);
        }

        $this->updateEntity($id);

        Trunk::forgetType('entity');
        $entity = $this->fetch($id, [], $locale_id);

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

        Trunk::forgetType('entity');
        return $entity;
    }

    /**
     * Delete locele for entity
     *
     * @param mixed $id entity id
     * @param mixed $locale_id  locale id
     *
     * @todo check if its last locale and delete whole entity if it is
     */
    public function deleteForLocale($id, $locale_id)
    {
        // get the locale
        $locale = $this->localeRepository->fetch($locale_id);
        // get the entity
        $entity = $this->fetch($id, [], $locale->id);

        $this->deleteFieldsForLocale($entity, $locale);

        // $this->db->table('entities')->where('id', '=', $id)->delete();

        Trunk::forgetType('entity');
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
        Trunk::forgetType('entity');
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

        $entities = $this->db    ->table('entities')
                                ->where('attribute_set_id', '=', $attributeSet->id)
                                ->delete();
        Trunk::forgetType('entity');
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

        $entities = $this->db    ->table('entities')
                                ->where('entity_type_id', '=', $entityType->id)
                                ->delete();
        Trunk::forgetType('entity');
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
        $params['created_at'] = $params['updated_at'] = Carbon::now('UTC')->toDateTimeString();

        // insert entity in database
        $entityId = $this->db->table('entities')->insertGetId($params);

        if (! $entityId) {
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
        $params['updated_at'] = Carbon::now('UTC')->toDateTimeString();

        $this->db->table('entities')->where('id', '=', $id)->update($params);
    }

    /**
     * Insert attribute values in entity
     *
     * @param array $fields - field values
     * @param mixed $attributes - attribute definitions
     *
     * @return void
     */
    protected function insertFields(array $fields, $attributes)
    {
        foreach ($fields as $field) {
            foreach ($attributes as $attribute) {
                if ($attribute->id == $field['attribute_id']) {
                    $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
                    $fieldHandler->insert($field, $attribute);
                }
            }
        }
    }

    /**
     * Update attribute values in entity
     *
     * @param array $fields - field values
     * @param mixed $attributes - attribute definitions
     *
     * @return boolean
     */
    protected function updateFields(array $fields, $attributes)
    {
        foreach ($fields as $field) {
            foreach ($attributes as $attribute) {
                if ($attribute->id == $field['attribute_id']) {
                    $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
                    $fieldHandler->insert($field, $attribute);
                }
            }
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
        $attributes = $this->attributeRepository->get();
        foreach ($attributes as $attribute) {
            $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
            $fieldHandler->deleteByEntity($entity, $attribute);
        }
    }

    /**
     * Delete attribute values for entity
     *
     * @param Model $entity
     * @param Model $locale
     *
     * @return void
     */
    protected function deleteFieldsForLocale($entity, $locale)
    {
        $attributes = [];
        $attributes = $this->attributeRepository->get();
        foreach ($attributes as $attribute) {
            $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
            $fieldHandler->deleteByEntityAndLocale($entity, $locale, $attribute);
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
        $attributes = $this->attributeRepository->get();

        foreach ($attributes as $attribute) {
            $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
            $fieldHandler->deleteByAttributeSet($attributeSet, $attribute);
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
        $attributes = $this->attributeRepository->get();

        foreach ($attributes as $attribute) {
            $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
            $fieldHandler->deleteByEntityType($entityType, $attribute);
        }
    }

    protected function insertStatus($id, $pointId, $localeIds)
    {
        $statusParams = [];
        foreach ($localeIds as $localeId) {
            $statusParams[] = [
                'entity_id' => $id,
                'workflow_point_id' => $pointId,
                'locale_id' => $localeId,
                'state' => 'active'
            ];
        }
        
        $this->db->table('entity_statuses')->insert($statusParams);
    }

    protected function updateStatus($id, $pointId, $localeIds)
    {
        $updateParams = [
            'state' => 'history',
            'updated_at' => Carbon::now('UTC')->toDateTimeString()
        ];

        $this->db->table('entity_statuses')
                 ->where('entity_id', '=', $id)
                 ->where('state', '=', 'active')
                 ->whereIn('locale_id', $localeIds)
                 ->update($updateParams);

        $statusParams = [];
        foreach ($localeIds as $localeId) {
            $statusParams[] = [
                'entity_id' => $id,
                'workflow_point_id' => $pointId,
                'locale_id' => $localeId,
                'state' => 'active'
            ];
        }

        $this->db->table('entity_statuses')->insert($statusParams);
    }


    


    // ----------------------------------------------------------------------------------------------
    // GETTERS
    // ----------------------------------------------------------------------------------------------

    /**
     * Get entity by ID
     *
     * @param mixed $id
     * @param array $include
     * @param mixed $locale
     *
     * @return array
     */
    protected function _fetch($id, $include = [], $locale = null, $status = null)
    {
        $params = func_get_args();
        $params['function'] = __METHOD__;
        if (Trunk::has($params, 'entity')) {
            $entity = Trunk::get($params, 'entity');
            $entity->clearIncluded();
            $entity->load($include);
            $meta = ['id' => $id, 'include' => $include, 'locale' => $locale, 'status' => $status];
            $entity->setMeta($meta);
            return $entity;
        }

        if (! is_null($status) && ! is_array($status)) {
            $status = explode(',', $status);
            foreach ($status as &$s) {
                $s = trim($s);
            }
        }

        $locale_ids = [0];
        if (! is_null($locale)) {
            $locale = $this->localeRepository->fetch($locale);
            $locale_ids[] = $locale->id;
        } else {
            $locales = $this->localeRepository->get();
            foreach ($locales as $l) {
                $locale_ids[] = $l->id;
            }
        }

        $query = $this->db->table('entities')
                        ->select(
                            'entities.id',
                            'entities.entity_type_id',
                            'entities.attribute_set_id',
                            'entity_types.code as entity_type',
                            'entity_types.localized as localized',
                            'entity_types.localized_workflow as localized_workflow',
                            'entity_types.workflow_id as workflow_id',
                            $this->db->raw('"entity" as type'),
                            'entities.created_at as created_at',
                            'entities.updated_at as updated_at'
                        )
                        ->where('entities.id', '=', $id)
                        ->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id');

        if (! empty($status)) {
            $query = $this->filterStatus($query, $status, $locale_ids);
        }

        $entity = $query->first();
        
        if (! $entity) {
            throw new NotFoundException(['Entity not found.']);
        }

        if (! is_null($locale) && $entity->localized) {
            $entity->locale = $locale->code;
        }

        $entity = $this->getStatusesForEntities($entity, $status, $locale);

        $entity = $this->getFieldsForEntities($entity, $locale);

        $timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
        $entity->created_at = Carbon::parse($entity->created_at)->tz($timezone);
        $entity->updated_at = Carbon::parse($entity->updated_at)->tz($timezone);

        $result = new Model($entity);
        $result->setParams($params);
        $meta = ['id' => $id, 'include' => $include, 'locale' => is_null($locale)?$locale:$locale->id, 'status' => $status];
        $result->setMeta($meta);
        $result->load($include);

        return $result;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [], $locale = null, $status = null)
    {
        $params = func_get_args();
        $params['function'] = __METHOD__;

        if (Trunk::has($params, 'entity')) {
            $entity = Trunk::get($params, 'entity');
            $entity->clearIncluded();
            $entity->load($include);
            $meta = [
                'include' => $include,
                'locale' => $locale,
                'status' => $status
            ];
            $entity->setMeta($meta);
            return $entity;
        }

        if (! is_null($status) && ! is_array($status)) {
            $status = explode(',', $status);
            foreach ($status as &$s) {
                $s = trim($s);
            }
        }

        $locale_ids = [0];
        if (! is_null($locale)) {
            $locale = $this->localeRepository->fetch($locale);
            $locale_ids[] = $locale->id;
        } else {
            $locales = $this->localeRepository->get();
            foreach ($locales as $l) {
                $locale_ids[] = $l->id;
            }
        }

        $query =  $this->db->table('entities')
                        ->select(
                            'entities.id as id',
                            'entities.entity_type_id as entity_type_id',
                            'entities.attribute_set_id as attribute_set_id',
                            'entity_types.code as entity_type',
                            'entity_types.localized as localized',
                            'entity_types.localized_workflow as localized_workflow',
                            'entity_types.workflow_id as workflow_id',
                            $this->db->raw('"entity" as type'),
                            'entities.created_at as created_at',
                            'entities.updated_at as updated_at'
                        )
                        ->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id');
        
        $query = $this->parseFilters($query, $filter, $locale);

        $query->groupBy('entities.id');

        $total = $query->count();

        $query = $this->parsePaging($query, $offset, $limit);

        $query = $this->parseSorting($query, $sort);

        if (! empty($status)) {
            $query = $this->filterStatus($query, $status, $locale_ids);
        }

        $entities = $query->get();

        if (! $entities) {
            $entities = [];
        }

        $entities = $this->getStatusesForEntities($entities, $status, $locale);
        $entities = $this->getFieldsForEntities($entities, $locale);

        foreach ($entities as &$entity) {
            $timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
            $entity->created_at = Carbon::parse($entity->created_at)->tz($timezone);
            $entity->updated_at = Carbon::parse($entity->updated_at)->tz($timezone);
            if (! is_null($locale) && $entity->localized) {
                $entity->locale = $locale->code;
            }
        }

        $result = new Collection($entities);
        
        $result->setParams($params);

        $meta = [
            'count' => count($entities),
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'filter' => $filter,
            'sort' => $sort,
            'include' => $include,
            'locale' => is_null($locale)?$locale:$locale->id,
            'status' => $status
        ];
        $result->setMeta($meta);

        $result->load($include);
        
        return $result;
    }

    protected function parseFilters($query, $filters, $locale = null)
    {
        $fieldFilters = [];
        $status = null;
        $public = false;
        $fulltextSearch = null;
        foreach ($filters as $key => $filter) {
            if ($key == 'entity_type' || $key == 'type')
            {
                $key = 'entity_types.code';
            }
            if ($key == 'type_id')
            {
                $key = 'entity_type_id';
            }

            if ($key == 's')
            {
                $fulltextSearch = strval($filter);
                continue;
            }

            if (strpos($key, '.') == false)
            {
                $key = 'entities.' . $key;
            }

            if (substr($key, 0, 7) === "fields.")
            {
                $code = substr($key, 7);
                $fieldFilters[$code] = $filter;
                continue;
            }

            if (! is_array($filter))
            {
                $query = $query->where($key, '=', $filter);
                continue;
            }

            $query = $this->parseFilterOperator($query, $key, $filter);
        }
        if (! empty($fieldFilters))
        {
            $attributes = $this->attributeRepository->get(['code' => ['in'=>array_keys($fieldFilters)]]);

            foreach ($attributes as $attribute)
            {
                $query = $this->parseFieldFilter($query, $attribute, $fieldFilters[$attribute->code], $locale);
            }
        }

        if( ! empty($fulltextSearch) )
        {
            $query = $this->parseFulltextSearch($query, $fulltextSearch, $locale);
        }

        return $query;
    }

    protected function parseFilterOperator($query, $key, $filter)
    {
        foreach ($filter as $operator => $value) {
            switch ($operator) {
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

    protected function parseFulltextSearch($query, $search, $locale = null)
    {
        $query = $query->join('attribute_values_fulltext as fulltextsearch', function($join)
            {
                $join->on('fulltextsearch.entity_id', '=', 'entities.id');
            }
        );
        if( ! is_null($locale) )
        {
            $query->whereIn('fulltextsearch.locale_id', [0, $locale->id]);
        }
        $query = $query->whereRaw('MATCH (fulltextsearch.value) AGAINST (?)' , array($search));

        return $query;
    }

    protected function parseFieldFilter($query, $attribute, $filter, $locale = null)
    {
        $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
        $query = $fieldHandler->filterEntities($query, $attribute, $filter, $locale);

        return $query;
    }

    protected function filterStatus($query, $status, $localeIds)
    {
        $query->join('entity_statuses', 'entities.id', '=', 'entity_statuses.entity_id')
              ->join('workflow_points', 'entity_statuses.workflow_point_id', '=', 'workflow_points.id')
              ->where('entity_statuses.state', '=', 'active')
              ->whereIn('entity_statuses.locale_id', $localeIds)
              ->whereIn('workflow_points.status', $status);
        return $query;
    }

    protected function parseSorting($query, $sort)
    {
        if (! empty($sort)) {
            $sort = (is_array($sort))? $sort: [$sort];
            $fieldSorting = [];
            foreach ($sort as $sortCriteria) {
                if ($sortCriteria[0] === '-') {
                    $sortCriteria = substr($sortCriteria, 1);
                }

                if (substr($sortCriteria, 0, 7) === "fields.") {
                    $code = substr($sortCriteria, 7);
                    $fieldSorting[] = $code;
                }
            }
            $attributes = [];
            if (! empty($fieldSorting)) {
                $attributes = $this->attributeRepository->get(['code' => ['in'=>$fieldSorting]]);
            }


            foreach ($sort as $sortCriteria) {
                $sortDirection = 'asc';

                if ($sortCriteria[0] === '-') {
                    $sortCriteria = substr($sortCriteria, 1);
                    $sortDirection = 'desc';
                }

                if ($sortCriteria == 'entity_type' || $sortCriteria == 'type') {
                    $sortCriteria = 'entity_types.code';
                }
                if ($sortCriteria == 'type_id') {
                    $sortCriteria = 'entity_type_id';
                }

                if (strpos($sortCriteria, '.') == false) {
                    $sortCriteria = 'entities.' . $sortCriteria;
                }

                if (substr($sortCriteria, 0, 7) === "fields.") {
                    $code = substr($sortCriteria, 7);
                    foreach ($attributes as $attribute) {
                        if ($attribute->code == $code) {
                            $query = $this->parseFieldSorting($query, $attribute, $sortDirection);
                        }
                    }
                    continue;
                }

                $query = $query->orderBy($sortCriteria, $sortDirection);
            }
        }

        return $query;
    }

    protected function parseFieldSorting($query, $attribute, $direction, $locale = null)
    {
        $fieldHandler = $this->fieldHandlerFactory->make($attribute->field_type);
        $query = $fieldHandler->sortEntities($query, $attribute, $direction, $locale);

        return $query;
    }

    /**
     * Get entity statuses by entity IDs
     *
     * @param $entityIds
     * @param $locale
     *
     * @return array
     */
    protected function getStatusesForEntities($entities, $status = null, $locale = null)
    {
        if (empty($entities)) {
            return $entities;
        }
        $singleResult = false;
        if (!is_array($entities)) {
            $entities = array($entities);
            $singleResult = true;
        }

        $entityIds = [];
        $entitiesById = [];
        foreach ($entities as $entity) {
            $entityIds[] = $entity->id;
            $entitiesById[$entity->id] = $entity;
        }

        $result = [];
        $statuses = $this->getAllStatuses($entityIds, $status, $locale);
        foreach ($statuses as $status) {
            if (! array_key_exists($status->entity_id, $result) && empty($locale) && $entitiesById[$status->entity_id]->localized_workflow) {
                $result[$status->entity_id] = [];
            }

            if ($status->locale_id != 0 && empty($locale) && $entitiesById[$status->entity_id]->localized_workflow) {
                $result[$status->entity_id][$status->locale_code] = $status->status;
            } else {
                $result[$status->entity_id] = $status->status;
            }
        }

        foreach ($entities as &$entity) {
            $entity->status = null;
            if (! empty($result[$entity->id])) {
                $entity->status = $result[$entity->id];
            }
        }

        if ($singleResult) {
            $entities = $entities[0];
        }

        return $entities;
    }

    /**
     * Get statuses from database
     *
     * @param  array $entityIds
     * @param  integer|null $locale
     *
     * @return array
     */
    protected function getAllStatuses($entityIds, $status, $locale)
    {
        // statuses query
        $query = $this->db->table('entities')
                    ->select(
                        'entities.id as entity_id',
                        'entity_statuses.locale_id',
                        'locales.code as locale_code',
                        'workflow_points.status',
                        'entity_types.localized_workflow'
                    )
                    ->join('entity_types', 'entities.entity_type_id', '=', 'entity_types.id')
                    ->join('entity_statuses', 'entities.id', '=', 'entity_statuses.entity_id')
                    ->join('workflow_points', 'entity_statuses.workflow_point_id', '=', 'workflow_points.id')
                    ->leftJoin('locales', 'entity_statuses.locale_id', '=', 'locales.id')
                    ->whereIn('entities.id', $entityIds)
                    ->where('entity_statuses.state', '=', 'active');
        if (! is_null($locale)) {
            $localeIds = [0, $locale->id];
            $query->whereIn('entity_statuses.locale_id', $localeIds);
        }
                    
        if (! empty($status)) {
            $query->whereIn('workflow_points.status', $status);
        }
        $statuses = $query->orderBy('entities.id')
                          ->get();

        return $statuses;
    }

    /**
     * Get entity fields by entity IDs
     *
     * @param $entityIds
     * @param $locale
     *
     * @return array
     */
    protected function getFieldsForEntities($entities, $locale = null)
    {
        if (empty($entities)) {
            return $entities;
        }
        $singleResult = false;
        if (!is_array($entities)) {
            $entities = array($entities);
            $singleResult = true;
        }

        $entityIds = [];
        $entitiesById = [];
        foreach ($entities as $entity) {
            $entityIds[] = $entity->id;
            $entitiesById[$entity->id] = $entity;
        }

        $result = [];
        $attributeIds = [];
        $attributes = [];

        $locales = $this->localeRepository->get();
        $localesById = [];
        foreach ($locales as $l) {
            $localesById[$l->id] = $l;
        }

        $values = $this->getValuesAll($entityIds, $locale);

        // get distinct attribute ids from values
        foreach ($values as $value) {
            if (! in_array($value->attribute_id, $attributeIds)) {
                $attributeIds[] = $value->attribute_id;
            }
        }

        // get attributes
        if (! empty($attributeIds)) {
            $attributes = $this->attributeRepository->get(['id' => ['in' => $attributeIds]]);
        }

        $attributesById = [];
        foreach ($attributes as $attribute) {
            $attributesById[$attribute->id] = $attribute;
        }

        $attributeSettings = $this->attributeManager->getFieldTypes();
        $fieldHandlers = [];
        $fields = [];

        foreach ($entityIds as $entityId) {
            $fields[$entityId] = new stdClass();
        }
        foreach ($values as $value) {
            $attribute = $attributesById[$value->attribute_id];
            $handlerName = $attributeSettings[$attribute->field_type]['handler'];
            $hasMultipleValues = $attributeSettings[$attribute->field_type]['has_multiple_values'];
            if (! array_key_exists($handlerName, $fieldHandlers)) {
                $fieldHandlers[$handlerName] = $this->fieldHandlerFactory->make($attribute->field_type);
            }

            $fieldHandler = $fieldHandlers[$handlerName];

            $formattedValue = $fieldHandler->formatValue($value->value, $attribute);

            if ($hasMultipleValues) {
                $formattedValue = [$formattedValue];
            }

            if ($entitiesById[$value->entity_id]->localized && $attribute->localized && is_null($locale)) {
                if (! isset($fields[$value->entity_id]->{$attribute->code})) {
                    $fields[$value->entity_id]->{$attribute->code} = [];

                    foreach ($locales as $l) {
                        if (! $entitiesById[$value->entity_id]->localized_workflow || empty($entitiesById[$value->entity_id]->status) || ! is_array($entitiesById[$value->entity_id]->status) || array_key_exists($l->code, $entitiesById[$value->entity_id]->status)) {
                            $fields[$value->entity_id]->{$attribute->code}[$l->code] = null;
                        }
                    }
                }

                foreach ($locales as $l) {
                    if ($l->id == $value->locale_id && (! $entitiesById[$value->entity_id]->localized_workflow || empty($entitiesById[$value->entity_id]->status) || ! is_array($entitiesById[$value->entity_id]->status) || array_key_exists($l->code, $entitiesById[$value->entity_id]->status))) {
                        if (isset($fields[$value->entity_id]->{$attribute->code}[$l->code]) && $hasMultipleValues) {
                            $formattedValue = array_merge($fields[$value->entity_id]->{$attribute->code}[$l->code], $formattedValue);
                        }

                        $fields[$value->entity_id]->{$attribute->code}[$l->code] = $formattedValue;
                    }
                }
            } else {
                if (isset($fields[$value->entity_id]->{$attribute->code}) && $hasMultipleValues) {
                    $formattedValue = array_merge($fields[$value->entity_id]->{$attribute->code}, $formattedValue);
                }
                $fields[$value->entity_id]->{$attribute->code} = $formattedValue;
            }
        }

        foreach ($entities as &$entity) {
            $entity->fields = $fields[$entity->id];
        }

        if ($singleResult) {
            $entities = $entities[0];
        }

        return $entities;
    }

    protected function entityHasMultipleLocales($entity)
    {
        if ($entity->locale) {
            return false;
        }

        return true;
    }

    protected function getValuesAll($entityIds, $locale = null)
    {
        // get values from various tables
        $valuesDatetime = $this->getValuesDatetime($entityIds, $locale);
        $valuesDecimal = $this->getValuesDecimal($entityIds, $locale);
        $valuesInteger = $this->getValuesInteger($entityIds, $locale);
        $valuesText = $this->getValuesText($entityIds, $locale);
        // $valuesVarchar = $this->getValuesVarchar($entityIds, $locale);

        // get all values
        $values = array_merge(
            $valuesDatetime,
            $valuesDecimal,
            $valuesInteger,
            $valuesText
        );

        return $values;
    }

    protected function getValuesDatetime($entityIds, $locale = null)
    {
        return $this->getValues('attribute_values_datetime', $entityIds, $locale);
    }

    protected function getValuesDecimal($entityIds, $locale = null)
    {
        return $this->getValues('attribute_values_decimal', $entityIds, $locale);
    }
    
    protected function getValuesInteger($entityIds, $locale = null)
    {
        return $this->getValues('attribute_values_integer', $entityIds, $locale);
    }
    
    protected function getValuesText($entityIds, $locale = null)
    {
        return $this->getValues('attribute_values_text', $entityIds, $locale);
    }
    
    // protected function getValuesVarchar($entityIds, $locale = null)
    // {
    // 	return $this->getValues('attribute_values_varchar', $entityIds, $locale);
    // }
    
    protected function getValues($table, $entityIds, $locale = null)
    {
        // values query
        $query = $this->db->table('entities')
                    ->select(
                        'entities.id as entity_id',
                        'attributes.id as attribute_id',
                        $table . '.locale_id',
                        $table . '.value'
                    )
                    ->join('attribute_sets', 'entities.attribute_set_id', '=', 'attribute_sets.id')
                    ->leftJoin('set_attributes', 'attribute_sets.id', '=', 'set_attributes.attribute_set_id')
                    ->leftJoin('attributes', 'attributes.id', '=', 'set_attributes.attribute_id')
                    ->leftJoin($table, function ($join) use ($table) {
                        $join    ->on($table . '.attribute_id', '=', 'attributes.id')
                                ->on($table . '.entity_id', '=', 'entities.id');
                    })
                    ->whereIn('entities.id', $entityIds)
                    ->where('attributes.table', '=', $table);

        if (! is_null($locale)) {
            $query->whereIn($table . '.locale_id', [0, $locale->id]);
        }
        $values = $query->orderBy('entities.id', 'set_attributes.sort_order', $table . '.sort_order')
                        ->get();

        return $values;
    }
}
