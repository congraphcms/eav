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
use Cookbook\EAV\Commands\AttributeCreateCommand;


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
	 * @var Cookbook\Contracts\EAV\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AttributeCreateHandler
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
	 * Handle AttributeCreateCommand
	 * 
	 * @param Cookbook\EAV\Commands\AttributeCreateCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeCreateCommand $command)
	{
		return $this->attributeRepository->create($command->request->all());
	}
}