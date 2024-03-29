<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Validators\Attributes;

use Congraph\Eav\Commands\Attributes\AttributeUpdateCommand;
use Congraph\Eav\Managers\AttributeManager;
use Congraph\Contracts\Eav\FieldValidatorFactoryContract;
use Congraph\Contracts\Eav\AttributeRepositoryContract;
use Congraph\Core\Exceptions\ValidationException;
use Congraph\Core\Exceptions\NotFoundException;
use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Validation\Validator;


/**
 * AttributeUpdateValidator class
 * 
 * Validating command for updating attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeUpdateValidator extends Validator
{

	/**
	 * Helper for attributes
	 * 
	 * @var Congraph\Eav\Managers\AttributeManager
	 */
	protected $attributeManager;

	/**
	 * Array of available field_types for attribute
	 *
	 * @var array
	 */
	protected $availableFieldTypes;

	/**
	 * Factory for field validators,
	 * makes appropriate field validator depending on field type
	 *
	 * @var Congraph\Contracts\Eav\FieldValidatorFactoryContract
	 */
	protected $fieldValidatorFactory;

	/**
	 * Repository for attributes
	 * 
	 * @var Congraph\Contracts\Eav\AttributeRepositoryContract
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
	 * Create new AttributeUpdateValidator
	 * 
	 * @return void
	 */
	public function __construct(AttributeManager $attributeManager, FieldValidatorFactoryContract $fieldValidatorFactory, AttributeRepositoryContract $attributeRepository)
	{
		$this->attributeManager = $attributeManager;
		$this->fieldValidatorFactory = $fieldValidatorFactory;
		$this->attributeRepository = $attributeRepository;

		$this->availableFieldTypes = $this->attributeManager->getFieldTypes();

		$this->rules = [
			// 'id'					=> 'required|exists:attributes,id',
			// 'code'					=> ['sometimes', 'required', 'unique:attributes,code', 'regex:/^[0-9a-zA-Z-_]*$/'],
			'admin_label'			=> 'sometimes|max:100',
			'admin_notice'			=> 'sometimes|max:1000',
			// 'field_type' 			=> 'required|in:' . implode(array_keys($this->availableFieldTypes), ','),
			'default_value'			=> '',
			// 'localized'				=> 'boolean',
			// 'unique'				=> 'boolean',
			'required'				=> 'sometimes|boolean',
			'filterable'			=> 'sometimes|boolean',
			// 'searchable'			=> 'sometimes|boolean',
			'data'					=> 'sometimes',
			'options'				=> 'sometimes|array',
			'options.*.id'			=> 'sometimes|required|integer',
			'options.*.locale' 		=> 'sometimes|integer',
			'options.*.label'		=> 'required|max:250',
			'options.*.value'		=> 'required|max:250',
			'options.*.default'		=> 'boolean'
		];

		parent::__construct();

		$this->exception->setErrorKey('attribute');
	}


	/**
	 * Validate RepositoryCommand
	 * 
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(RepositoryCommand $command)
	{
		$attribute = $this->attributeRepository->fetch($command->id);

		$attributeSettings = $this->attributeManager->getFieldType($attribute->field_type);

		$command->params['id'] = $command->id;

		$validator = $this->newValidator($command->params, $this->rules, true);

		if( ! $attributeSettings['has_options'] )
		{
			if( ! empty($params['options']) )
			{
				$this->exception->addErrors(['options' => 'This attribute type can\'t have options.']);
			}
		}

		$this->setValidator($validator);

		$this->validateParams($command->params, null, true);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

		if( ! $attributeSettings['can_have_default_value'] )
		{
			$params['default_value'] = null;
		}

		if( ! $attributeSettings['can_be_filter'] )
		{
			if( ! empty($params['filterable']) )
			{
				$this->exception->addErrors(['filterable' => 'This attribute type can\'t be filterable.']);
			}
			$params['filterable'] = 0;
		}
		
		$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);

		try
		{
			$fieldValidator->validateAttributeForUpdate($command->params, $attribute);
		}
		catch(ValidationException $e)
		{
			$this->exception->addErrors($e->getErrors());
		}
		

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

	}
}