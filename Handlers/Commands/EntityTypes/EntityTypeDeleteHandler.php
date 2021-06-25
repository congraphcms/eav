<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Handlers\Commands\EntityTypes;


use Congraph\Contracts\Eav\AttributeSetRepositoryContract;
use Congraph\Contracts\Eav\EntityRepositoryContract;
use Congraph\Contracts\Eav\EntityTypeRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Bus\RepositoryCommandHandler;

/**
 * EntityTypeDeleteHandler class
 * 
 * Handling command for deleting entity type
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeDeleteHandler extends RepositoryCommandHandler
{

	/**
	 * Repository for handling attribute sets
	 * 
	 * @var Congraph\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for handling entities
	 * 
	 * @var Congraph\Contracts\Eav\EntityRepositoryContract
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
	 * @return void
	 */
	public function handle()
	{
		$entityType = $this->repository->delete($this->id);

		$this->attributeSetRepository->deleteByEntityType($entityType);

		$this->entityRepository->deleteByEntityType($entityType);

		return $entityType;
	}
}