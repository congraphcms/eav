<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Handlers\Commands\EntityTypes;


use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Bus\RepositoryCommandHandler;

/**
 * EntityTypeDeleteHandler class
 * 
 * Handling command for deleting entity type
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeDeleteHandler extends RepositoryCommandHandler
{

	/**
	 * Repository for handling attribute sets
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for handling entities
	 * 
	 * @var Cookbook\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;

	/**
	 * Create new EntityTypeDeleteHandler
	 * 
	 * @param ookbook\Contracts\Eav\EntityTypeRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(EntityTypeRepositoryContract $repository, AttributeSetRepositoryContract $attributeSetRepository, EntityRepositoryContract $entityRepository)
	{
		parent::__construct($repository);
		$this->attributeSetRepository = $attributeSetRepository;
		$this->entityRepository = $entityRepository;
	}

	/**
	 * Handle RepositoryCommand
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		$entityType = $this->repository->delete($command->id);

		$this->attributeSetRepository->deleteByEntityType($entityType);

		$this->entityRepository->deleteByEntityType($entityType);

		return $entityType;
	}
}