<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Handlers\Commands\AttributeSets;


use Congraph\Contracts\Eav\AttributeSetRepositoryContract;
use Congraph\Core\Bus\RepositoryCommandHandler;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * AttributeSetGetHandler class
 * 
 * Handling command for getting attribute sets
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetGetHandler extends RepositoryCommandHandler
{

	/**
	 * Create new AttributeSetGetHandler
	 * 
	 * @param Congraph\Contracts\Eav\AttributeSetRepositoryContract $repository
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
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		return $this->repository->get(
			(!empty($command->params['filter']))?$command->params['filter']:[],
			(!empty($command->params['offset']))?$command->params['offset']:0,
			(!empty($command->params['limit']))?$command->params['limit']:0,
			(!empty($command->params['sort']))?$command->params['sort']:[],
			(!empty($command->params['include']))?$command->params['include']:[]
		);
	}
}