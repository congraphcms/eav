<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\Entities;

use Cookbook\Contracts\EAV\EntityRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Core\Validation\Validator;

/**
 * EntityFetchValidator class
 *
 * Validating command for fetching entities
 *
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityFetchValidator extends Validator
{

	/**
	 * Repository for entities
	 * 
	 * @var Cookbook\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;

	/**
	 * Create new EntityFetchValidator
	 *
	 * @return void
	 */
	public function __construct(EntityRepositoryContract $entityRepository)
	{
		$this->entityRepository = $entityRepository;

		parent::__construct();

		$this->exception->setErrorKey('entities');
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
		// $entity = $this->entityRepository->fetch($command->id);
	}
}
