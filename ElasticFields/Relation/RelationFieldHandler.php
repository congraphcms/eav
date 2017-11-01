<?php 
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\ElasticFields\Relation;

use Cookbook\Eav\ElasticFields\AbstractFieldHandler;
use Cookbook\Eav\Managers\AttributeManager;
use Cookbook\Eav\Repositories\EntityElasticRepository;
use Elasticsearch\ClientBuilder;
use stdClass;

/**
 * RelationFieldHandler class
 * 
 * Responsible for handling relation field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RelationFieldHandler extends AbstractFieldHandler {

	/**
	 * Entity Repository
	 * 
	 * @var Cookbook\Eav\Repositories\EntityElasticRepository
	 */
	public $entityRepository;


	/**
	 * Create new RelationFieldHandler
	 * 
	 * @param Illuminate\Database\Connection 			$db
	 * @param Cookbook\Eav\Managers\AttributeManager 	$attributeManager
	 * @param string 									$table
	 *  
	 * @return void
	 */
	public function __construct(
		ClientBuilder $elasticClientBuilder, 
		AttributeManager $attributeManager,
		EntityElasticRepository $entityRepository)
	{

		parent::__construct($elasticClientBuilder, $attributeManager);

		$this->entityRepository = $entityRepository;
	}


	/**
	 * Parse value for database input
	 * 
	 * @param mixed $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function parseValue($value, $attribute)
	{
		if(empty($value))
		{
			return null;
		}
		if(is_array($value))
		{

			$value = $value['id'];
		}
		
		return $value;
	}

	/**
	 * Format value for output
	 * 
	 * @param mixed $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function formatValue($value, $attribute)
	{
		if(empty($value))
		{
			return null;
		}
		$relation = new stdClass();
		$relation->id = $value;
		$relation->type = 'entity';
		return $relation;
	}

	/**
	 * Delete values in database for entity
	 * 
	 * @param object $valueParams
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function deleteByEntity($entity, $attribute)
	{
		
	}

	/**
	 * Clean all related values and set entries for given attribute set
	 * 
	 * Takes attribute set that needs to be deleted,
	 * and deletes all related values and set entries
	 * 
	 * @param object $attributeSet
	 * @param object $attribute
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function deleteByAttributeSet($attributeSet, $attribute)
	{
		
	}

	/**
	 * Clean all related values for given entity type
	 * 
	 * Takes attribute set that needs to be deleted,
	 * and deletes all related values and set entries
	 * 
	 * @param object $entityType
	 * @param object $attribute
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function deleteByEntityType($entityType, $attribute)
	{
		
	}
}