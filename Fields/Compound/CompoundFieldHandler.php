<?php 
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Compound;

use Illuminate\Support\Facades\Config;
use Cookbook\Eav\Fields\AbstractFieldHandler;
use Cookbook\Eav\Managers\AttributeManager;
use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Illuminate\Database\Connection;
use Cookbook\Core\Exceptions\BadRequestException;
use Cookbook\Eav\Facades\MetaData;
use Illuminate\Support\Facades\Event;
use \Exception;

/**
 * CompoundFieldHandler class
 * 
 * Responsible for handling compound field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CompoundFieldHandler extends AbstractFieldHandler {

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_text';

	protected static $waitingForMultiLocaleUpdate = false;


	/**
	 * Repository for entities
	 * 
	 * @var Cookbook\Contracts\Eav\EntityRepositoryContract
	 */
	public $entityRepository;


	/**
	 * Create new CompoundFieldHandler
	 * 
	 * @param Illuminate\Database\Connection 			$db
	 * @param Cookbook\Eav\Managers\AttributeManager 	$attributeManager
	 * @param string 									$table
	 *  
	 * @return void
	 */
	public function __construct(
		Connection $db, 
		AttributeManager $attributeManager, 
		AttributeRepositoryContract $attributeRepository, 
		EntityRepositoryContract $entityRepository)
	{
		// Inject dependencies
		$this->db = $db;
		$this->attributeManager = $attributeManager;
		$this->attributeRepository = $attributeRepository;
		$this->entityRepository = $entityRepository;

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
		// var_dump([$value, $params]);
		$inputs = $attribute->data->inputs;
		$value = $this->calculate($inputs, $locale, $params, $entity);
		$value = $this->getExpectedValue($value, $attribute);
		return $value;
	}

	protected function calculate($inputs, $locale, $params, $entity)
	{
		$localeCode = false;
		if($locale)
		{
			$localeCode = MetaData::getLocaleById($locale)->code;
		}
		$inputs = array_values($inputs);
		$reverseInputs = array_reverse($inputs);
		$provisionalValue = null;
		foreach ($reverseInputs as $key => $input)
		{
			switch ($input->type)
			{
				case 'literal':
					$provisionalValue = $input->value;
					break;
				case 'field':
					// somewhat sketchy (locales and stuff)
					
					// get field value
					$code = MetaData::getAttributeById($input->value)->code;
					$fieldValue = null;
					$takeFromEntity = true;
					if(array_key_exists('fields', $params) && is_array($params['fields']) && array_key_exists($code, $params['fields']))
					{
						// var_dump('calculate from params - ' . $localeCode . ' - ' . $locale);
						// var_dump($params['fields'][$code]);
						if($localeCode && is_array($params['fields'][$code]) && array_key_exists($localeCode, $params['fields'][$code]))
						{
							// var_dump('localized params');
							$fieldValue = $params['fields'][$code][$localeCode];
							$takeFromEntity = false;
						}
						else
						{
							// var_dump('flat params');
							$fieldValue = $params['fields'][$code];
							$takeFromEntity = false;
						}
						
					}

					if ($entity instanceof \Cookbook\Core\Repositories\Model && $takeFromEntity && property_exists($entity, $code))
					{
						// var_dump('calculate from entity - ' . $localeCode . ' - ' . $locale);
						// var_dump($entity->fields->$code);
						if($localeCode && is_array($entity->fields->$code))
						{
							// var_dump('localized entity');
							if(array_key_exists($localeCode, $entity->fields->$code))
							{
								$fieldValue = ($entity->fields->$code)[$localeCode];
							}
						}
						else
						{
							// var_dump('flat entity');
							$fieldValue = $entity->fields->$code;
						}
						
					}
					$provisionalValue = $fieldValue;
					break;
				case 'operator':
					switch ($input->value) 
					{
						case 'CONCAT':
							$remainingInputs = array_slice($inputs, 0, count($inputs) - 1 - $key);
							$fieldValue = $this->calculate($remainingInputs, $locale, $params, $entity);
							// var_dump('CONCAT values');
							// var_dump($fieldValue);
							// var_dump($provisionalValue);
							return $fieldValue . $provisionalValue;
							break;
						
						default:
							throw new BadRequestException('Invalid compound field operator');
							break;
					}
					break;
				default:
					throw new BadRequestException('Invalid compound field input');
					break;
			}
		}

		return $provisionalValue;
	}

	protected function getExpectedValue($value, $attribute)
	{
		$expectedValue = $attribute->data->expected_value;

		switch ($expectedValue)
		{
			case 'string':
				return strval($value);
				break;
			
			default:
				throw new BadRequestException('Invalid compound field expected value');
				break;
		}
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
		return $this->getExpectedValue($value, $attribute);
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

		Event::fire('cb.before.entity.field.insert', [$valueParams, $attribute, $attributeSettings, $entity]);

		if($attributeSettings['has_multiple_values'])
		{
			// sort_order counter
			$sort_order = 0;
			foreach ($valueParams['value'] as $value)
			{
				$singleValueParams = $valueParams;
				$singleValueParams['value'] = $value;
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

		Event::fire('cb.after.entity.field.insert', [$valueParams, $attribute, $attributeSettings, $entity]);
	}


	public function onValueUpdate($valueParams, $changedAttribute, $attributeSettings, $entity)
	{
		$attributes = MetaData::getAttributes();

		foreach ($attributes as $attribute)
		{
			if($attribute->field_type !== 'compound')
			{
				continue;
			}

			$inputs = $attribute->data->inputs;
			$needsChange = false;

			$localized = false;
			$attributesInvlolved = [];
			$attributesInvlolvedIds = [];

			foreach ($inputs as $input)
			{
				if($input['type'] !== 'field')
				{
					continue;
				}
				$inputAttribute = MetaData::getAttributeById($input['value']);
				$attributesInvlolved[] = $inputAttribute;
				$attributesInvlolvedIds[] = $inputAttribute->id;

				if($inputAttribute->localized)
				{
					$localized = true;
				}

				if($input['value'] != $changedAttribute->id)
				{
					continue;
				}
				$needsChange = true;
			}

			if(!$needsChange)
			{
				continue;
			}

			$compoundAttribute = $attribute;

			$attrSettings = $this->attributeManager->getFieldTypes()[$compoundAttribute->field_type];

			$newValueParams = [
				'entity_id' => $valueParams['entity_id'],
				'attribute_id' => $compoundAttribute->id,
				'entity_type_id' => $valueParams['entity_type_id'],
				'attribute_set_id' => $valueParams['attribute_set_id'],
				'value' => null
			];

			$updateSingleLocale = false;

			if(!$localized)
			{
				$newValueParams['locale_id'] = 0;
				$updateSingleLocale = true;
			}
			else
			{
				if($changedAttribute->$localized)
				{
					$newValueParams['locale_id'] = $valueParams['locale_id'];
					$updateSingleLocale = true;
				}
			}

			if($updateSingleLocale)
			{
				$this	->db->table( $this->table )
					->where( 'attribute_id', '=', $newValueParams['attribute_id'] )
					->where( 'entity_id', '=', $newValueParams['entity_id'] )
					->where( 'locale_id', '=', $newValueParams['locale_id'] )
					->delete();

				if($compoundAttribute->searchable)
				{
					$this->db->table('attribute_values_fulltext')
						->where( 'attribute_id', '=', $newValueParams['attribute_id'] )
						->where( 'entity_id', '=', $newValueParams['entity_id'] )
						->where( 'locale_id', '=', $newValueParams['locale_id'] )
						->delete();
				}
			}
			else
			{
				$this	->db->table( $this->table )
					->where( 'attribute_id', '=', $newValueParams['attribute_id'] )
					->where( 'entity_id', '=', $newValueParams['entity_id'] )
					->delete();

				if($compoundAttribute->searchable)
				{
					$this->db->table('attribute_values_fulltext')
						->where( 'attribute_id', '=', $newValueParams['attribute_id'] )
						->where( 'entity_id', '=', $newValueParams['entity_id'] )
						->delete();
				}

				$newValueParamsArray = [];

			}

			$parsedValue = $this->parseValue($newValueParams['value'], $compoundAttribute, $entity);
			$newValueParams['value'] = $parsedValue;

			Event::fire('cb.before.entity.field.update', [$newValueParams, $compoundAttribute, $attrSettings, $entity]);

			// delete all values for provided entity, attribute and language
			$this	->db->table( $this->table )
					->where( 'attribute_id', '=', $newValueParams['attribute_id'] )
					->where( 'entity_id', '=', $newValueParams['entity_id'] )
					->where( 'locale_id', '=', $newValueParams['locale_id'] )
					->delete();

			if($compoundAttribute->searchable)
			{
				$this->db->table('attribute_values_fulltext')
					->where( 'attribute_id', '=', $newValueParams['attribute_id'] )
					->where( 'entity_id', '=', $newValueParams['entity_id'] )
					->where( 'locale_id', '=', $newValueParams['locale_id'] )
					->delete();
			}

			// if this field type has multiple values and 
			// value is array give each value a sort_order
			// and then insert them separately into database
			if($attrSettings['has_multiple_values'])
			{
				if( ! is_array($newValueParams['value']) )
				{
					$newValueParams['value'] = [$newValueParams['value']];
				}

				// sort_order counter
				$sort_order = 0;


				foreach ($newValueParams['value'] as $value)
				{
					$singleValueParams = $newValueParams;
					$parsedValue = $this->parseValue($value, $compoundAttribute, $entity);
					$singleValueParams['value'] = $parsedValue;
					// give it a sort_order
					$singleValueParams['sort_order'] = $sort_order++;
					$this->db->table($this->table)->insert($singleValueParams);
					if($compoundAttribute->searchable)
					{
						$this->db->table('attribute_values_fulltext')->insert($singleValueParams);
					}
				}
			}
			else
			{
				$parsedValue = $this->parseValue($newValueParams['value'], $compoundAttribute, $entity);
				$newValueParams['value'] = $parsedValue;
				$this->db->table($this->table)->insert($newValueParams);
				if($compoundAttribute->searchable)
				{
					$this->db->table('attribute_values_fulltext')->insert($newValueParams);
				}
			}

			Event::fire('cb.after.entity.field.update', [$newValueParams, $compoundAttribute, $attrSettings, $entity]);
		}
	}

	public function onBeforeEntityUpdate($command)
	{
		$entity = $this->db->table('entities')->find($command->id);
		$attributeSet = MetaData::getAttributeSetById($entity->attribute_set_id);
		foreach ($attributeSet->attributes as $setAttribute)
		{
			$attribute = MetaData::getAttributeById($setAttribute->id);

			if($attribute->field_type !== 'compound')
			{
				continue;
			}

			if(!$attribute->localized)
			{
				$command->params['fields'][$attribute->code] = null;
				continue;
			}

			if(isset($command->params['locale']))
			{
				$command->params['fields'][$attribute->code] = null;
				foreach ($attribute->data->inputs as $input)
				{
					if($input->type != 'field')
					{
						continue;
					}

					$attr = MetaData::getAttributeById($input->value);
					if($attr && !$attr->localized && array_key_exists($attr->code, $command->params['fields']))
					{
						self::$waitingForMultiLocaleUpdate = true;
					}
				}
				continue;
			}
			
			$locales = MetaData::getLocales();
			$command->params['fields'][$attribute->code] = [];
			foreach ($locales as $locale)
			{
				$command->params['fields'][$attribute->code][$locale->code] = null;
			}
		}
	}

	public function onAfterEntityUpdate($command, $result)
	{
		if(!self::$waitingForMultiLocaleUpdate)
		{
			return;
		}

		self::$waitingForMultiLocaleUpdate = false;

		$updateParams = [
			'fields' => []
		];
		$locales = MetaData::getLocales();
		$attributeSet = MetaData::getAttributeSetById($result->attribute_set_id);
		foreach ($attributeSet->attributes as $setAttribute)
		{
			$attribute = MetaData::getAttributeById($setAttribute->id);

			if($attribute->field_type != 'compound' || !$attribute->localized)
			{
				continue;
			}

			$skipAttribute = true;
			foreach ($attribute->data->inputs as $input)
			{
				if($input->type != 'field')
				{
					continue;
				}

				$attr = MetaData::getAttributeById($input->value);
				if($attr && !$attr->localized && array_key_exists($attr->code, $command->params['fields']))
				{
					$skipAttribute = false;
					break;
				}
			}

			if($skipAttribute)
			{
				continue;
			}

			$updateParams['fields'][$attribute->code] = [];
			foreach ($locales as $locale)
			{
				$updateParams['fields'][$attribute->code][$locale->code] = null;
			}
		}

		if(empty($updateParams['fields']))
		{
			return;
		}
		// var_dump("COMPUND UPDATE");
		$this->entityRepository->update($result->id, $updateParams);
		$result = $this->entityRepository->fetch($result->id, [], $result->locale);

		return $result;
	}

}