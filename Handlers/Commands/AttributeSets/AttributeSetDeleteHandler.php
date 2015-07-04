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
use Cookbook\Eav\Commands\AttributeSets\AttributeSetDeleteCommand;


/**
 * AttributeSetDeleteHandler class
 * 
 * Handling command for deleting attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetDeleteHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Create new AttributeDeleteHandler
	 * 
	 * @param Cookbook\Contracts\Eav\Repositories\AttributeSetRepositoryContract $attributeSetRepository
	 * 
	 * @return void
	 */
	public function __construct(AttributeSetRepositoryContract $attributeSetRepository)
	{
		// inject dependencies
		$this->attributeSetRepository = $attributeSetRepository;
	}


	/**
	 * Handle AttributeSetDeleteCommand
	 * 
	 * @param Cookbook\Eav\Commands\AttributeSets\AttributeSetDeleteCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeSetDeleteCommand $command)
	{
		return $this->attributeSetRepository->delete($command->id);
	}
}