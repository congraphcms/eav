<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Handlers\Commands;


use Cookbook\Contracts\EAV\AttributeSetRepositoryContract;
use Cookbook\EAV\Commands\AttributeSetUpdateCommand;


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
class AttributeSetUpdateHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\EAV\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Create new AttributeSetUpdateHandler
	 * 
	 * @param Cookbook\Contracts\EAV\Repositories\AttributeSetRepositoryContract $attributeSetRepository
	 * 
	 * @return void
	 */
	public function __construct(AttributeSetRepositoryContract $attributeSetRepository)
	{
		// inject dependencies
		$this->attributeSetRepository = $attributeSetRepository;
	}


	/**
	 * Handle AttributeSetUpdateCommand
	 * 
	 * @param Cookbook\EAV\Commands\AttributeSetUpdateCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeSetUpdateCommand $command)
	{
		return $this->attributeSetRepository->update($command->id, $command->request->all());
	}
}