<?php 
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields\Node;

use Illuminate\Support\Facades\Config;
use Congraph\Eav\Fields\AbstractFieldHandler;
use Congraph\Eav\Facades\MetaData;
use stdClass;

/**
 * NodeFieldHandler class
 * 
 * Responsible for handling node field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class NodeFieldHandler extends AbstractFieldHandler {

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_integer';

	
	/**
	 * Parse value for database input
	 * 
	 * @param mixed $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function parseValue($value, $attribute, $locale, $params, $entity)
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
		// delete all values for provided entity, attribute and language
		$this	->db->table( $this->table )
				->where( 'attribute_id', '=', $attribute->id )
				->where( 'entity_id', '=', $entity->id )
				->delete();

		// delete all values for provided entity, attribute and language
		$this	->db->table( $this->table )
				->where( 'attribute_id', '=', $attribute->id )
				->where( 'value', '=', $entity->id )
				->delete();
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
		// delete all attribute values associated with provided attribute set
		$this->db->table($this->table)
				 ->where('attribute_id', '=', $attribute->id)
				 ->where('attribute_set_id', '=', $attributeSet->id)
				 ->delete();
		
		$entityIds = $this->db->table('entities')
							  ->where('attribute_set_id', '=', $attributeSet->id)
							  ->pluck('id');
		if( ! empty($entityIds) )
		{
			$this->db->table($this->table)
					 ->where('attribute_id', '=', $attribute->id)
					 ->whereIn('value', $entityIds)
					 ->delete();
		}
		
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
		// delete all attribute values associated with provided attribute set
		$this->db->table($this->table)
				 ->where('attribute_id', '=', $attribute->id)
				 ->where('entity_type_id', '=', $entityType->id)
				 ->delete();
		
		$entityIds = $this->db->table('entities')
							  ->where('entity_type_id', '=', $entityType->id)
							  ->pluck('id');
		if( ! empty($entityIds) )
		{
			$this->db->table($this->table)
					 ->where('attribute_id', '=', $attribute->id)
					 ->whereIn('value', $entityIds)
					 ->delete();
		}
	}

	public function onBeforeEntityGet($command)
	{

		$attributes = MetaData::getAttributes();
		$include = (isset($command->params['include']))?$command->params['include']: [];

		if(!is_array($include))
		{
			$include = explode(',', strval($include));
		}

		foreach ($include as &$r)
		{
			$r = ltrim(rtrim($r));
		}

		foreach ($attributes as $attribute)
		{
			if(!in_array($attribute->field_type, ['node', 'node_collection']))
			{
				continue;
			}

			$includeKey = 'fields.' . $attribute->code;
			$hasKey = false;
			
			foreach ($include as $relation)
			{
				if(substr($relation, 0, strlen($includeKey)) === $includeKey)
				{
					$hasKey = true;
					break;
				}
			}

			if(!$hasKey)
			{
				$include[] = $includeKey;
			}
		}

		if(!empty($include))
		{
			$command->params['include'] = $include;
		}
	}
}