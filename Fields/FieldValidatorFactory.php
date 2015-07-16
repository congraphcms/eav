<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields;

use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Eav\Managers\AttributeManager;
use Illuminate\Contracts\Container\Container;

/**
 * Field Validator Factory class
 * 
 * Used to create suitable validator for different field types
 *
 * @uses  		Cookbook\Contracts\Eav\FieldValidatorFactoryContract
 * @uses  		Cookbook\Eav\Managers\AttributeManager
 * @uses 		Illuminate\Container\Container
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class FieldValidatorFactory implements FieldValidatorFactoryContract
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
	 * @var Cookbook\Eav\Fields\AttributeManager
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
	 * Make appropriate FieldValidator by attribute field type.
	 * Definition of FieldValidators for each data type is found in config file.
	 * 
	 * @param string $attributeFieldType - field type of attribute
	 * 
	 * @return Cookbook\Eav\Fields\AbstractFieldValidator
	 * 
	 * @throws InvalidArgumentException
	 */
	public function make($attributeFieldType)
	{

		$fieldSettings = $this->attributeManager->getFieldType($attributeFieldType);

		if(empty($fieldSettings['validator']))
		{
			throw new \InvalidArgumentException('Field type must have defined validator.');
		}

		$validator = $fieldSettings['validator'];

		return $this->container->make($validator);
	}
}