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
use Cookbook\Core\Bus\RepositoryCommandHandler;
use Cookbook\Core\Bus\RepositoryCommand;

/**
 * AttributeSetUpdateHandler class
 * 
 * Handling command for updating attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetUpdateHandler extends RepositoryCommandHandler
{

	/**
	 * Create new AttributeSetUpdateHandler
	 * 
	 * @param Cookbook\Contracts\Eav\AttributeSetRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(AttributeSetRepositoryContract $repository)
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
		return $this->repository->update($command->id, $command->params);
	}
}