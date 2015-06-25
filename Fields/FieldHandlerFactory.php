<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Fields;

use Cookbook\Contracts\EAV\FieldHandlerFactoryContract;
use Cookbook\EAV\Managers\AttributeManager;
use Illuminate\Contracts\Container\Container;

/**
 * Attribute Handler Factory class
 * 
 * Used to create suitable handler for different field types
 *
 * @uses  		Cookbook\Contracts\EAV\FieldHandlerFactoryContract
 * @uses  		Cookbook\EAV\Managers\AttributeManager
 * @uses 		Illuminate\Container\Container
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class FieldHandlerFactory implements FieldHandlerFactoryContract
{

	/**
	 * Laravel Container object.
	 *
	 * @var Illuminate\Container\Container
	 */
	protected $container;

	/**
	 * AttributeManager
	 * 
	 * @var Cookbook\EAV\Managers\AttributeManager
	 */
	public $attributeManager;

	/**
	 * Create new AttributeHandlerFactory
	 * 
	 * @return void
	 */
	public function __construct(Container $container, AttributeManager $attributeManager)
	{

		// Inject dependencies
		$this->container = $container;
		$this->attributeManager = $attributeManager;

	}


	/**
	 * Make appropriate FieldHandler by attribute field type.
	 * Definition of FieldHandlers for each data type is found in config file.
	 * 
	 * @param string $attributeFieldType - field type of attribute
	 * 
	 * @return Cookbook\EAV\Fields\AbstractFieldHandler
	 * 
	 * @throws InvalidArgumentException
	 */
	public function make($attributeFieldType)
	{

		$fieldSettings = $this->attributeManager->getFieldType($attributeFieldType);

		if(empty($fieldSettings['handler']))
		{
			throw new \InvalidArgumentException('Field type must have defined handler.');
		}

		$handler = $fieldSettings['handler'];

		return $this->container->make($handler);
	}
}