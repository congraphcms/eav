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

use Cookbook\Eav\Commands\Attributes\AttributeFetchCommand;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;


/**
 * AttributeFetchValidator class
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
class AttributeFetchValidator extends Validator
{

	/**
	 * Set of rules for validating attribute fetch
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Create new AttributeFetchValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->rules = [
			'id' => 'required|exists:attributes,id'
		];

		parent::__construct();
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
		
		$params = ['id' => $command->id];

		$this->validateParams($params, $this->rules);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
	}
}