<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Services;

use Carbon\Carbon;
use Congraph\Contracts\Eav\AttributeRepositoryContract;
use Congraph\Contracts\Eav\AttributeSetRepositoryContract;
use Congraph\Contracts\Eav\EntityTypeRepositoryContract;
use Congraph\Contracts\Locales\LocaleRepositoryContract;
use Congraph\Contracts\Workflows\WorkflowRepositoryContract;
use Congraph\Contracts\Workflows\WorkflowPointRepositoryContract;
use Congraph\Core\Facades\Trunk;
use Congraph\Core\Repositories\Collection;
use Congraph\Core\Repositories\Model;
use Congraph\Core\Repositories\UsesCache;
use Congraph\Eav\Managers\AttributeManager;

use Congraph\Eav\Traits\ElasticQueryBuilderTrait;


use Elasticsearch\ClientBuilder;
// use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use stdClass;

/**
 * MetaDataService class
 *
 * Repository for fetching meta data for each request
 *
 * @uses        Illuminate\Database\Connection
 * @uses        Congraph\Core\Repository\AbstractRepository
 * @uses        Congraph\Contracts\Eav\AttributeHandlerFactoryContract
 * @uses        Congraph\Eav\Managers\AttributeManager
 *
 * @author      Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright   Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package     congraph/eav
 * @since       0.1.0-alpha
 * @version     0.1.0-alpha
 */
class MetaDataService//, UsesCache
{

    /**
     * Repository for handling attributes
     *
     * @var \Congraph\Contracts\Eav\AttributeRepositoryContract
     */
    protected $attributeRepository;

    /**
     * Repository for handling attribute sets
     *
     * @var \Congraph\Contracts\Eav\AttributeSetRepositoryContract
     */
    protected $attributeSetRepository;

    /**
     * Repository for handling entity types
     *
     * @var \Congraph\Contracts\Eav\AttributeRepositoryContract
     */
    protected $entityTypeRepository;

    /**
     * Repository for handling workflows
     *
     * @var \Congraph\Contracts\Workflows\WorkflowPointRepositoryContract
     */
    protected $workflowRepository;

    /**
     * Repository for handling workflow points
     *
     * @var \Congraph\Contracts\Workflows\WorkflowPointRepositoryContract
     */
    protected $workflowPointRepository;

    /**
     * Repository for handling locales
     *
     * @var \Congraph\Contracts\Locales\LocaleRepositoryContract
     */
    protected $localeRepository;




    protected static $attributes;

    protected static $attributeSets;

    protected static $entityTypes;

    protected static $workflows;

    protected static $workflowPoints;

    protected static $locales;

    /**
     * Create new MetaDataService
     *
     * @param Congraph\Eav\Handlers\AttributeHandlerFactoryContract $attributeHandlerFactory
     * @param Congraph\Eav\Managers\AttributeManager $attributeManager
     * @param Congraph\Contracts\Eav\AttributeSetRepositoryContract $attributeSetRepository
     * @param Congraph\Contracts\Eav\AttributeRepositoryContract $attributeRepository
     * @param Congraph\Contracts\Eav\EntityTypeRepositoryContract $entityTypeRepository
     * @param Congraph\Contracts\Workflows\WorkflowPointRepositoryContract $workflowPointRepository
     * @param Congraph\Contracts\Locales\LocaleRepositoryContract $localeRepository
     *
     * @return void
     */
    public function __construct(
        AttributeRepositoryContract $attributeRepository,
        AttributeSetRepositoryContract $attributeSetRepository,   
        EntityTypeRepositoryContract $entityTypeRepository,
        WorkflowRepositoryContract $workflowRepository,
        WorkflowPointRepositoryContract $workflowPointRepository,
        LocaleRepositoryContract $localeRepository)
    {
        // Inject dependencies
        $this->attributeRepository = $attributeRepository;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->entityTypeRepository = $entityTypeRepository;
        $this->workflowRepository = $workflowRepository;
        $this->workflowPointRepository = $workflowPointRepository;
        $this->localeRepository = $localeRepository;
    }

    public function getAttributes()
    {
        if(self::$attributes === null)
        {
            self::$attributes = $this->attributeRepository->get();
        }

        if (! $this->isValidAttributeCollection(self::$attributes)) {
            Log::warning('MetaDataService detected malformed attributes metadata. Rebuilding cache.', [
                'items' => $this->describeCollection(self::$attributes),
            ]);
            self::$attributes = $this->attributeRepository->get();
        }

        return self::$attributes;
    }

    protected function isValidAttributeCollection($attributes): bool
    {
        $attributes = $this->normalizeMetadataItems($attributes);

        if (! is_iterable($attributes)) {
            return false;
        }

        foreach ($attributes as $attribute) {
            if (! is_object($attribute)) {
                return false;
            }

            if (! isset($attribute->id) || ! isset($attribute->field_type) || ! isset($attribute->code)) {
                return false;
            }
        }

        return true;
    }

