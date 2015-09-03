<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\EntityTypes;

use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;

use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;


/**
 * EntityTypeDeleteValidator class
 * 
 * Validating command for deleting entity type
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeDeleteValidator extends Validator
{

	/**
	 * Repository for entity types
	 * 
	 * @var Cookbook\Contracts\Eav\EntityTypeRepositoryContract
	 */
	protected $entityTypeRepository;

	/**
	 * Create new EntityTypeDeleteValidator
	 * 
	 * @return void
	 */
	public function __construct(EntityTypeRepositoryContract $entityTypeRepository)
	{

		$this->entityTypeRepository = $entityTypeRepository;

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
		$entityType = $this->entityTypeRepository->fetch($command->id);

		if( ! $entityType )
		{
			throw new NotFoundException('No entity type with that ID.');
		}
	}
}