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
use Cookbook\Contracts\EAV\EntityRepositoryContract;
use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Core\Validation\Validator;

/**
 * EntityUpdateValidator class
 *
 * Validating command for updating entities
 *
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityUpdateValidator extends Validator
{

	/**
	 * Factory for field validators,
	 * makes appropriate field validator depending on field type
	 *
	 * @var Cookbook\Contracts\Eav\FieldValidatorFactoryContract
	 */
	protected $fieldValidatorFactory;

	/**
	 * Repository for entities
	 * 
	 * @var Cookbook\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;

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
		EntityRepositoryContract $entityRepository, 
		AttributeSetRepositoryContract $attributeSetRepository, 
		AttributeRepositoryContract $attributeRepository)
	{
		$this->fieldValidatorFactory = $fieldValidatorFactory;
		$this->entityRepository = $entityRepository;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->attributeRepository = $attributeRepository;

		$this->rules = [
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
		$entity = $this->entityRepository->fetch($command->id);

		$this->validateParams($command->params, $this->rules, true);

		if ($this->exception->hasErrors()) {
			throw $this->exception;
		}

		$command->params['entity_type_id'] = $entity->entity_type_id;
		$command->params['attribute_set_id'] = $entity->attribute_set_id;

		$attributeSet = $this->attributeSetRepository->fetch($entity->attribute_set_id, ['attributes']);

		$attributesByCode = [];
		foreach ($attributeSet->attributes as $attr)
		{
			$attribute = $this->attributeRepository->fetch($attr->id);
			if (! isset($command->params['fields'][$attribute->code]))
			{
				continue;
			}

			$attributesByCode[$attribute->code] = $attribute;

			$value = $command->params['fields'][$attribute->code];

			$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);

			try {
				$fieldValidator->validateValue($value, $attribute, $command->id);
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
			throw $this->exception;
		}
	}
}
