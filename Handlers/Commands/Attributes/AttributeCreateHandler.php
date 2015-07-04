<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Handlers\Commands\Attributes;


use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Eav\Commands\Attributes\AttributeCreateCommand;


/**
 * AttributeCreateHandler class
 * 
 * Handling command for creating attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeCreateHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AttributeCreateHandler
	 * 
	 * @param Cookbook\Contracts\Eav\Repositories\AttributeRepositoryContract $attributeRepository
	 * 
	 * @return void
	 */
	public function __construct(AttributeRepositoryContract $attributeRepository)
	{
		// inject dependencies
		$this->attributeRepository = $attributeRepository;
	}


	/**
	 * Handle AttributeCreateCommand
	 * 
	 * @param Cookbook\Eav\Commands\Attributes\AttributeCreateCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeCreateCommand $command)
	{
		return $this->attributeRepository->create($command->request->all());
	}
}