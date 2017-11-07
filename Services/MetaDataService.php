<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Services;

use Carbon\Carbon;
use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;
use Cookbook\Contracts\Locales\LocaleRepositoryContract;
use Cookbook\Contracts\Workflows\WorkflowRepositoryContract;
use Cookbook\Contracts\Workflows\WorkflowPointRepositoryContract;
use Cookbook\Core\Facades\Trunk;
use Cookbook\Core\Repositories\Collection;
use Cookbook\Core\Repositories\Model;
use Cookbook\Core\Repositories\UsesCache;
use Cookbook\Eav\Managers\AttributeManager;

use Cookbook\Eav\Traits\ElasticQueryBuilderTrait;


use Elasticsearch\ClientBuilder;
// use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * MetaDataService class
 *
 * Repository for fetching meta data for each request
 *
 * @uses        Illuminate\Database\Connection
 * @uses        Cookbook\Core\Repository\AbstractRepository
 * @uses        Cookbook\Contracts\Eav\AttributeHandlerFactoryContract
 * @uses        Cookbook\Eav\Managers\AttributeManager
 *
 * @author      Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright   Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package     cookbook/eav
 * @since       0.1.0-alpha
 * @version     0.1.0-alpha
 */
class MetaDataService//, UsesCache
{

    /**
     * Repository for handling attributes
     *
     * @var \Cookbook\Contracts\Eav\AttributeRepositoryContract
     */
    protected $attributeRepository;

    /**
     * Repository for handling attribute sets
     *
     * @var \Cookbook\Contracts\Eav\AttributeSetRepositoryContract
     */
    protected $attributeSetRepository;

    /**
     * Repository for handling entity types
     *
     * @var \Cookbook\Contracts\Eav\AttributeRepositoryContract
     */
    protected $entityTypeRepository;

    /**
     * Repository for handling workflows
     *
     * @var \Cookbook\Contracts\Workflows\WorkflowPointRepositoryContract
     */
    protected $workflowRepository;

    /**
     * Repository for handling workflow points
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




    protected static $attributes;

    protected static $attributeSets;

    protected static $entityTypes;

    protected static $workflows;

    protected static $workflowPoints;

    protected static $locales;

    /**
     * Create new MetaDataService
     *
     * @param Cookbook\Eav\Handlers\AttributeHandlerFactoryContract $attributeHandlerFactory
     * @param Cookbook\Eav\Managers\AttributeManager $attributeManager
     * @param Cookbook\Contracts\Eav\AttributeSetRepositoryContract $attributeSetRepository
     * @param Cookbook\Contracts\Eav\AttributeRepositoryContract $attributeRepository
     * @param Cookbook\Contracts\Eav\EntityTypeRepositoryContract $entityTypeRepository
     * @param Cookbook\Contracts\Workflows\WorkflowPointRepositoryContract $workflowPointRepository
     * @param Cookbook\Contracts\Locales\LocaleRepositoryContract $localeRepository
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
            // self::$attributes = $this->attributeRepository->get();
            self::$attributes = Cache::rememberForever('attributes', function() {
                return $this->attributeRepository->get();
            });
        }

        return self::$attributes;
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
            // self::$entityTypes = $this->entityTypeRepository->get();
            self::$entityTypes = Cache::rememberForever('entityTypes', function() {
                return $this->entityTypeRepository->get();
            });
        }

        return self::$entityTypes;
    }

    public function getEntityTypeById($id)
    {
        foreach ($this->getEntityTypes() as $type)
        {
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
            // self::$attributeSets = $this->attributeSetRepository->get();
            self::$attributeSets = Cache::rememberForever('attributeSets', function() {
                return $this->attributeSetRepository->get();
            });
        }

        return self::$attributeSets;
    }

    public function getAttributeSetById($id)
    {
        foreach ($this->getAttributeSets() as $set)
        {
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
            // self::$locales = $this->localeRepository->get();
            self::$locales = Cache::rememberForever('locales', function() {
                return $this->localeRepository->get();
            });
        }

        return self::$locales;
    }

    public function getLocaleById($id)
    {
        foreach ($this->getLocales() as $locale)
        {
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

    public function getWorkflows()
    {
        if(self::$workflows === null)
        {
            // self::$workflows = $this->workflowRepository->get();
            self::$workflows = Cache::rememberForever('workflows', function() {
                return $this->workflowRepository->get();
            });
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
            // self::$workflowPoints = $this->workflowPointRepository->get();
            self::$workflowPoints = Cache::rememberForever('workflowPoints', function() {
                return $this->workflowPointRepository->get();
            });
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