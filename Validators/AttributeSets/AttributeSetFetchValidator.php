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

use Cookbook\Eav\Commands\AttributeSets\AttributeSetFetchCommand;
use Cookbook\Core\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Validator;


/**
 * AttributeSetFetchValidator class
 * 
 * Validating command for fetching attribute by ID
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetFetchValidator
{

	/**
	 * Set of rules for validating attribute set fetch
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

	/**
	 * Create new AttributeSetFetchValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->rules = [
			'id' => 'required|exists:attribute_sets,id'
		];

		$this->exception = new NotFoundException();
	}


	/**
	 * Validate AttributeSetFetchCommand
	 * 
	 * @param Cookbook\Eav\Commands\AttributeSets\AttributeSetFetchCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(AttributeSetFetchCommand $command)
	{
		
		$params = ['id' => $command->id];

		$validator = Validator::make($params, $this->rules);

		if($validator->fails())
		{
			$this->exception->addErrors($validator->errors()->toArray());
		}

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
	}
}