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
 * EntityFetchCommand class
 * 
 * Command for fetching entity
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityFetchCommand extends RepositoryCommand
{
	/**
	 * Create new EntityFetchCommand
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
		$locale = (!empty($this->params['locale']))?$this->params['locale']:null;
		$include = (!empty($this->params['include']))?$this->params['include']:[];
		$status = (!empty($this->params['status']))?$this->params['status']:null;
		return $this->repository->fetch($this->id, $include, $locale, $status);
	}

}