<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\AttributeSets;

use Cookbook\Eav\Commands\AttributeSets\AttributeSetCreateCommand;
use Cookbook\Core\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;


/**
 * AttributeSetCreateValidator class
 * 
 * Validating command for creating attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetCreateValidator
{


	/**
	 * Set of rules for validating attribute set
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Set of rules for validating set attributes
	 *
	 * @var array
	 */
	protected $attributeRules;

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

	/**
	 * Create new AttributeSetCreateValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{

		$this->rules = [
			'code'					=> 'required|unique:attribute_sets,code',
			'entity_type_id'		=> 'required|integer|exists:entity_types,id',
			'name'					=> 'required',
		];

		$this->attributeRules = 
		[
			'id'			=> 'required|integer|exists:attributes,id'
		];

		$this->exception = new ValidationException();

		$this->exception->setErrorKey('attribute-sets');
	}


	/**
	 * Validate AttributeSetCreateCommand
	 * 
	 * @param Cookbook\Eav\Commands\AttributeSets\AttributeSetCreateCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(AttributeSetCreateCommand $command)
	{
		$params = $command->request->all();

		$validator = Validator::make($params, $this->rules);

		if($validator->fails())
		{
			$this->exception->addErrors($validator->errors()->toArray());
		}

		if( isset($params['attributes']) )
		{
			foreach ($params['attributes'] as $key => $attribute) {
				$attributeValidator = Validator::make($attribute, $this->attributeRules);

				if($attributeValidator->fails())
				{
					$this->exception->setErrorKey('attribute-sets.attributes.' . $key);
					$this->exception->addErrors($attributeValidator->errors()->toArray());
				}
			}
		}

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
	}
}