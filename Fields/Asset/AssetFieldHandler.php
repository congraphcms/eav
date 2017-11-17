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

use Illuminate\Support\Facades\Config;
use Cookbook\Eav\Fields\AbstractFieldHandler;
use stdClass;

/**
 * AssetFieldHandler class
 * 
 * Responsible for handling asset field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AssetFieldHandler extends AbstractFieldHandler {

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_integer';


	/**
	 * Parse value for database input
	 * 
	 * @param mixed $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function parseValue($value, $attribute, $locale, $params, $entity)
	{
		if(empty($value))
		{
			return null;
		}
		$value = $value['id'];
		return $value;
	}

	/**
	 * Format value for output
	 * 
	 * @param mixed $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function formatValue($value, $attribute)
	{
		if(empty($value))
		{
			return null;
		}
		$relation = new stdClass();
		$relation->id = $value;
		$relation->type = 'file';
		return $relation;
	}

	/**
	 * Insert value to database
	 * 
	 * Takes attribute value params and attribute definition
	 * 
	 * @param array $valueParams
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function insert($valueParams, $attribute, $params, $entity)
	{
		parent::insert($valueParams, $attribute, $params, $entity);

		// check if file is an image and create appropriate versions
		
		// get file/files
		// check their type
		// if type is image
		//  - get needed versions from config
		//  - check which versions already exist
		//  - make versions that don't exist

	}


	/**
	 * Update value in database
	 * 
	 * Takes attribute value params and attribute definition
	 * 
	 * @param array $valueParams
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function update($valueParams, $attribute, $params, $entity)
	{
		parent::update($valueParams, $attribute, $params, $entity);

		// check if file is an image and create appropriate versions
	}

	/**
	 * Handle File Delete
	 * 
	 * @return void
	 */
	public function onFileDelete($command, $result)
	{
		$fileId = $command->id;
		$fieldTypes = $this->attributeManager->getFieldTypes();
		$fileTypes = [];
		foreach ($fieldTypes as $key => $fieldType)
		{
			if($fieldType['handler'] == get_class($this))
			{
				$fileTypes[] = $key;
			}
		}

		if(empty($fileTypes))
		{
			return;
		}

		$attributes = $this->attributeRepository->get(['field_type' => ['in' => $fileTypes]]);
		$attributeIds = [];
		foreach ($attributes as $attribute)
		{
			$attributeIds[] = $attribute->id;
		}

		if(empty($attributeIds))
		{
			return;
		}
		// delete all values for provided entity, attribute and language
		$this	->db->table( $this->table )
				->whereIn( 'attribute_id', $attributeIds )
				->where( 'value', '=', $fileId )
				->delete();
	}

}