<?php namespace Vizioart\Attributes\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;

use Carbon\Carbon;

class DateInputAttributeHandler extends AbstractAttributeHandler {


	/**
	 * Take attribute value and transform it for output (backend use)
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	public function getValue($value, $attribute, $options){
		$value->value = Carbon::parse( $value->value )->timezone('Europe/Prague')->toDateString();
		return $value;
	}


	/**
	 * Take attribute values and batch transform them for frontend output
	 * 
	 * @param $values
	 * @param $with
	 * 
	 * @return mixed
	 */
	public function fetchValues($values, $lang_id, $with) {
		
		foreach ($values as $entity_id => &$attributes) {
			foreach ($attributes as $attribute_code => &$attribute) {
				$date = Carbon::parse( $attribute->value )->timezone('Europe/Prague')->toDateString();
				$attribute->value = $date;
			}
		}

		return $values;
	}

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

		$input = $valueParams['value'];
		
		if ( ! empty($input) ) {
			$date = Carbon::parse( $input )->timezone('Europe/Prague')->toDateString();
			$valueParams['value'] = $date;
		}

		return $valueParams;
	}

	
}