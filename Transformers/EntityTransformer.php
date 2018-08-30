<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\EAV\Transformers;

/**
 * EntityTransformer class
 * 
 * @uses     	Congraph\EAV\Transformers\GenericTransformer
 * @uses     	Congraph\EAV\Transformers\AttributeSetTransformer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTransformer extends GenericTransformer {

	/**
	 * Defines rules for attribute group transformation
	 */
	public function __construct(){
		$this->rules = array(
			'id' => array(
				'type' => 'int'
			),
			'object_id' => array(
				'type' => 'int'
			),
			'attribute_set_id' => array(
				'type' => 'int'
			),
			'multiple_sets' => array(
				'type' => 'bool'
			),
			'archive_parent' => array(
				'type' => 'bool'
			),
			'default_attribute_set_id' => array(
				'type' => 'int'
			),

			'attribute_set' => array(
				'transformer' => new AttributeSetTransformer
			),

			// 'attribute_values' => array(
			// 	'collection' => true,
			// 	'list_by' => array('code', 'language_id'),
			// 	'transformer' => new AttributeValueTransformer
			// )
		);
	}

}