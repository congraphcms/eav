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


use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommandHandler;
use Cookbook\Core\Bus\RepositoryCommand;

/**
 * EntityTypeCreateHandler class
 * 
 * Handling command for creating entity type
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeCreateHandler extends RepositoryCommandHandler
{

	/**
	 * Create new EntityTypeCreateHandler
	 * 
	 * @param ookbook\Contracts\Eav\EntityTypeRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(EntityTypeRepositoryContract $repository)
	{
		parent::__construct($repository);
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
		return $this->repository->create($command->params);
	}
}