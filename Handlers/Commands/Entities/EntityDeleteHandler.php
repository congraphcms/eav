<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Handlers\Commands\Entities;


use Congraph\Contracts\Eav\EntityRepositoryContract;
use Congraph\Core\Bus\RepositoryCommandHandler;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * EntityDeleteHandler class
 * 
 * Handling command for deleting entity
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityDeleteHandler extends RepositoryCommandHandler
{

	/**
	 * Create new EntityDeleteHandler
	 * 
	 * @param Congraph\Contracts\Eav\EntityRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(EntityRepositoryContract $repository)
	{
		parent::__construct($repository);
	}

	/**
	 * Handle RepositoryCommand
	 * 
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		$entity = $this->repository->delete($command->id);

		return $entity;
	}
}