<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Commands\AttributeSets;

use Congraph\Contracts\Eav\AttributeSetRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * AttributeSetUpdateCommand class
 * 
 * Command for updating attribute set
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetUpdateCommand extends RepositoryCommand
{
	/**
	 * Create new AttributeSetUpdateCommand
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
		return $this->repository->update($this->id, $this->params);
	}

}