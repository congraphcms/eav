<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields\Asset;

use Congraph\Contracts\Eav\AttributeRepositoryContract;
use Congraph\Contracts\Filesystem\FileRepositoryContract;
use Congraph\Core\Exceptions\NotFoundException;
use Congraph\Core\Exceptions\ValidationException;
use Congraph\Eav\Fields\AbstractFieldValidator;
use Congraph\Eav\Managers\AttributeManager;
use Illuminate\Database\Connection;

/**
 * AssetFieldValidator class
 * 
 * Validating fields and values of type asset
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
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
	 * @var Congraph\Contracts\Filesystem\FileRepositoryContract
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
	 * @param Congraph\Eav\Managers\AttributeManager 	$attributeManager
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
	 * Check for specific rules and validation on attribute insert
	 * 
	 * Called after standard attribute validation with referenced attribute params
	 * depending on boolean value returned by this function attribute insert will continue or stop the execution
	 * 
	 * @param array $params
	 * 
	 * @return boolean
	 */
	public function validateAttributeForInsert(array &$params)
	{
		parent::validateAttributeForInsert($params);

		if ( empty($params['data']) || !is_array($params['data']))
		{
			return;
		}

		$data = $params['data'];

		if ( ! empty($data['allowed_types']) )
		{
			if( ! is_array($data['allowed_types']) )
			{
				$data['allowed_types'] = [$data['allowed_types']];
			}

			$this->sortAllowedTypes($data);
		}
		else
		{
			$data['allowed_types'] = false;
		}

		$params['data'] = $data;

		return true;
	}

	/**
	 * Check for specific rules and validation on attribute update
	 * 
	 * Called after standard attribute validation with referenced attribute params
	 * depending on boolean value returned by this function attribute update will continue or stop the execution
	 * 
	 * @param array $params
	 * 
	 * @return boolean
	 */
	public function validateAttributeForUpdate(array &$params, $attribute)
	{
		parent::validateAttributeForUpdate($params, $attribute);

		if ( empty($params['data']) || !is_array($params['data']))
		{
			return;
		}

		$data = $params['data'];

		if ( ! empty($data['allowed_types']) )
		{
			if( ! is_array($data['allowed_types']) )
			{
				$data['allowed_types'] = [$data['allowed_types']];
			}

			$this->sortAllowedTypes($data);
		}
		else
		{
			$data['allowed_types'] = false;
		}

		$params['data'] = $data;

		return;
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
			throw new ValidationException(['Invalid asset object.']);
		}

		if( $value['type'] != 'file')
		{
			throw new ValidationException(['Asset can be made only with files.']);
		}

		try
		{
			$file = $this->fileRepository->fetch($value['id']);
		}
		catch(NotFoundException $e)
		{
			throw new ValidationException(['File doesn\'t exist.']);
		}

		if( ! empty($attribute->data->allowed_types) )
		{
			if( ! in_array($file->mime_type, $attribute->data->allowed_types) )
			{
				throw new ValidationException(['Invalid file extension.']);
			}
		}
	}

	protected function sortAllowedTypes(array &$data)
	{
		$allowedTypes = $data['allowed_types'];

		foreach ($allowedTypes as &$type) {
			$type = trim(strval($type));
		}

		$data['allowed_types'] = $allowedTypes;
	}
}