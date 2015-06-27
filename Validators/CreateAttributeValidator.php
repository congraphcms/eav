<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Validators;

use Cookbook\EAV\Commands\CreateAttributeCommand;
use Cookbook\EAV\Managers\AttributeManager;
use Cookbook\Contracts\EAV\FieldValidatorFactoryContract;
use Cookbook\Core\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;


/**
 * CreateAttributeValidator class
 * 
 * Validating command for creating attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CreateAttributeValidator
{

	/**
	 * Helper for attributes
	 * 
	 * @var Cookbook\EAV\Managers\AttributeManager
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
	 * @var Cookbook\Contracts\EAV\FieldValidatorFactoryContract
	 */
	protected $fieldValidatorFactory;

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
	 * Set of rules for validating translations
	 *
	 * @var array
	 */
	protected $translationRules;

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

	/**
	 * Create new CreateAttributeValidator
	 * 
	 * @return void
	 */
	public function __construct(AttributeManager $attributeManager, FieldValidatorFactoryContract $fieldValidatorFactory)
	{
		$this->attributeManager = $attributeManager;
		$this->fieldValidatorFactory = $fieldValidatorFactory;

		$this->availableFieldTypes = $this->attributeManager->getFieldTypes();

		$this->rules = [
			'code'					=> ['required', 'unique:attributes,code', 'regex:/^[0-9a-zA-Z-_]*$/'],
			'admin_label'			=> 'required|between:3,100',
			'admin_notice'			=> 'max:1000',
			'field_type' 			=> 'required|in:' . implode(array_keys($this->availableFieldTypes), ','),
			'default_value'			=> '',
			'localized'				=> 'boolean',
			'unique'				=> 'boolean',
			'required'				=> 'boolean',
			'filterable'			=> 'boolean',
			'status'				=> 'required|string',
			'data'					=> '',
			'options'				=> 'sometimes|array',
			'translations'			=> 'sometimes|array'
		];

		$this->optionRules = 
		[
			'locale' 				=> 'required|integer',
			'label'					=> 'required|max:250',
			'value'					=> 'required|max:250',
			'is_default'			=> 'boolean',
			'sort_order' 			=> 'integer'
		];

		$this->translationRules = 
		[
			'locale' 				=> 'required|integer',
			'label'					=> 'required|max:250',
			'description'			=> 'required|max:1000',
		];

		$this->exception = new ValidationException();

		$this->exception->setErrorKey('attribute');
	}


	/**
	 * Validate CreateAttributeCommand
	 * 
	 * @param Cookbook\EAV\Commands\CreateAttributeCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(CreateAttributeCommand $command)
	{
		$params = $command->request->all();

		$validator = Validator::make($params, $this->rules);

		if($validator->fails())
		{
			$this->exception->addErrors($validator->errors()->toArray());
		}

		if( isset($params['options']) )
		{
			foreach ($params['options'] as $key => $option) {
				$optionValidator = Validator::make($option, $this->optionRules);

				if($optionValidator->fails())
				{
					$this->exception->addErrors($optionValidator->errors()->toArray());
				}
			}
		}

		if( isset($params['translations']) )
		{
			foreach ($params['translations'] as $key => $translation) {
				$translationValidator = Validator::make($option, $this->translationRules);

				if($translationValidator->fails())
				{
					$this->exception->addErrors($translationValidator->errors()->toArray());
				}
			}
		}

		$fieldValidator = $this->fieldValidatorFactory->make($params['field_type']);

		try
		{
			$fieldValidator->validateAttributeForInsert($params);
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