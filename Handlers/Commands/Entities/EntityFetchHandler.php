<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Handlers\Commands\Entities;


use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommandHandler;
use Cookbook\Core\Bus\RepositoryCommand;

/**
 * EntityFetchHandler class
 * 
 * Handling command for fetching entity
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityFetchHandler extends RepositoryCommandHandler
{

	/**
	 * Create new EntityFetchHandler
	 * 
	 * @param Cookbook\Contracts\Eav\EntityRepositoryContract $repository
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
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		$include = (!empty($command->params['include']))?$command->params['include']:[];
		return $this->repository->fetch($command->id, $include);
	}
}