<?php namespace Cookbook\Eav\Fields\Text;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;
use Cookbook\Eav\Fields\AbstractFieldHandler;

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