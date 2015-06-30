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

use Cookbook\EAV\Commands\AttributeSetUpdateCommand;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Core\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Validator;


/**
 * AttributeSetUpdateValidator class
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
class AttributeSetUpdateValidator
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
	 * Create new AttributeSetUpdateValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->rules = [
			'id'					=> 'required|exists:attribute_sets,id'
		];

		$this->rules = [
			'slug'					=> 'required|unique:attribute_sets,slug',
			'name'					=> 'required',
		];

		$this->attributeRules = 
		[
			'attribute_id'			=> 'required|integer|exists:attributes,id',
			'sort_order' 			=> 'integer'
		];

		$this->exception = new ValidationException();

		$this->exception->setErrorKey('attribute-sets');
	}


	/**
	 * Validate AttributeSetUpdateCommand
	 * 
	 * @param Cookbook\EAV\Commands\AttributeSetUpdateCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(AttributeSetUpdateCommand $command)
	{
		$idValidator = Validator::make(['id' => $command->id], $this->idRules);

		if($idValidator->fails())
		{
			throw new NotFoundException($idValidator->errors()->toArray());
		}

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