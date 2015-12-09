<?php 
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Text;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;
use Cookbook\Eav\Fields\AbstractFieldHandler;

/**
 * TextFieldHandler class
 * 
 * Responsible for handling text field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class TextFieldHandler extends AbstractFieldHandler {

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_text';
	
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
		$value = strval($value);
		return $value;
	}
}