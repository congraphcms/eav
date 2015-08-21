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

use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Core\Exceptions\NotFoundException;


/**
 * AttributeSetDeleteValidator class
 * 
 * Validating command for deleting attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetDeleteValidator extends Validator
{

	/**
	 * Set of rules for validating attribute set
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Repository for attribute sets
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Create new AttributeSetDeleteValidator
	 * 
	 * @return void
	 */
	public function __construct(AttributeSetRepositoryContract $attributeSetRepository)
	{

		$this->attributeSetRepository = $attributeSetRepository;

		$this->rules = [
			'id' => 'required|exists:attribute_sets,id'
		];

		parent::__construct();
	}


	/**
	 * Validate RepositoryCommand
	 * 
	 * @param  Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(RepositoryCommand $command)
	{
		$attributeSet = $this->attributeSetRepository->fetch($command->id);

		if( ! $attributeSet )
		{
			throw new NotFoundException('No attribute set with that ID.');
		}
	}
}