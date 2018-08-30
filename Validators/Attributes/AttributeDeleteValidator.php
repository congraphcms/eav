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

use Congraph\Eav\Commands\Attributes\AttributeDeleteCommand;
use Congraph\Contracts\Eav\AttributeRepositoryContract;
use Congraph\Core\Exceptions\NotFoundException;
use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Validation\Validator;


/**
 * AttributeDeleteValidator class
 * 
 * Validating command for deleting attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeDeleteValidator extends Validator
{

	/**
	 * Set of rules for validating attribute
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Repository for attributes
	 * 
	 * @var Congraph\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AttributeDeleteValidator
	 * 
	 * @return void
	 */
	public function __construct(AttributeRepositoryContract $attributeRepository)
	{

		$this->attributeRepository = $attributeRepository;

		$this->rules = [
			'id' => 'required|exists:attributes,id'
		];

		parent::__construct();
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
		
		if( ! $attribute )
		{
			throw new NotFoundException('No attribute with that ID.');
		}
	}
}