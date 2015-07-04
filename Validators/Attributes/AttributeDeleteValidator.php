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

use Cookbook\Eav\Commands\Attributes\AttributeDeleteCommand;
use Cookbook\Core\Exceptions\NotFoundException;
use Illuminate\Support\Facades\Validator;


/**
 * AttributeDeleteValidator class
 * 
 * Validating command for deleting attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeDeleteValidator
{

	/**
	 * Set of rules for validating attribute
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
	 * Create new AttributeDeleteValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{

		$this->rules = [
			'id' => 'required|exists:attributes,id'
		];

		$this->exception = new NotFoundException();
	}


	/**
	 * Validate AttributeDeleteCommand
	 * 
	 * @param Cookbook\Eav\Commands\Attributes\AttributeDeleteCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(AttributeDeleteCommand $command)
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