<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Transformers;

/**
 * AttributeValueTransformer class
 * 
 * @uses     	Cookbook\EAV\Transformers\GenericTransformer
 * @uses     	Cookbook\EAV\Transformers\AttributeTransformer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeValueTransformer extends GenericTransformer {

	/**
	 * Defines rules for attribute group transformation
	 */
	public function __construct(){
		$this->rules = array(
			'id' => array(
				'type' => 'int'
			),
			'entity_id' => array(
				'type' => 'int'
			),
			'entity_type_id' => array(
				'type' => 'int'
			),
			'attribute_id' => array(
				'type' => 'int'
			),
			'language_id' => array(
				'type' => 'int'
			),
			'attribute' => array(
				'transformer' => new AttributeTransformer
			)
		);
	}

}