<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Handlers\Command;


use Cookbook\Contracts\EAV\AttributeRepositoryContract;
use Cookbook\EAV\Commands\CreateAttributeCommand;


/**
 * CreateAttributeCommandHandler class
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
class CreateAttributeCommandHandler
{
	/**
	 * Repository for attribute DB operations
	 * 
	 * @var Cookbook\Contracts\EAV\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Create new CreateAttributeCommandHandler
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
	 * Handle CreateAttributeCommand
	 * 
	 * @param Cookbook\EAV\Commands\CreateAttributeCommand $command
	 * 
	 * @return void
	 */
	public function handle(CreateAttributeCommand $command)
	{
		// $attributeParams = [
		// 	'code' => $command->code,
		// 	'admin_label' => $command->admin_label,
		// 	'admin_notice' => $command->admin_notice,
		// 	'field_type' => $command->field_type,
		// 	'localized' => $command->localized,
		// 	'default_value' => $command->default_value,
		// 	'unique' => $command->unique,
		// 	'required' => $command->required,
		// 	'filterable' => $command->filterable,
		// 	'status' => $command->status,
		// 	'translations' => $command->translations,
		// 	'options' => $command->options,
		// 	'data' => $command->data
		// ];
		var_dump('Create attribute');
		var_dump($command->request->all());
		return $this->attributeRepository->create($command->request->all());
	}
}