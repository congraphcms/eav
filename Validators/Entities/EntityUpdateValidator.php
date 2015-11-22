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
use Cookbook\Contracts\Locales\LocaleRepositoryContract;
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
	 * Repository for handling locales
	 * 
	 * @var Cookbook\Contracts\Locales\LocaleRepositoryContract
	 */
	protected $localeRepository;

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
		AttributeRepositoryContract $attributeRepository,
		LocaleRepositoryContract $localeRepository)
	{
		$this->fieldValidatorFactory = $fieldValidatorFactory;
		$this->entityRepository = $entityRepository;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->attributeRepository = $attributeRepository;
		$this->localeRepository = $localeRepository;

		$this->rules = [
			'locale'             	=> 'sometimes',
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
		

		$locale_id = (!empty($command->params['locale']))?$command->params['locale']:null;

		$entity = $this->entityRepository->fetch($command->id, [], $locale_id);

		if( ! empty($locale_id) )
		{
			try
			{
				$locale = $this->localeRepository->fetch($locale_id);
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

		$this->validateParams($command->params, $this->rules, true);

		if ($this->exception->hasErrors()) {
			throw $this->exception;
		}

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

			$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);

			if( ! isset($locale) && $attribute->localized )
			{
				if( ! is_array($command->params['fields'][$attribute->code]) )
				{
					$this->exception->setErrorKey('entity.fields.' . $attribute->code);
					$this->exception->addErrors(['Invalid value format.']);
					continue;
				}

				foreach ($locales as $l)
				{

					if( isset($command->params['fields'][$attribute->code][$l->code]) )
					{
						$value = $command->params['fields'][$attribute->code][$l->code];

						try {
							$fieldValidator->validateValue($value, $attribute);
						} catch (ValidationException $e) {
							$this->exception->setErrorKey('entity.fields.' . $attribute->code . '.' . $l->code);
							$this->exception->addErrors($e->getErrors());
						}
					}

					
				}
			}
			else
			{
				$value = $command->params['fields'][$attribute->code];

				try {
					$fieldValidator->validateValue($value, $attribute);
				} catch (ValidationException $e) {
					$this->exception->setErrorKey('entity.fields.' . $attribute->code);
					$this->exception->addErrors($e->getErrors());
				}
			}
		}

		foreach ($command->params['fields'] as $code => $value) {
			if (! array_key_exists($code, $attributesByCode)) {
				$this->exception->setErrorKey('entities.fields.' . $code);
				$this->exception->addErrors(['Field doesn\'t exist.']);
				continue;
			}

			if(is_array($value) && ! isset($locale) && $attributesByCode[$code]->localized)
			{
				foreach ($value as $loc => $value)
				{
					$fault = true;
					foreach ($locales as $l)
					{
						if($l->code == $loc)
						{
							$fault = false;
						}
					}

					if($fault)
					{
						$this->exception->setErrorKey('entities.fields.' . $code . '.' . $loc);
						$this->exception->addErrors(['Locale doesn\'t exist.']);
					}
				}
			}
		}

		if ($this->exception->hasErrors()) {
			throw $this->exception;
		}
	}
}
