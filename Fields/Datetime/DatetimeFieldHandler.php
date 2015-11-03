<?php 
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Datetime;

use Illuminate\Support\Facades\Config;
use Cookbook\Eav\Fields\AbstractFieldHandler;
use Carbon\Carbon;

/**
 * DatetimeFieldHandler class
 * 
 * Responsible for handling datetime field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class DatetimeFieldHandler extends AbstractFieldHandler {
	
	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_datetime';


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
		$value = Carbon::parse($value)->tz('UTC')->toDateTimeString();
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
		$value = Carbon::parse($value);
		if(Config::get('app.timezone'))
		{
			$value->tz(Config::get('app.timezone'));
		}
		return $value;
	}
}