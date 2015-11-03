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
		AttributeManager $attributeManager)
	{
		$this->fieldValidatorFactory = $fieldValidatorFactory;
		$this->entityTypeRepository = $entityTypeRepository;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->attributeRepository = $attributeRepository;
		$this->attributeManager = $attributeManager;

		$this->rules = [
			'entity_type_id'        => ['sometimes', 'exists:entity_types,id'],
			'type'                  => ['sometimes', 'exists:entity_types,code', 'required_without_all:entity_type_id,entity_type'],
			'entity_type'           => ['sometimes', 'array'],
			'entity_type.id'        => ['sometimes', 'exists:entity_types,id', 'required_with:entity_type'],
			'attribute_set_id'      => ['sometimes', 'exists:attribute_sets,id', 'required_without:attribute_set'],
			'attribute_set'         => ['sometimes', 'array'],
			'attribute_set.id'      => ['sometimes', 'exists:attribute_sets,id', 'required_with:attribute_set'],
			'locale_id'             => 'integer',
			'fields'                => 'array'
		];

		parent::__construct();

		$this->exception->setErrorKey('entities');
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

		if (! isset($comand->params['entity_type_id'])) {
			if (isset($command->params['entity_type']) && is_array($command->params['entity_type']) && isset($command->params['entity_type']['id'])) {
				$command->params['entity_type_id'] = $command->params['entity_type']['id'];
			}

			if (isset($command->params['type'])) {
				$entityTypes = $this->entityTypeRepository->get(['code' => $command->params['type']]);
				$entityType = $entityTypes[0];
				$command->params['entity_type_id'] = $entityType->id;
			}
		}

		unset($command->params['type']);
		unset($command->params['entity_type']);

		if (! isset($comand->params['attribute_set_id'])) {
			$command->params['attribute_set_id'] = $command->params['attribute_set']['id'];
		}

		unset($command->params['attribute_set']);

		if (! $entityType->multiple_sets && $command->params['attribute_set_id'] != $entityType->default_set_id) {
			$this->exception->setErrorKey('entities.attribute_set_id');
			$this->exception->addErrors(['Invalid attribute set.']);

			throw $this->exception;
		}

		$attributeSet = $this->attributeSetRepository->fetch($command->params['attribute_set_id']);

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
			if (! isset($command->params['fields'][$attribute->code]) )
			{

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

				if(empty($default_value))
				{
					if($attribute->required)
					{
						$this->exception->setErrorKey('entities.fields.' . $attribute->code);
						$this->exception->addErrors(['This field is required.']);
						
					}
					continue;
				}

				$command->params['fields'][$attribute->code] = $default_value;
			}

			$value = $command->params['fields'][$attribute->code];

			$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);

			try {
				$fieldValidator->validateValue($value, $attribute);
			} catch (ValidationException $e) {
				$this->exception->setErrorKey('entities.fields.' . $attribute->code);
				$this->exception->addErrors($e->getErrors());
			}
		}

		foreach ($command->params['fields'] as $code => $value) {
			if (! array_key_exists($code, $attributesByCode)) {
				$this->exception->setErrorKey('entities.fields.' . $code);
				$this->exception->addErrors(['Field doesn\'t exist.']);
			}
		}

		if ($this->exception->hasErrors()) {
			var_dump($this->exception->getErrors());
			throw $this->exception;
		}
	}
}
