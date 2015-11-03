<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Relation;

use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\EntityRepositoryContract;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Eav\Fields\AbstractFieldValidator;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;

/**
 * RelationFieldValidator class
 * 
 * Validating fields and values of type relation
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RelationFieldValidator extends AbstractFieldValidator
{

	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations = ['e', 'ne', 'in', 'nin'];

	/**
	 * Repository for entities
	 *
	 * @var Cookbook\Contracts\Eav\EntityRepositoryContract
	 */
	protected $entityRepository;

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_integer';

	/**
	 * Create new RelationFieldValidator
	 * 
	 * @param Illuminate\Database\Connection 			$db
	 * @param Cookbook\Eav\Managers\AttributeManager 	$attributeManager
	 * @param string 									$table
	 *  
	 * @return void
	 */
	public function __construct(Connection $db, AttributeManager $attributeManager, AttributeRepositoryContract $attributeRepository, EntityRepositoryContract $entityRepository)
	{
		// Inject dependencies
		$this->db = $db;
		$this->attributeManager = $attributeManager;
		$this->entityRepository = $entityRepository;
		$this->attributeRepository = $attributeRepository;

		$this->exception = new ValidationException();
	}

	/**
	 * Validate attribute value
	 * 
	 * This function can be extended by specific attribute handler
	 * 
	 * @param array $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function validateValue($value, $attribute, $entity_id = 0)
	{

		parent::validateValue($value, $attribute, $entity_id);

		if( ! is_array($value) || ! isset($value['id']) || ! isset($value['type']))
		{
			throw new ValidationException(['Invalid relation object.']);
		}

		if( $value['type'] != 'entity')
		{
			throw new ValidationException(['Relation can be made only with entities.']);
		}

		try
		{
			$entity = $this->entityRepository->fetch($value['id']);
		}
		catch(NotFoundException $e)
		{
			throw new ValidationException(['Entity doesn\'t exist.']);
		}
	}
}