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
 * AttributeTransformer class
 * 
 * @uses     	Congraph\EAV\Transformers\GenericTransformer
 * @uses     	Congraph\EAV\Transformers\AttributeOptionTransformer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeTransformer extends GenericTransformer {

	/**
	 * Defines rules for attribute group transformation
	 */
	public function __construct(){
		$this->rules = array(
			'id' => array(
				'type' => 'int'
			),
			'is_required' => array(
				'type' => 'bool'
			),
			'is_unique' => array(
				'type' => 'bool'
			),
			'is_filterable' => array(
				'type' => 'bool'
			),
			'language_dependable' => array(
				'type' => 'bool'
			),
			'options' => array(
				'collection' => true,
				'transformer' => new AttributeOptionTransformer
			)
		);
	}

}