    public function getAttributeById($id)
    {
        foreach ($this->getAttributes() as $attribute)
        {
            if($attribute->id == $id)
            {
                return $attribute;
            }
        }

        return false;
    }

    public function getAttributeByCode($code)
    {
        foreach ($this->getAttributes() as $attribute)
        {
            if($attribute->code == $code)
            {
                return $attribute;
            }
        }

        return false;
    }

    public function getAttributeByIdOrCode($identifier)
    {
        $attribute = $this->getAttributeById($identifier);
        if($attribute === false)
        {
            $attribute = $this->getAttributeByCode($identifier);
        }

        return $attribute;
    }

    public function getEntityTypes()
    {
        if(self::$entityTypes === null)
        {
            self::$entityTypes = $this->entityTypeRepository->get();
        }

        if (! $this->isValidEntityTypeCollection(self::$entityTypes)) {
            Log::warning('MetaDataService detected malformed entityTypes metadata. Rebuilding cache.', [
                'items' => $this->describeCollection(self::$entityTypes),
            ]);
            self::$entityTypes = $this->entityTypeRepository->get();
        }

        return self::$entityTypes;
    }

    protected function isValidEntityTypeCollection($entityTypes): bool
    {
        $entityTypes = $this->normalizeMetadataItems($entityTypes);

        if (! is_iterable($entityTypes)) {
            return false;
        }

        foreach ($entityTypes as $entityType) {
            if (! is_object($entityType)) {
                return false;
            }

            if (! isset($entityType->id) || ! isset($entityType->code)) {
                return false;
            }
        }

        return true;
    }

    public function getEntityTypeById($id)
    {
        foreach ($this->getEntityTypes() as $type)
        {
            if (! is_object($type) || ! isset($type->id)) {
                Log::warning('MetaDataService encountered malformed entity type in getEntityTypeById.', [
                    'requested_id' => $id,
                    'item' => $this->describeItem($type),
                ]);
                continue;
            }

            if($type->id == $id)
            {
                return $type;
            }
        }

        return false;
    }

    public function getEntityTypeByCode($code)
    {
        foreach ($this->getEntityTypes() as $type)
        {
            if (! is_object($type) || ! isset($type->code)) {
                Log::warning('MetaDataService encountered malformed entity type in getEntityTypeByCode.', [
                    'requested_code' => $code,
                    'item' => $this->describeItem($type),
                ]);
                continue;
            }

            if($type->code == $code)
            {
                return $type;
            }
        }

        return false;
    }

    public function getEntityTypeByIdOrCode($identifier)
    {
        $type = $this->getEntityTypeById($identifier);
        if($type === false)
        {
            $type = $this->getEntityTypeByCode($identifier);
        }

        return $type;
    }

    public function getAttributeSets()
    {
        if(self::$attributeSets === null)
        {
            self::$attributeSets = $this->attributeSetRepository->get();
        }

        if (! $this->isValidAttributeSetCollection(self::$attributeSets)) {
            Log::warning('MetaDataService detected malformed attributeSets metadata. Rebuilding cache.', [
                'items' => $this->describeCollection(self::$attributeSets),
            ]);
            self::$attributeSets = $this->attributeSetRepository->get();
        }

        return self::$attributeSets;
    }

    protected function isValidAttributeSetCollection($attributeSets): bool
    {
        $attributeSets = $this->normalizeMetadataItems($attributeSets);

        if (! is_iterable($attributeSets)) {
            return false;
        }

        foreach ($attributeSets as $attributeSet) {
            if (! is_object($attributeSet)) {
                return false;
            }

            if (! isset($attributeSet->id) || ! isset($attributeSet->code)) {
                return false;
            }
        }

        return true;
    }

    public function getAttributeSetById($id)
    {
        foreach ($this->getAttributeSets() as $set)
        {
            if (! is_object($set) || ! isset($set->id)) {
                Log::warning('MetaDataService encountered malformed attribute set in getAttributeSetById.', [
                    'requested_id' => $id,
                    'item' => $this->describeItem($set),
                ]);
                continue;
            }

            if($set->id == $id)
            {
                return $set;
            }
        }

        return false;
    }

    public function getAttributeSetByCode($code)
    {
        foreach ($this->getAttributeSets() as $set)
        {
            if (! is_object($set) || ! isset($set->code)) {
                Log::warning('MetaDataService encountered malformed attribute set in getAttributeSetByCode.', [
                    'requested_code' => $code,
                    'item' => $this->describeItem($set),
                ]);
                continue;
            }

            if($set->code == $code)
            {
                return $set;
            }
        }

        return false;
    }

