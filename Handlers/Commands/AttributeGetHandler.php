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
use Cookbook\EAV\Commands\AttributeGetCommand;


/**
 * AttributeGetHandler class
 * 
 * Handling command for getting attributes
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeGetHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\EAV\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AttributeGetHandler
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
	 * Handle AttributeGetCommand
	 * 
	 * @param Cookbook\EAV\Commands\AttributeGetCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeGetCommand $command)
	{
		return $this->attributeRepository->get(
			$command->request->input('filter'), 
			$command->request->input('offset'), 
			$command->request->input('limit'), 
			$command->request->input('sort'),
			$command->request->input('include')
		);
	}
}