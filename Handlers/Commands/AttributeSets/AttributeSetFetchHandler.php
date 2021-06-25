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
 * AttributeSetFetchHandler class
 * 
 * Handling command for fetching attribute set by ID
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetFetchHandler extends RepositoryCommandHandler
{

	/**
	 * Create new AttributeSetFetchHandler
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
	 * @return void
	 */
	public function handle()
	{
		$include = (!empty($this->params['include']))?$this->params['include']:[];
		return $this->repository->fetch($this->id, $include);
	}
}