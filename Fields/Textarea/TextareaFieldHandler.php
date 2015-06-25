<?php namespace Vizioart\Attributes\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;

class TextAreaAttributeHandler extends AbstractAttributeHandler {



	/**
	 * Prepare attribute value for database
	 * 
	 * Cast value to be string
	 * 
	 * @param array $valueParams
	 * 
	 * @return boolean
	 */
	public function prepareValue($valueParams, $attribute){
		$valueParams['value'] = strval($valueParams['value']);
		return $valueParams;
	}

	
}