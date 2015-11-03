<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Managers;

use Illuminate\Contracts\Config\Repository as ConfigRepositoryContract;

/**
 * AttributeManager class
 * 
 * It's a helper manager class that provides information
 * from Eav config files
 * 
 * @uses   		Illuminate\Support\Facades\Config
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeManager
{

	/**
	 * Array of options for different field types
	 *
	 * @var array
	 */
	protected $availableFieldTypes = [];

	/**
	 * Laravel config repository for accessing config files
	 *
	 * @var Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Create new AttributeManager
	 * 
	 * @return void
	 */
	public function __construct(ConfigRepositoryContract $config)
	{
		// use laravel config repository to access config files
		$this->config = $config;
		
		// get available handlers for different data_types from config
		$this->availableFieldTypes = $this->config->get('cookbook.field_types');

		if(!is_array($this->availableFieldTypes))
		{
			$this->availableFieldTypes = [];
		}
	}


	/**
	 * Get all available field types
	 * 
	 * @return array
	 */
	public function getFieldTypes()
	{
		return $this->availableFieldTypes;
	}

	/**
	 * Get field type options by type slug
	 * 
	 * @param string $type
	 * 
	 * @return array
	 * 
	 * @throws InvalidArgumentException
	 */
	public function getFieldType($type)
	{
		// check if type exists
		if(!array_key_exists($type, $this->availableFieldTypes))
		{
			throw new \InvalidArgumentException('No such field type as: "' . $type . '".');
		}

		return $this->availableFieldTypes[$type];
	}
}