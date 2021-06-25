<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Commands\AttributeSets;

use Congraph\Contracts\Eav\AttributeSetRepositoryContract;
use Congraph\Contracts\Eav\EntityRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * AttributeSetDeleteCommand class
 * 
 * Command for deleting attribute set
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetDeleteCommand extends RepositoryCommand
{
	/**
	 * Repository for handling entities
	 * 
	 * @var Congraph\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;


	/**
	 * Create new AttributeSetDeleteCommand
	 * 
	 * @param Congraph\Contracts\Eav\AttributeSetRepositoryContract $repository
	 * @param Congraph\Contracts\Eav\EntityRepositoryContract $entityRepository
	 * 
	 * @return void
	 */
	public function __construct(
		AttributeSetRepositoryContract $repository,
		EntityRepositoryContract $entityRepository
	) {
		parent::__construct($repository);
		$this->entityRepository = $entityRepository;
	}

	/**
	 * Handle RepositoryCommand
	 * 
	 * @return void
	 */
	public function handle()
	{
		$attributeSet = $this->repository->delete($this->id);

		$this->entityRepository->deleteByAttributeSet($attributeSet);

		return $attributeSet;
	}

}