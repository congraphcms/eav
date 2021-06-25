<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Commands\Attributes;

use Congraph\Contracts\Eav\AttributeRepositoryContract;
use Congraph\Contracts\Eav\AttributeSetRepositoryContract;
use Congraph\Contracts\Eav\EntityRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * AttributeDeleteCommand class
 * 
 * Command for deleting attribute
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeDeleteCommand extends RepositoryCommand
{
	/**
	 * Repository for handling attribute sets
	 * 
	 * @var Congraph\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for handling entities
	 * 
	 * @var Congraph\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;



	/**
	 * Create new AttributeDeleteCommand
	 * 
	 * @param Congraph\Contracts\Eav\Repositories\AttributeRepositoryContract $repository
	 * @param Congraph\Contracts\Eav\Repositories\AttributeSetRepositoryContract $attributeSetRepository
	 * @param Congraph\Contracts\Eav\Repositories\EntityRepositoryContract $entityRepository
	 * 
	 * @return void
	 */
	public function __construct(
		AttributeRepositoryContract $repository,
		AttributeSetRepositoryContract $attributeSetRepository,
		EntityRepositoryContract $entityRepository
	) {
		parent::__construct($repository);
		$this->attributeSetRepository = $attributeSetRepository;
		$this->entityRepository = $entityRepository;
	}

	/**
	 * Handle RepositoryCommand
	 * 
	 * @return void
	 */
	public function handle()
	{
		$attribute = $this->repository->delete($this->id);

		$this->attributeSetRepository->deleteByAttribute($attribute);

		$this->entityRepository->deleteByAttribute($attribute);

		return $attribute;
	}

}
