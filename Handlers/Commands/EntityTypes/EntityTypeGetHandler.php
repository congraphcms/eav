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


use Congraph\Contracts\Eav\EntityTypeRepositoryContract;
use Congraph\Core\Bus\RepositoryCommandHandler;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * EntityTypeGetHandler class
 * 
 * Handling command for getting entity types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeGetHandler extends RepositoryCommandHandler
{

	/**
	 * Create new EntityTypeGetHandler
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
		return $this->repository->get(
			(!empty($this->params['filter']))?$this->params['filter']:[],
			(!empty($this->params['offset']))?$this->params['offset']:0,
			(!empty($this->params['limit']))?$this->params['limit']:0,
			(!empty($this->params['sort']))?$this->params['sort']:[],
			(!empty($this->params['include']))?$this->params['include']:[]
		);
	}
}