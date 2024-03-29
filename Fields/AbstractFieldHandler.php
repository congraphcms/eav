<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields;

use Congraph\Contracts\Eav\AttributeRepositoryContract;
use Congraph\Contracts\Eav\FieldHandlerContract;
use Congraph\Core\Traits\ErrorManagerTrait;
use Congraph\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Event;

/**
 * Abstract Field Handler class
 * 
 * Base class for all feild handlers
 * 
 * @uses  		Congraph\Core\Traits\ErrorManagerTrait
 * @uses  		Congraph\Eav\Managers\AttributeManager
 * @uses 		Illuminate\Database\Connection
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class AbstractFieldHandler implements FieldHandlerContract
{
	use ErrorManagerTrait;

	/**
	 * The database connection to use.
	 *
	 * @var Illuminate\Database\Connection
	 */
	protected $db;

	/**
	 * AttributeManager
	 * 
	 * @var AttributeManager
	 */
	public $attributeManager;

	/**
	 * Repository for attributes
	 * 
	 * @var Congraph\Contracts\Eav\AttributeRepositoryContract
	 */
	public $attributeRepository;

	/**
	 * Attribute value table name
	 * 
	 * @var string
	 */
	protected $table;


	/**
	 * Create new AbstractAttributeHandler
	 * 
	 * @param Illuminate\Database\Connection 			$db
	 * @param Congraph\Eav\Managers\AttributeManager 	$attributeManager
	 * @param string 									$table
	 *  
	 * @return void
	 */
	public function __construct(Connection $db, AttributeManager $attributeManager, AttributeRepositoryContract $attributeRepository)
	{
		// Inject dependencies
		$this->db = $db;
		$this->attributeManager = $attributeManager;
		$this->attributeRepository = $attributeRepository;

		// Init empty MessagBag object for errors
		$this->setErrors();
	}

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
		return $value;
	}

	/**
	 * Parse value for database input
	 * 
	 * @param mixed $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function parseFilter($filter, $attribute)
	{
		return $this->parseValue($filter, $attribute, null, null, null);
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
		return $value;
	}

	/**
	 * Parse attribute for database input
	 * 
	 * @param array $attribute
	 * 
	 * @return object
	 */
	public function parseAttribute($attribute)
	{
		return $attribute;
	}

	/**
	 * Format attribute for output
	 * 
	 * @param object $attribute
	 * 
	 * @return object
	 */
	public function formatAttribute($attribute)
	{
		return $attribute;
	}


	/**
	 * Insert value to database
	 * 
	 * Takes attribute value params and attribute definition
	 * 
	 * @param array $valueParams
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function insert($valueParams, $attribute, $params, $entity)
	{
		$attributeSettings = $this->attributeManager->getFieldTypes()[$attribute->field_type];
		// var_dump($valueParams['value']);

		if($attributeSettings['has_multiple_values'])
		{
			if( ! is_array($valueParams['value']) )
			{
				$valueParams['value'] = [$valueParams['value']];
			}

			foreach ($valueParams['value'] as &$value)
			{
				$parsedValue = $this->parseValue($value, $attribute, $valueParams['locale_id'], $params, $entity);
				$value = $parsedValue;
			}
		}
		else
		{
			$parsedValue = $this->parseValue($valueParams['value'], $attribute, $valueParams['locale_id'], $params, $entity);
			$valueParams['value'] = $parsedValue;
		}
		// var_dump($valueParams['value']);
		Event::dispatch('cb.before.entity.field.insert', [$valueParams, $attribute, $attributeSettings, $params, $entity]);

		if($attributeSettings['has_multiple_values'])
		{
			// sort_order counter
			$sort_order = 0;
			foreach ($valueParams['value'] as $v)
			{
				$singleValueParams = $valueParams;
				$singleValueParams['value'] = $v;
				$singleValueParams['sort_order'] = $sort_order++;
				// var_dump($singleValueParams);
				$this->db->table($this->table)->insert($singleValueParams);
				if($attribute->searchable)
				{
					$this->db->table('attribute_values_fulltext')->insert($singleValueParams);
				}
			}
		}
		else
		{
			$this->db->table($this->table)->insert($valueParams);
			if($attribute->searchable)
			{
				$this->db->table('attribute_values_fulltext')->insert($valueParams);
			}
		}

		Event::dispatch('cb.after.entity.field.insert', [$valueParams, $attribute, $attributeSettings, $params, $entity]);
	}


	/**
	 * Update value in database
	 * 
	 * Takes attribute value params and attribute definition
	 * 
	 * @param array $valueParams
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function update($valueParams, $attribute, $params, $entity)
	{

		$attributeSettings = $this->attributeManager->getFieldTypes()[$attribute->field_type];

		if($attributeSettings['has_multiple_values'])
		{
			if( ! is_array($valueParams['value']) )
			{
				$valueParams['value'] = [$valueParams['value']];
			}

			foreach ($valueParams['value'] as &$value)
			{
				$parsedValue = $this->parseValue($value, $attribute, $valueParams['locale_id'], $params, $entity);
				$value = $parsedValue;
			}
		}
		else
		{
			$parsedValue = $this->parseValue($valueParams['value'], $attribute, $valueParams['locale_id'], $params, $entity);
			$valueParams['value'] = $parsedValue;
		}

		Event::dispatch('cb.before.entity.field.update', [$valueParams, $attribute, $attributeSettings, $params, $entity]);

		// delete all values for provided entity, attribute and language
		$this	->db->table( $this->table )
				->where( 'attribute_id', '=', $valueParams['attribute_id'] )
				->where( 'entity_id', '=', $valueParams['entity_id'] )
				->where( 'locale_id', '=', $valueParams['locale_id'] )
				->delete();

		if($attribute->searchable)
		{
			$this->db->table('attribute_values_fulltext')
				->where( 'attribute_id', '=', $valueParams['attribute_id'] )
				->where( 'entity_id', '=', $valueParams['entity_id'] )
				->where( 'locale_id', '=', $valueParams['locale_id'] )
				->delete();
		}

		// if this field type has multiple values and 
		// value is array give each value a sort_order
		// and then insert them separately into database
		if($attributeSettings['has_multiple_values'])
		{
			// sort_order counter
			$sort_order = 0;
			foreach ($valueParams['value'] as $v)
			{
				$singleValueParams = $valueParams;
				$singleValueParams['value'] = $v;
				$singleValueParams['sort_order'] = $sort_order++;
				$this->db->table($this->table)->insert($singleValueParams);
				if($attribute->searchable)
				{
					$this->db->table('attribute_values_fulltext')->insert($singleValueParams);
				}
			}
		}
		else
		{
			$this->db->table($this->table)->insert($valueParams);
			if($attribute->searchable)
			{
				$this->db->table('attribute_values_fulltext')->insert($valueParams);
			}
		}

		Event::dispatch('cb.after.entity.field.update', [$valueParams, $attribute, $attributeSettings, $params, $entity]);
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
		if($attribute->searchable)
		{
			$this->db->table('attribute_values_fulltext')
				->where( 'attribute_id', '=', $attribute->id )
				->where( 'entity_id', '=', $entity->id )
				->delete();
		}
	}

	/**
	 * Delete values in database for entity and locale
	 * 
	 * @param object $entity
	 * @param object $locale
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function deleteByEntityAndLocale($entity, $locale, $attribute)
	{
		// delete all values for provided entity, attribute and language
		$this	->db->table( $this->table )
				->where( 'attribute_id', '=', $attribute->id )
				->where( 'entity_id', '=', $entity->id )
				->where( 'locale_id', '=', $locale->id )
				->delete();
		if($attribute->searchable)
		{
			$this->db->table('attribute_values_fulltext')
				->where( 'attribute_id', '=', $attribute->id )
				->where( 'entity_id', '=', $entity->id )
				->where( 'locale_id', '=', $locale->id )
				->delete();
		}
	}

	/**
	 * Clean all related values and set entries for given attribute
	 * 
	 * Takes attribute that needs to be deleted,
	 * and deletes all related values and set entries
	 * 
	 * @param object $attribute
	 * 
	 * @return boolean
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function deleteByAttribute($attribute)
	{
		// delete all attribute values associated with provided attribute
		$this->db->table($this->table)->where('attribute_id', '=', $attribute->id)->delete();
		if($attribute->searchable)
		{
			$this->db->table('attribute_values_fulltext')->where( 'attribute_id', '=', $attribute->id )->delete();
		}
		
		return true;
	}

	/**
	 * Clean all related values for given attribute option
	 * 
	 * Takes attribute option that needs to be deleted,
	 * and deletes all related values
	 * 
	 * @param object $option
	 * 
	 * @return boolean
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function deleteByOption($option)
	{
		// delete all attribute values that are associated with provided attribute option
		$this	->db->table($this->table)
				->where('attribute_id', '=', $option->attribute_id)
				->where('value', '=', $option->id)
				->delete();
		$this->db->table('attribute_values_fulltext')
			->where('attribute_id', '=', $option->attribute_id)
			->where('value', '=', $option->id)
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
		if($attribute->searchable)
		{
			$this->db->table('attribute_values_fulltext')
				->where('attribute_id', '=', $attribute->id)
				->where('attribute_set_id', '=', $attributeSet->id)
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
		if($attribute->searchable)
		{
			$this->db->table('attribute_values_fulltext')
				->where('attribute_id', '=', $attribute->id)
				->where('entity_type_id', '=', $entityType->id)
				->delete();
		}
	}
	

	/**
	 * Add filters to query for field
	 * 
	 * @param object $query
	 * @param object $attribute
	 * @param $filter
	 * 
	 * @return boolean
	 */
	public function filterEntities($query, $attribute, $filter, $locale = null)
	{
		$code = $attribute->code;

		if( is_array($filter) && array_key_exists('m', $filter))
		{
			$query = $query->join('attribute_values_fulltext as filter_' . $code, function($join) use($attribute, $filter)
				{
					$join->on('filter_' . $attribute->code . '.entity_id', '=', 'entities.id');
					$join->on('filter_' . $attribute->code . '.attribute_id', '=', $this->db->raw($attribute->id));
				}
			);
		}
		else
		{
			$query = $query->join($this->table . ' as filter_' . $code, function($join) use($attribute, $filter)
				{
					$join->on('filter_' . $attribute->code . '.entity_id', '=', 'entities.id');
					$join->on('filter_' . $attribute->code . '.attribute_id', '=', $this->db->raw($attribute->id));
				}
			);
		}

		if( ! is_array($filter) )
		{

			$filter = $this->parseFilter($filter, $attribute);
			
			$query = $query->where('filter_' . $code . '.value', '=', $filter);
		}
		else
		{
			$query = $this->parseFilterOperator($query, $attribute, $filter);
		}

		if( ! is_null($locale) && $attribute->localized )
		{
			$query->where('filter_' . $code . '.locale_id', '=', $locale->id);
		}

		return $query;
	}

	protected function parseFilterOperator($query, $attribute, $filter)
	{
		$code = $attribute->code;

		foreach ($filter as $operator => $value) 
		{	
			if(is_array($value))
			{
				foreach ($value as &$singleValue)
				{
					$singleValue = $this->parseFilter($singleValue, $attribute);
				}
			}
			else
			{
				$value = $this->parseFilter($value, $attribute);
			}
			
			switch ($operator) 
			{
				case 'e':
					$query = $query->where('filter_' . $code . '.value', '=', $value);
					break;
				case 'ne':
					$query = $query->where('filter_' . $code . '.value', '!=', $value);
					break;
				case 'lt':
					$query = $query->where('filter_' . $code . '.value', '<', $value);
					break;
				case 'lte':
					$query = $query->where('filter_' . $code . '.value', '<=', $value);
					break;
				case 'gt':
					$query = $query->where('filter_' . $code . '.value', '>', $value);
					break;
				case 'gte':
					$query = $query->where('filter_' . $code . '.value', '>=', $value);
					break;
				case 'in':
					$query = $query->whereIn('filter_' . $code . '.value', $value);
					break;
				case 'nin':
					$query = $query->whereNotIn('filter_' . $code . '.value', $value);
					break;
				case 'm':
					$query = $query->whereRaw('MATCH (filter_' . $code . '.value) AGAINST (?)' , array($value));
					break;
				
				default:
					throw new BadRequestException(['Filter operator not supported.']);
					break;
			}
		}

		return $query;
	}

	/**
	 * Sort entities by attribute
	 * 
	 * @param object $query
	 * @param object $attribute
	 * @param $direction
	 * 
	 * @return boolean
	 */
	public function sortEntities($query, $attribute, $direction, $locale = null)
	{
		$code = $attribute->code;
		$query = $query->leftJoin(
			$this->table . ' as sort_' . $code, 
			function($join) use($attribute, $locale)
			{
				$join->on('sort_' . $attribute->code . '.entity_id', '=', 'entities.id');
				$join->on('sort_' . $attribute->code . '.attribute_id', '=', $this->db->raw($attribute->id));
				if( ! is_null($locale) && $attribute->localized )
				{
					$join->on('sort_' . $attribute->code . '.locale_id', '=', $this->db->raw($locale->id));
				}
			}
		);
		
		$query = $query->orderBy('sort_' . $code . '.value', $direction);
		$query = $query->groupBy('sort_' . $code . '.value');

		return $query;
	}


}