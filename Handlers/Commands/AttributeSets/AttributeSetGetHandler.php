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
use Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand;


/**
 * AttributeSetGetHandler class
 * 
 * Handling command for getting attribute sets
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetGetHandler
{
	/**
	 * Repository for attribute set DB operations
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Create new AttributeSetGetHandler
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
	 * Handle AttributeSetGetCommand
	 * 
	 * @param Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand $command
	 * 
	 * @return void
	 */
	public function handle(AttributeSetGetCommand $command)
	{
		return $this->attributeSetRepository->get(
			$command->request->input('filter')?$command->request->input('filter'):[],
			$command->request->input('offset')?$command->request->input('offset'):0,
			$command->request->input('limit')?$command->request->input('limit'):0,
			$command->request->input('sort')?$command->request->input('sort'):[]
		);
	}
}