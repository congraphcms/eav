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
use Cookbook\Contracts\Eav\AttributeSetRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Contracts\Eav\FieldHandlerFactoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Bus\RepositoryCommandHandler;

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
class AttributeDeleteHandler extends RepositoryCommandHandler
{

	/**
	 * Repository for handling attribute sets
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for handling entities
	 * 
	 * @var Cookbook\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;



	/**
	 * Create new AttributeDeleteHandler
	 * 
	 * @param Cookbook\Contracts\Eav\Repositories\AttributeRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(AttributeRepositoryContract $repository, AttributeSetRepositoryContract $attributeSetRepository, EntityRepositoryContract $entityRepository)
	{
		parent::__construct($repository);
		$this->attributeSetRepository = $attributeSetRepository;
		$this->entityRepository = $entityRepository;
	}

	/**
	 * Handle RepositoryCommand
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		$attribute = $this->repository->delete($command->id);

		$this->attributeSetRepository->deleteByAttribute($attribute);

		$this->entityRepository->deleteByAttribute($attribute);

		return $attribute;
	}
}