    public function getAttributeSetByIdOrCode($identifier)
    {
        $set = $this->getAttributeSetById($identifier);
        if($set === false)
        {
            $set = $this->getAttributeSetByCode($identifier);
        }

        return $set;
    }

    public function getLocales()
    {
        if(self::$locales === null)
        {
            self::$locales = $this->localeRepository->get();
        }

        if (! $this->isValidLocaleCollection(self::$locales)) {
            Log::warning('MetaDataService detected malformed locales metadata. Rebuilding cache.', [
                'items' => $this->describeCollection(self::$locales),
            ]);
            self::$locales = $this->localeRepository->get();
        }

        return self::$locales;
    }

    protected function isValidLocaleCollection($locales): bool
    {
        $locales = $this->normalizeMetadataItems($locales);

        if (! is_iterable($locales)) {
            return false;
        }

        foreach ($locales as $locale) {
            if (! is_object($locale)) {
                return false;
            }

            if (! isset($locale->id) || ! isset($locale->code)) {
                return false;
            }
        }

        return true;
    }

    public function getLocaleById($id)
    {
        foreach ($this->getLocales() as $locale)
        {
            if (! is_object($locale) || ! isset($locale->id)) {
                Log::warning('MetaDataService encountered malformed locale in getLocaleById.', [
                    'requested_id' => $id,
                    'item' => $this->describeItem($locale),
                ]);
                continue;
            }

            if($locale->id == $id)
            {
                return $locale;
            }
        }

        return false;
    }

    public function getLocaleByCode($code)
    {
        foreach ($this->getLocales() as $locale)
        {
            if (! is_object($locale) || ! isset($locale->code)) {
                Log::warning('MetaDataService encountered malformed locale in getLocaleByCode.', [
                    'requested_code' => $code,
                    'item' => $this->describeItem($locale),
                ]);
                continue;
            }

            if($locale->code == $code)
            {
                return $locale;
            }
        }

        return false;
    }

    public function getLocaleByIdOrCode($identifier)
    {
        $locale = $this->getLocaleById($identifier);
        if($locale === false)
        {
            $locale = $this->getLocaleByCode($identifier);
        }

        return $locale;
    }

    protected function describeCollection($items): array
    {
        $normalizedItems = $this->normalizeMetadataItems($items);

        if (! is_iterable($normalizedItems)) {
            return [[
                'type' => gettype($items),
                'class' => is_object($items) ? get_class($items) : null,
            ]];
        }

        $description = [];

        foreach ($normalizedItems as $item) {
            $description[] = $this->describeItem($item);
        }

        return $description;
    }

    protected function describeItem($item): array
    {
        if (is_object($item)) {
            return [
                'type' => get_class($item),
                'id' => $item->id ?? null,
                'code' => $item->code ?? null,
            ];
        }

        return [
            'type' => gettype($item),
            'value' => $item,
        ];
    }

    protected function normalizeMetadataItems($items)
    {
        if ($items instanceof Collection) {
            return $items->getData();
        }

        if ($items instanceof Model) {
            return [$items];
        }

        if (is_object($items) && method_exists($items, 'getData')) {
            return $items->getData();
        }

        return $items;
    }

    public function getWorkflows()
    {
        if(self::$workflows === null)
        {
            self::$workflows = $this->workflowRepository->get();
        }

        return self::$workflows;
    }

    public function getWorkflowById($id)
    {
        foreach ($this->getWorkflows() as $workflow)
        {
            if($workflow->id == $id)
            {
                return $workflow;
            }
        }

        return false;
    }

    public function getWorkflowByName($name)
    {
        foreach ($this->getWorkflows() as $workflow)
        {
            if($workflow->name == $name)
            {
                return $workflow;
            }
        }

        return false;
    }

    public function getWorkflowByIdOrName($identifier)
    {
        $workflow = $this->getWorkflowById($identifier);
        if($workflow === false)
        {
            $workflow = $this->getWorkflowByName($identifier);
        }

        return $workflow;
    }

    public function getWorkflowPoints()
    {
        if(self::$workflowPoints === null)
        {
            self::$workflowPoints = $this->workflowPointRepository->get();
        }

        return self::$workflowPoints;
    }

    public function getWorkflowPointById($id)
    {
        foreach ($this->getWorkflowPoints() as $point)
        {
            if($point->id == $id)
            {
                return $point;
            }
        }

        return false;
    }

    public function getWorkflowPointByStatus($status)
    {
        foreach ($this->getWorkflowPoints() as $point)
        {
            if($point->status == $status)
            {
                return $point;
            }
        }

        return false;
    }

    public function getWorkflowPointByIdOrStatus($identifier)
    {
        $point = $this->getWorkflowPointById($identifier);
        if($point === false)
        {
            $point = $this->getWorkflowPointByStatus($identifier);
        }

        return $point;
    }
}
