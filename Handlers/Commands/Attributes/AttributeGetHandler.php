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
use Cookbook\Eav\Commands\Attributes\AttributeGetCommand;


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
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AttributeGetHandler
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
	 * Handle AttributeGetCommand
	 * 
	 * @param Cookbook\Eav\Commands\Attributes\AttributeGetCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeGetCommand $command)
	{
		return $this->attributeRepository->get(
			$command->request->input('filter')?$command->request->input('filter'):[],
			$command->request->input('offset')?$command->request->input('offset'):0,
			$command->request->input('limit')?$command->request->input('limit'):0,
			$command->request->input('sort')?$command->request->input('sort'):[]
		);
	}
}