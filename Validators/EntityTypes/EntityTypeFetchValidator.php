<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Validators\EntityTypes;

use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Validation\Validator;

use Congraph\Contracts\Eav\EntityTypeRepositoryContract;


/**
 * EntityTypeFetchValidator class
 * 
 * Validating command for fetching entity type
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeFetchValidator extends Validator
{

	/**
	 * Repository for entity types
	 * 
	 * @var Congraph\Contracts\Eav\EntityTypeRepositoryContract
	 */
	protected $entityTypeRepository;

	/**
	 * Create new EntityTypeFetchValidator
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
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(RepositoryCommand $command)
	{
		$entityType = $this->entityTypeRepository->fetch($command->id);
	}
}