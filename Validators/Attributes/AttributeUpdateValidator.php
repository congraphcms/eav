<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\Attributes;

use Cookbook\Eav\Commands\Attributes\AttributeUpdateCommand;
use Cookbook\Eav\Managers\AttributeManager;
use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;


/**
 * AttributeUpdateValidator class
 * 
 * Validating command for updating attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeUpdateValidator extends Validator
{

	/**
	 * Helper for attributes
	 * 
	 * @var Cookbook\Eav\Managers\AttributeManager
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
	 * @var Cookbook\Contracts\Eav\FieldValidatorFactoryContract
	 */
	protected $fieldValidatorFactory;

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
			'code'					=> ['sometimes', 'required', 'unique:attributes,code', 'regex:/^[0-9a-zA-Z-_]*$/'],
			// 'admin_label'			=> 'sometimes|required|between:3,100',
			// 'admin_notice'			=> 'max:1000',
			// 'field_type' 			=> 'required|in:' . implode(array_keys($this->availableFieldTypes), ','),
			'default_value'			=> '',
			// 'localized'				=> 'boolean',
			// 'unique'				=> 'boolean',
			'required'				=> 'sometimes|boolean',
			'filterable'			=> 'sometimes|boolean',
			'data'					=> 'sometimes',
			'options'				=> 'sometimes|array',
			'translations'			=> 'sometimes|array'
		];

		$this->optionRules = 
		[
			'id'					=> 'sometimes|required|integer',
			'locale' 				=> 'sometimes|required|integer',
			'label'					=> 'required|max:250',
			'value'					=> 'required|max:250',
			'is_default'			=> 'boolean',
			'sort_order' 			=> 'integer'
		];

		parent::__construct();

		$this->exception->setErrorKey('attribute');
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
		$attribute = $this->attributeRepository->fetch($command->id);

		$validator = $this->newValidator($command->params, $this->rules);

		if( isset($command->params['options']) )
		{
			$validator->each('options', $this->optionRules);
		}
		$this->setValidator($validator);

		$this->validateParams($command->params, null, true);
		
		$fieldValidator = $this->fieldValidatorFactory->make($attribute->field_type);

		try
		{
			$fieldValidator->validateAttributeForUpdate($command->params);
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