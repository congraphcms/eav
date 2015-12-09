<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\Entities;

use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;
use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Contracts\Locales\LocaleRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Exceptions\BadRequestException;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Validation\Validator;
use Cookbook\Eav\Managers\AttributeManager;


/**
 * EntityGetValidator class
 * 
 * Validating command for getting entities
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityGetValidator extends Validator
{

	/**
	 * Available fields for sorting
	 *
	 * @var array
	 */
	protected $availableSorting;

	/**
	 * Available fields for filtering
	 *
	 * @var array
	 */
	protected $availableFilters;

	/**
	 * Default sorting criteria
	 *
	 * @var array
	 */
	protected $defaultSorting;

	/**
	 * Repository for attributes
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Repository for attribute sets
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for entities
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $entityTypeRepository;

	/**
	 * Repository for locales
	 * 
	 * @var Cookbook\Contracts\Locales\LocaleRepositoryContract
	 */
	protected $localeRepository;
	
	/**
	 * Factory for field validators,
	 * makes appropriate field validator depending on field type
	 *
	 * @var Cookbook\Contracts\Eav\FieldValidatorFactoryContract
	 */
	protected $fieldValidatorFactory;

	/**
	 * Helper for attributes
	 * 
	 * @var Cookbook\Eav\Managers\AttributeManager
	 */
	protected $attributeManager;
	

	/**
	 * Create new AttributeGetValidator
	 * 
	 * @return void
	 */
	public function __construct(
		AttributeManager $attributeManager, 
		FieldValidatorFactoryContract $fieldValidatorFactory, 
		EntityTypeRepositoryContract $entityTypeRepository,
		AttributeSetRepositoryContract $attributeSetRepository,
		AttributeRepositoryContract $attributeRepository,
		LocaleRepositoryContract $localeRepository
	)
	{
		$this->attributeRepository = $attributeRepository;

		$this->availableSorting = [
			'id',
			'type',
			'entity_type_id',
			'attribute_set',
			'attribute_set_id',
			'created_at'
		];

		$this->availableFilters = [
			'id' 					=> ['e', 'ne', 'lt', 'lte', 'gt', 'gte', 'in', 'nin'],
			'type_id'				=> ['e', 'ne', 'lt', 'lte', 'gt', 'gte', 'in', 'nin'],
			'type'					=> ['e', 'ne', 'in', 'nin'],
			'entity_type'			=> ['e', 'ne', 'in', 'nin'],
			'entity_type_id'		=> ['e', 'ne', 'in', 'nin'],
			'attribute_set'			=> ['e', 'ne', 'in', 'nin'],
			'attribute_set_id'		=> ['e', 'ne', 'in', 'nin'],
			'created_at'			=> ['lt', 'lte', 'gt', 'gte']
		];

		$this->defaultSorting = ['-created_at'];

		parent::__construct();
		
		$this->fieldValidatorFactory = $fieldValidatorFactory;
		$this->attributeManager = $attributeManager;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->entityTypeRepository = $entityTypeRepository;
		$this->localeRepository = $localeRepository;
	}


	/**
	 * Validate RepositoryCommand
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(RepositoryCommand $command)
	{
		if( isset($command->params['locale']) )
		{
			try
			{
				$this->localeRepository->fetch($command->params['locale']);
			}
			catch(NotFoundException $e)
			{
				$e = new BadRequestException();
				$e->setErrorKey('locale.');
				$e->addErrors('Invalid locale.');

				throw $e;
			}
		}
		if( ! empty($command->params['filter']) )
		{
			$this->validateFilters($command->params['filter']);

			if( ! isset($command->params['filter']['entity_type_id']) && isset($command->params['filter']['type']) )
			{
				$types = [];
				if( ! is_array($command->params['filter']['type']) )
				{
					$types[] = $command->params['filter']['type'];
				}
				else
				{
					foreach ($command->params['filter']['type'] as $operator => $filter)
					{
						if( ! is_array($filter) )
						{
							$filter = explode(',', strval($filter));
						}

						foreach ($filter as $type)
						{
							$types[] = $type;
						}
					}
				}
				if( ! empty($types) )
				{
					$entityTypes = $this->entityTypeRepository->get([ 'code' => ['in' => $types] ]);

					$entityTypesByCode = [];
					foreach ($entityTypes as $entityType)
					{
						$entityTypesByCode[$entityType->code] = $entityType;
					}
					if( ! is_array($command->params['filter']['type']) )
					{
						if(array_key_exists($command->params['filter']['type'], $entityTypesByCode))
						{
							$command->params['filter']['entity_type_id'] = $entityTypesByCode[$command->params['filter']['type']]->id;
						}
						else
						{
							$command->params['filter']['entity_type_id'] = 0;
						}
					}
					else
					{
						foreach ($command->params['filter']['type'] as $operator => $filter)
						{
							if($operation == 'in' || $operation == 'nin')
							{
								if( ! is_array($filter) )
								{
									$filter = explode(',', strval($filter));
								}
								foreach ($filter as $type)
								{
									if( ! is_array($command->params['filter']['entity_type_id']) )
									{
										$command->params['filter']['entity_type_id'] = [];
									}
									if( ! is_array($command->params['filter']['entity_type_id'][$operator]) )
									{
										$command->params['filter']['entity_type_id'][$operator] = [];
									}
									if(array_key_exists($type, $entityTypesByCode))
									{
										$command->params['filter']['entity_type_id'][$operator] = array_merge($command->params['filter']['entity_type_id'][$operator], [$entityTypesByCode[$type]->id]);
									}
									else
									{
										$command->params['filter']['entity_type_id'][$operator] = array_merge($command->params['filter']['entity_type_id'][$operator], [0]);
									}
								}
							}

							if(array_key_exists($filter, $entityTypesByCode))
							{
								$command->params['filter']['entity_type_id'] = $entityTypesByCode[$filter]->id;
							}
							else
							{
								$command->params['filter']['entity_type_id'] = 0;
							}
						}
					}
				}
			}

			if( ! isset($command->params['filter']['attribute_set_id']) && isset($command->params['filter']['attribute_set']) )
			{
				$sets = [];
				if( ! is_array($command->params['filter']['attribute_set']) )
				{
					$sets[] = $command->params['filter']['attribute_set'];
				}
				else
				{
					foreach ($command->params['filter']['attribute_set'] as $operator => $filter)
					{
						if( ! is_array($filter) )
						{
							$filter = explode(',', strval($filter));
						}

						foreach ($filter as $set)
						{
							$sets[] = $set;
						}
					}
				}
				if( ! empty($sets) )
				{
					$attributeSets = $this->attributeSetRepository->get([ 'code' => ['in' => $sets] ]);

					$attributeSetsByCode = [];
					foreach ($attributeSets as $attributeSet)
					{
						$attributeSetsByCode[$attributeSet->code] = $attributeSet;
					}
					if( ! is_array($command->params['filter']['attribute_set']) )
					{
						if(array_key_exists($command->params['filter']['attribute_set'], $attributeSetsByCode))
						{
							$command->params['filter']['attribute_set_id'] = $attributeSetsByCode[$command->params['filter']['attribute_set']]->id;
						}
						else
						{
							$command->params['filter']['attribute_set_id'] = 0;
						}
					}
					else
					{
						foreach ($command->params['filter']['attribute_set'] as $operator => $filter)
						{
							if($operation == 'in' || $operation == 'nin')
							{
								if( ! is_array($filter) )
								{
									$filter = explode(',', strval($filter));
								}
								foreach ($filter as $attribute_set)
								{
									if( ! is_array($command->params['filter']['attribute_set_id']) )
									{
										$command->params['filter']['attribute_set_id'] = [];
									}
									if( ! is_array($command->params['filter']['attribute_set_id'][$operator]) )
									{
										$command->params['filter']['attribute_set_id'][$operator] = [];
									}
									if(array_key_exists($attribute_set, $attributeSetsByCode))
									{
										$command->params['filter']['attribute_set_id'][$operator] = array_merge($command->params['filter']['attribute_set_id'][$operator], [$attributeSetsByCode[$attribute_set]->id]);
									}
									else
									{
										$command->params['filter']['attribute_set_id'][$operator] = array_merge($command->params['filter']['attribute_set_id'][$operator], [0]);
									}
								}
							}

							if(array_key_exists($filter, $attributeSetsByCode))
							{
								$command->params['filter']['attribute_set_id'] = $attributeSetsByCode[$filter]->id;
							}
							else
							{
								$command->params['filter']['attribute_set_id'] = 0;
							}
						}
					}
				}
			}

			unset($command->params['filter']['type']);
			unset($command->params['filter']['attribute_set']);
		}

		if( empty($command->params['offset']) )
		{
			$command->params['offset'] = 0;
		}
		if( empty($command->params['limit']) )
		{
			$command->params['limit'] = 0;
		}
		$this->validatePaging($command->params['offset'], $command->params['limit']);
		
		if( ! empty($command->params['sort']) )
		{
			$this->validateSorting($command->params['sort']);
		}
		
		// if( ! empty($command->params['include']) )
		// {
		// 	$this->validateInclude($command->params['include']);
		// }
	}

	protected function validateFilters(&$filters)
	{
		if( ! empty($filters) )
		{
			if(is_string($filters))
			{
				$objFilters = json_decode($filters, true);
 				if(json_last_error() == JSON_ERROR_NONE)
 				{
 					$filters = $objFilters;
 				}
 				else
 				{
 					$e = new BadRequestException();
					$e->setErrorKey('filter');
					$e->addErrors('Invalid filter format.');

					throw $e;
 				}
			}
			$fieldFilters = [];
			foreach ($filters as $field => &$filter)
			{

				// check for field filters
				// 
				if(substr( $field, 0, 7 ) === "fields.")
				{
					$code = substr($field, 7);
					$fieldFilters[$code] = $filter;
					continue;
				}

				if($field = 's')
				{
					$filter = strval($filter);
					continue;
				}


				if( ! array_key_exists($field, $this->availableFilters) )
				{
					$e = new BadRequestException();
					$e->setErrorKey('filter.' . $field);
					$e->addErrors('Filtering by \'' . $field . '\' is not allowed.');

					throw $e;
				}

				if( ! is_array($filter) )
				{
					if( ! in_array('e', $this->availableFilters[$field]) )
					{
						$e = new BadRequestException();
						$e->setErrorKey('filter.' . $field);
						$e->addErrors('Filter operation is not allowed.');

						throw $e;
					}

					continue;
				}

				foreach ($filter as $operation => &$value) {
					if( ! in_array($operation, $this->availableFilters[$field]) )
					{
						$e = new BadRequestException();
						$e->setErrorKey('filter.' . $field);
						$e->addErrors('Filter operation is not allowed.');

						throw $e;
					}

					if($operation == 'in' || $operation == 'nin')
					{
						if( ! is_array($value) )
						{
							$value = explode(',', strval($value));
						}
					}
					else
					{
						if( is_array($value) || is_object($value))
						{
							$e = new BadRequestException();
							$e->setErrorKey('filter.' . $field);
							$e->addErrors('Invalid filter.');
						}
					}
				}
			}

			if( ! empty($fieldFilters) )
			{
				$attributes = $this->attributeRepository->get(['code' => ['in'=>array_keys($fieldFilters)]]);
				$attributeCodes = [];
				foreach ($attributes as $attribute)
				{
					$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);
					$fieldValidator->validateFilter($fieldFilters[$attribute->code], $attribute);
					$filters['fields.' . $attribute->code] = $fieldFilters[$attribute->code];
					$attributeCodes[] = $attribute->code;
				}

				foreach ($fieldFilters as $code => $filter)
				{
					if( ! in_array($code, $attributeCodes) )
					{
						$e = new BadRequestException();
						$e->setErrorKey('filter.fields.' . $code);
						$e->addErrors('Filtering by \'' . $code . '\' is not allowed.');

						throw $e;
					}
				}
			}

			return;
		}

		$filters = [];
	}

	protected function validatePaging(&$offset = 0, &$limit = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);
	}

	protected function validateSorting(&$sort)
	{
		if( empty($sort) )
		{
			$sort = $this->defaultSorting;
			return;
		}

		if( ! is_array($sort) )
		{
			$sort = explode(',', strval($sort));
		}

		$fieldSorting = [];
		foreach ($sort as $criteria)
		{
			if( empty($criteria) )
			{
				continue;
			}

			if( $criteria[0] === '-' )
			{
				$criteria = substr($criteria, 1);
			}

			if(substr( $criteria, 0, 7 ) === "fields.")
			{
				$code = substr($criteria, 7);
				$fieldSorting[] = $code;
				continue;
			}

			if( ! in_array($criteria, $this->availableSorting) )
			{
				$e = new BadRequestException();
				$e->setErrorKey('sort.' . $criteria);
				$e->addErrors('Sorting by \'' . $criteria . '\' is not allowed.');

				throw $e;
			}
		}

		if( ! empty($fieldSorting) )
		{
			$attributes = $this->attributeRepository->get(['code' => ['in'=>$fieldSorting]]);
			$attributesByCode = [];
			foreach ($attributes as $attribute)
			{
				$attributeSettings = $this->attributeManager->getFieldType($attribute->field_type);
				if( ! isset($attributeSettings['sortable']) || ! $attributeSettings['sortable'] )
				{
					$e = new BadRequestException();
					$e->setErrorKey('sort.fields.' . $attribute->code);
					$e->addErrors('Sorting by \'' . $attribute->code . '\' is not allowed.');

					throw $e;
				}

				$attributeCodes[] = $attribute->code;
			}

			foreach ($fieldSorting as $code)
			{
				if( ! in_array($code, $attributeCodes) )
				{
					$e = new BadRequestException();
					$e->setErrorKey('sort.fields.' . $code);
					$e->addErrors('Sorting by \'' . $code . '\' is not allowed.');

					throw $e;
				}
			}
		}
	}
}