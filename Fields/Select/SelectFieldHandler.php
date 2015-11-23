<?php 
namespace Cookbook\Eav\Fields\Select;


use Cookbook\Eav\Fields\AbstractFieldHandler;

class SelectFieldHandler extends AbstractFieldHandler {

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
		foreach ($attribute->options as $option)
		{
			if($option->value == $value)
			{
				return $option->id;
			}
		}

		return null;
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
		foreach ($attribute->options as $option)
		{
			if($option->id == $value)
			{
				return $option->value;
			}
		}

		return null;
	}
}