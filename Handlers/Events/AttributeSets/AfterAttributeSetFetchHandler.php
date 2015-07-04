<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Handlers\Events\AttributeSets;


use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Eav\Events\AttributeSets\AfterAttributeSetFetch;


/**
 * AfterAttributeSetFetchHandler class
 * 
 * Handling after attribute set fetch event.
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AfterAttributeSetFetchHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new AfterAttributeSetFetchHandler
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
	 * Handle AfterAttributeSetFetch event
	 * 
	 * @param Cookbook\Eav\Events\AttributeSets\AfterAttributeSetFetch $event
	 * 
	 * @return void
	 */
	public function handle(AfterAttributeSetFetch $event)
	{
		$include = $event->command->request->input('include');

		if( empty($include) || ! is_array($include) || ! in_array('attributes', $include) )
		{
			return true;
		}

		$attributeIds = [];

		$attributes = $event->result['data']->attributes;

		if(empty($attributes))
		{
			return true;
		}

		foreach ($attributes as $attribute) {
			$attributeIds[] = $attribute->id;
		}

		$attributes = $this->attributeRepository->get( [ 'id' => ['in' => $attributeIds] ] );

		if( ! is_array($event->result['includes']) )
		{
			$event->result['includes'] = [];
		}

		$event->result['includes'] = array_merge($event->result['includes'], $attributes);
	}
}