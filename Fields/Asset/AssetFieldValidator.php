<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Asset;

use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Filesystem\FileRepositoryContract;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Eav\Fields\AbstractFieldValidator;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;

/**
 * AssetFieldValidator class
 * 
 * Validating fields and values of type asset
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AssetFieldValidator extends AbstractFieldValidator
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
	 * @var Cookbook\Contracts\Filesystem\FileRepositoryContract
	 */
	protected $fileRepository;

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
	public function __construct(Connection $db, AttributeManager $attributeManager, AttributeRepositoryContract $attributeRepository, FileRepositoryContract $fileRepository)
	{
		// Inject dependencies
		$this->db = $db;
		$this->attributeManager = $attributeManager;
		$this->attributeRepository = $attributeRepository;
		$this->fileRepository = $fileRepository;

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

		if( ! $attribute->required && empty($value) )
		{
			return;
		}

		if( ! is_array($value) || ! isset($value['id']) || ! isset($value['type']))
		{
			throw new ValidationException(['Invalid relation object.']);
		}

		if( $value['type'] != 'file')
		{
			throw new ValidationException(['Asset can be made only with files.']);
		}

		try
		{
			$entity = $this->fileRepository->fetch($value['id']);
		}
		catch(NotFoundException $e)
		{
			throw new ValidationException(['File doesn\'t exist.']);
		}
	}
}