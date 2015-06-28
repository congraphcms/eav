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


use Cookbook\Contracts\EAV\AttributeRepositoryContract;
use Cookbook\EAV\Commands\AttributeDeleteCommand;


/**
 * AttributeDeleteHandler class
 * 
 * Handling command for deleting attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeDeleteHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\EAV\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AttributeDeleteHandler
	 * 
	 * @param Cookbook\Contracts\EAV\Repositories\AttributeRepositoryContract $attributeRepository
	 * 
	 * @return void
	 */
	public function __construct(AttributeRepositoryContract $attributeRepository)
	{
		// inject dependencies
		$this->attributeRepository = $attributeRepository;
	}


	/**
	 * Handle AttributeDeleteCommand
	 * 
	 * @param Cookbook\EAV\Commands\AttributeDeleteCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeDeleteCommand $command)
	{
		return $this->attributeRepository->delete($command->id);
	}
}