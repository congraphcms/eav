<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Commands\Entities;

use Congraph\Contracts\Eav\EntityRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * EntityUpdateCommand class
 * 
 * Command for updating entity
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityUpdateCommand extends RepositoryCommand
{
	/**
	 * Create new EntityUpdateCommand
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
	 * @return void
	 */
	public function handle()
	{
		return $this->repository->update($this->id, $this->params);
	}

}