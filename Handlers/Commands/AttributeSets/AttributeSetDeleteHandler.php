<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Handlers\Commands\AttributeSets;


use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Bus\RepositoryCommandHandler;

/**
 * AttributeSetDeleteHandler class
 * 
 * Handling command for deleting attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetDeleteHandler extends RepositoryCommandHandler
{

	/**
	 * Repository for handling entities
	 * 
	 * @var Cookbook\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;


	/**
	 * Create new AttributeSetDeleteHandler
	 * 
	 * @param Cookbook\Contracts\Eav\AttributeSetRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(AttributeSetRepositoryContract $repository, EntityRepositoryContract $entityRepository)
	{
		parent::__construct($repository);
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
		$attributeSet = $this->repository->delete($command->id);

		$this->entityRepository->deleteByAttributeSet($attributeSet);

		return $attributeSet;
	}
}