<?php namespace Cookbook\EAV\Fields\Text;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;
use Cookbook\EAV\Fields\AbstractFieldHandler;

class TextFieldHandler extends AbstractFieldHandler {


	/**
	 * Prepare attribute value for database
	 * 
	 * Cast value to be string
	 * 
	 * @param array $valueParams
	 * 
	 * @return boolean
	 */
	public function prepareValue($valueParams, \stdClass $attribute){
		$valueParams['value'] = strval($valueParams['value']);
		return $valueParams;
	}

	/**
	 * Take attribute value and transform it for output (management API use)
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	public function transformManagementValue($value, $attribute, $options)
	{
		return $value;
	}

	/**
	 * Take attribute value and transform it for frontend output
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	public function transformValue($value, $attribute, $options)
	{
		return $value;
	}

	/**
	 * Take attribute values and bulk transform them for frontend output
	 * 
	 * @param $values
	 * @param $with
	 * 
	 * @return mixed
	 */
	public function bulkTransformValues($values, $lang_id, $with)
	{
		return $values;
	}

	/**
	 * Check for specific rules and validation on attribute insert
	 * 
	 * Called after standard attribute validation with referenced attribute params
	 * depending on boolean value returned by this function attribute insert will continue or stop the execution
	 * 
	 * @param array $params
	 * 
	 * @return boolean
	 */
	public function checkAttributeForInsert(array &$params)
	{
		return true;
	}

	/**
	 * Check for specific rules and validation on attribute update
	 * 
	 * Called after standard attribute validation with referenced attribute params
	 * depending on boolean value returned by this function attribute update will continue or stop the execution
	 * 
	 * @param array $params
	 * 
	 * @return boolean
	 */
	public function checkAttributeForUpdate(array &$params)
	{
		return true;
	}

	/**
	 * Make changes to attribute before handing it to application
	 * 
	 * @param stdClass $attribute
	 * 
	 * @return object
	 */
	public function transformAttribute(\stdClass $attribute)
	{
		return $attribute;
	}

	
}