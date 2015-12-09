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

use Cookbook\Eav\Commands\Attributes\AttributeCreateCommand;
use Cookbook\Eav\Managers\AttributeManager;
use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;
use InvalidArgumentException;

/**
 * AttributeCreateValidator class
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
class AttributeCreateValidator extends Validator
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
	public function __construct(AttributeManager $attributeManager, FieldValidatorFactoryContract $fieldValidatorFactory)
	{
		$this->attributeManager = $attributeManager;
		$this->fieldValidatorFactory = $fieldValidatorFactory;

		$this->availableFieldTypes = $this->attributeManager->getFieldTypes();

		$this->rules = [
			'code'					=> ['required', 'unique:attributes,code', 'regex:/^[0-9a-zA-Z-_]*$/'],
			'admin_label'			=> 'sometimes|max:100',
			'admin_notice'			=> 'sometimes|max:1000',
			'field_type' 			=> 'required|in:' . implode(array_keys($this->availableFieldTypes), ','),
			'default_value'			=> '',
			'localized'				=> 'boolean',
			'unique'				=> 'boolean',
			'required'				=> 'boolean',
			'filterable'			=> 'boolean',
			'searchable'			=> 'boolean',
			'data'					=> '',
			'options'				=> 'sometimes|array'
		];

		$this->optionRules = 
		[
			'locale' 				=> 'sometimes|integer',
			'label'					=> 'required|max:250',
			'value'					=> 'required|max:250',
			'default'				=> 'boolean'
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

		$validator = $this->newValidator($command->params, $this->rules, true);

		if( isset($command->params['options']) )
		{
			$validator->each('options', $this->optionRules);
		}
		$this->setValidator($validator);

		$this->validateParams($command->params, null, true);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

		try
		{
			$attributeSettings = $this->attributeManager->getFieldType($command->params['field_type']);
		}
		catch(InvalidArgumentException $e)
		{
			$this->exception->addErrors(['field_type' => $e->getMessage()]);
			throw $this->exception;
		}

		if( ! $attributeSettings['has_options'] )
		{
			if( ! empty($params['options']) )
			{
				$this->exception->addErrors(['options' => 'This attribute type can\'t have options.']);
			}
			$params['options'] = [];
		}

		if( ! $attributeSettings['can_have_default_value'] )
		{
			if( isset($params['default_value']) && ! is_null($params['default_value']) )
			{
				$this->exception->addErrors(['default_value' => 'This attribute type can\'t have default value.']);
			}
			$params['default_value'] = null;
		}

		if( ! $attributeSettings['can_be_unique'] )
		{
			if( ! empty($params['unique']) )
			{
				$this->exception->addErrors(['unique' => 'This attribute type can\'t be unique.']);
			}
			$params['unique'] = 0;
		}
		if( ! $attributeSettings['can_be_localized'] )
		{
			if( ! empty($params['localized']) )
			{
				$this->exception->addErrors(['localized' => 'This attribute type can\'t be localized.']);
			}
			$params['localized'] = 0;
		}
		if( ! $attributeSettings['can_be_filter'] )
		{
			if( ! empty($params['filterable']) )
			{
				$this->exception->addErrors(['filterable' => 'This attribute type can\'t be filterable.']);
			}
			$params['filterable'] = 0;
		}
		if( ! $attributeSettings['can_be_searchable'] )
		{
			if( ! empty($params['searchable']) )
			{
				$this->exception->addErrors(['searchable' => 'This attribute type can\'t be searchable.']);
			}
			$params['searchable'] = 0;
		}

		if($this->exception->hasErrors())
		{
			throw $this->exception;
		}

		$fieldValidator = $this->fieldValidatorFactory->make($command->params['field_type']);

		try
		{
			$fieldValidator->validateAttributeForInsert($command->params);
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