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

use Cookbook\Contracts\EAV\AttributeRepositoryContract;
use Cookbook\Contracts\EAV\AttributeSetRepositoryContract;
use Cookbook\Contracts\EAV\EntityTypeRepositoryContract;
use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Contracts\Locales\LocaleRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Core\Validation\Validator;
use Cookbook\Eav\Managers\AttributeManager;

/**
 * EntityCreateValidator class
 *
 * Validating command for creating entities
 *
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityCreateValidator extends Validator
{

	/**
	 * Factory for field validators,
	 * makes appropriate field validator depending on field type
	 *
	 * @var Cookbook\Contracts\Eav\FieldValidatorFactoryContract
	 */
	protected $fieldValidatorFactory;

	/**
	 * Repository for entity types
	 * 
	 * @var Cookbook\Contracts\Eav\EntityTypeRepositoryContract
	 */
	protected $entityTypeRepository;

	/**
	 * Repository for attribute sets
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for attributes
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Repository for handling locales
	 * 
	 * @var Cookbook\Contracts\Locales\LocaleRepositoryContract
	 */
	protected $localeRepository;

	/**
	 * Attribute config manager
	 * 
	 * @var Cookbook\Eav\Managers\AttributeManager
	 */
	protected $attributeManager;

	/**
	 * Set of rules for validating attribute
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Set of rules for validating options
	 *
	 * @var array
	 */
	protected $optionRules;

	/**
	 * Create new AttributeCreateValidator
	 *
	 * @return void
	 */
	public function __construct(
		FieldValidatorFactoryContract $fieldValidatorFactory, 
		EntityTypeRepositoryContract $entityTypeRepository, 
		AttributeSetRepositoryContract $attributeSetRepository, 
		AttributeRepositoryContract $attributeRepository,
		LocaleRepositoryContract $localeRepository,
		AttributeManager $attributeManager)
	{
		$this->fieldValidatorFactory = $fieldValidatorFactory;
		$this->entityTypeRepository = $entityTypeRepository;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->attributeRepository = $attributeRepository;
		$this->localeRepository = $localeRepository;
		$this->attributeManager = $attributeManager;

		$this->rules = [
			'entity_type_id'        => ['required_without:entity_type', 'exists:entity_types,id' ],
			'entity_type'           => ['sometimes'],
			'entity_type.id'        => ['sometimes', 'exists:entity_types,id'],
			'attribute_set_id'      => ['required_without:attribute_set', 'exists:attribute_sets,id'],
			'attribute_set'         => ['sometimes'],
			'attribute_set.id'      => ['sometimes', 'exists:attribute_sets,id'],
			'locale'             	=> 'sometimes',
			'status'				=> ['sometimes'],
			'fields'                => 'array'
		];

		parent::__construct();

		$this->exception->setErrorKey('entity');
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
		$this->validateParams($command->params, $this->rules, true);

		if ($this->exception->hasErrors()) {
			throw $this->exception;
		}

		if ( ! isset($command->params['entity_type_id']) )
		{
			if( is_string($command->params['entity_type']) )
			{
				$entityType = $this->entityTypeRepository->get(['code' => $command->params['entity_type']]);
				if( empty($entityType) )
				{
					$this->exception->addErrors(['entity_type' => 'Invalid entity type.']);
					throw $this->exception;
				}
				$entityType = $entityType[0];
				$command->params['entity_type_id'] = $entityType->id;
			}
			elseif( is_array($command->params['entity_type']) && isset($command->params['entity_type']['id']))
			{
				$command->params['entity_type_id'] = $command->params['entity_type']['id'];
			}
			else
			{
				$this->exception->addErrors(['entity_type_id' => 'This is required.']);
				throw $this->exception;
			}
		}

		unset($command->params['entity_type']);

		if (! isset($command->params['attribute_set_id']))
		{
			if( is_string($command->params['attribute_set']) )
			{
				$attributeSet = $this->attributeSetRepository->get(['code' => $command->params['attribute_set']]);
				if( empty($attributeSet) )
				{
					$this->exception->addErrors(['attribute_set' => 'Invalid entity type.']);
					throw $this->exception;
				}
				$attributeSet = $attributeSet[0];
				$command->params['attribute_set_id'] = $attributeSet->id;
			}
			elseif( is_array($command->params['attribute_set']) && isset($command->params['attribute_set']['id']))
			{
				$command->params['attribute_set_id'] = $command->params['attribute_set']['id'];
			}
			else
			{
				$this->exception->addErrors(['attribute_set_id' => 'This is required.']);
				throw $this->exception;
			}
		}

		unset($command->params['attribute_set']);

		if( ! isset($entityType) )
		{
			$entityType = $this->entityTypeRepository->fetch($command->params['entity_type_id']);
		}

		if (! $entityType->multiple_sets && $command->params['attribute_set_id'] != $entityType->default_set_id) {
			$this->exception->setErrorKey('entity.attribute_set_id');
			$this->exception->addErrors(['Invalid attribute set.']);

			throw $this->exception;
		}

		$attributeSet = $this->attributeSetRepository->fetch($command->params['attribute_set_id']);
		if( isset($command->params['locale']) )
		{
			try
			{
				$locale = $this->localeRepository->fetch($command->params['locale']);
			}
			catch(NotFoundException $e)
			{
				$this->exception->addErrors(['locale' => 'Invalid locale.']);
				throw $this->exception;
			}
		}
		else
		{
			$locales = $this->localeRepository->get();
		}

		$attributeIds = [];
		$attributes = [];

		foreach ($attributeSet->attributes as $attribute) {
			$attributeIds[] = $attribute->id;
		}

		if (! empty($attributeIds)) {
			$attributes = $this->attributeRepository->get(['id' => ['in' => $attributeIds]]);
		}

		$attributesByCode = [];
		foreach ($attributes as $attribute) {
			$attributesByCode[$attribute->code] = $attribute;
			$attributeSettings = $this->attributeManager->getFieldType($attribute->field_type);

			$default_value = null;
			if($attributeSettings['has_options'])
			{
				foreach ($attribute->options as $option)
				{
					if($option->default)
					{
						$default_value = $option->value;
					}
				}
			}
			else
			{
				$default_value = $attribute->default_value;
			}

			if (! isset($command->params['fields'][$attribute->code]) )
			{
				if(empty($default_value))
				{
					if($attribute->required)
					{
						$this->exception->setErrorKey('entity.fields.' . $attribute->code);
						$this->exception->addErrors(['This field is required.']);
						continue;
					}
					
				}

				if( ! isset($locale) && $attribute->localized )
				{
					foreach ($locales as $l)
					{
						$command->params['fields'][$attribute->code][$l->code] = $default_value;
					}
					
				}
				else
				{
					$command->params['fields'][$attribute->code] = $default_value;
				}
				
			}

			if( ! isset($locale) && $attribute->localized )
			{
				if( ! is_array($command->params['fields'][$attribute->code]) )
				{
					$command->params['fields'][$attribute->code] = [];
				}

				foreach ($locales as $l)
				{

					if( ! isset($command->params['fields'][$attribute->code][$l->code]) )
					{
						if(empty($default_value))
						{
							if($attribute->required)
							{
								$this->exception->setErrorKey('entity.fields.' . $attribute->code . '.' . $l->code);
								$this->exception->addErrors(['This field is required.']);
								continue;
							}
						}
						$command->params['fields'][$attribute->code][$l->code] = $default_value;
					}

					$value = $command->params['fields'][$attribute->code][$l->code];

					$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);

					try {
						$fieldValidator->validateValue($value, $attribute);
					} catch (ValidationException $e) {
						$this->exception->setErrorKey('entity.fields.' . $attribute->code . '.' . $l->code);
						$this->exception->addErrors($e->getErrors());
					}
				}
			}
			else
			{
				$value = $command->params['fields'][$attribute->code];

				$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);

				try {
					$fieldValidator->validateValue($value, $attribute);
				} catch (ValidationException $e) {
					$this->exception->setErrorKey('entity.fields.' . $attribute->code);
					$this->exception->addErrors($e->getErrors());
				}
			}
			
		}

		foreach ($command->params['fields'] as $code => $value)
		{
			if (! array_key_exists($code, $attributesByCode))
			{
				$this->exception->setErrorKey('entity.fields.' . $code);
				$this->exception->addErrors(['Field doesn\'t exist.']);
			}
		}

		if ($this->exception->hasErrors())
		{
			throw $this->exception;
		}
	}
}
