<?php 
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields\Date;

use Illuminate\Support\Facades\Config;
use Congraph\Eav\Fields\AbstractFieldHandler;
use Carbon\Carbon;

/**
 * DateFieldHandler class
 * 
 * Responsible for handling date field types
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class DateFieldHandler extends AbstractFieldHandler {
	
	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_date';


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
		$value = Carbon::parse($value)->toDateString();
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
		
		$value = Carbon::parse($value)->toDateString();
		// if(Config::get('app.timezone'))
		// {
		// 	$value->tz(Config::get('app.timezone'));
		// }
		return $value;
	}
}