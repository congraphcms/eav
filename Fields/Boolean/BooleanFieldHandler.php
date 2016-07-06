<?php 
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Boolean;

use Illuminate\Support\Facades\Config;
use Cookbook\Eav\Fields\AbstractFieldHandler;

/**
 * BooleanFieldHandler class
 * 
 * Responsible for handling boolean field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class BooleanFieldHandler extends AbstractFieldHandler {


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
	public function parseValue($value, $attribute)
	{
		if(is_null($value))
		{
			return null;
		}
		$value = intval(!!intval($value));
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
		if(is_null($value))
		{
			return null;
		}
		$value = intval(!!intval($value));
		return $value;
	}
}