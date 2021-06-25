<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Commands\EntityTypes;

use Congraph\Contracts\Eav\EntityTypeRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * EntityTypeFetchCommand class
 * 
 * Command for fetching entity type
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeFetchCommand extends RepositoryCommand
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
	 * @return void
	 */
	public function handle()
	{
		$include = (!empty($this->params['include']))?$this->params['include']:[];
		return $this->repository->fetch($this->id, $include);
	}

}