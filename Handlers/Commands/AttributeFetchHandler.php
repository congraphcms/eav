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
use Cookbook\EAV\Commands\AttributeFetchCommand;


/**
 * AttributeFetchHandler class
 * 
 * Handling command for fetching attribute by ID
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeFetchHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\EAV\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AttributeFetchHandler
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
	 * Handle AttributeFetchCommand
	 * 
	 * @param Cookbook\EAV\Commands\AttributeFetchCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeFetchCommand $command)
	{
		return $this->attributeRepository->fetchById($command->id);
	}
}