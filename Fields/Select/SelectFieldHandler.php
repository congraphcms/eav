<?php namespace Vizioart\Attributes\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\DB;

class SelectAttributeHandler extends AbstractAttributeHandler {


	/**
	 * Take attribute value and transform it for frontend output
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	public function fetchValue($value, $attribute, $options){
		foreach ($options as $option) {
			if($option->id == $value->value){
				$value->value = $option->value;
				$value->label = $option->label;
				break;
			}
		}
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
	public function fetchValues($values, $lang_id, $with){

		$optionIDs = array();

		foreach ($values as $entity_id => $fields) {
			foreach ($fields as $code => $field) {
				if(is_array($field->value)){
					foreach ($filed->value as $value) {
						$optionIDs[] = intval($value);
					}
				}else{
					$optionIDs[] = intval($field->value);
				}
			}
			
		}

		$options = DB::table('attribute_options')
					->whereIn('id', $optionIDs)
					->lists('value', 'id');

		foreach ($values as $entity_id => &$fields) {
			foreach ($fields as $code => &$field) {
				if(is_array($field->value)){
					$optionValues = array();
					foreach ($filed->value as $value) {
						if(isset($options[intval($field->value)])){
							$optionValues[] = $options[intval($value)];
						}
					}
					$field->value = $optionValues;
				}else{
					if(isset($options[intval($field->value)])){
						$field->value = $options[intval($field->value)];
					}
				}
			}
			
		}

		return $values;
	}

	/**
	 * Take attribute value and transform it for output
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	public function getValue($value, $attribute, $options){

		$value->value = intval($value->value);
		return $value;
	}

	/**
	 * Provide default value for attribute
	 * 
	 * @param $value
	 * @param $attribute
	 * @param $options
	 * 
	 * @return mixed
	 */
	public function getDefaultValue($attribute, $options = array()){

		$attributeSettings = $this->attributeManager->getDataType($attribute->data_type);
		if($attributeSettings['has_multiple_values']){
			$value = array();
		}else{
			$value = 0;
		}
		foreach ($options as $option) {
			if($option->is_default){
				if($attributeSettings['has_multiple_values']){
					$value[] = $option->id;
				}else{
					$value = $option->id;
				}
			}
		}

		return $value;
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
		$valueParams['value'] = intval($valueParams['value']);
		return $valueParams;
	}

	/**
	 * Validate attribute value
	 * 
	 * This function should be overriden by specific attribute handler
	 * 
	 * @param array $valueParams
	 * @param Eloqunt $attributeDefinition
	 * 
	 * @return boolean
	 */
	public function validateAttribute($valueParams, Eloquent $attributeDefinition){
		$parentValidation = parent::validateAttribute($valueParams, $attributeDefinition);

		if(empty($valueParams['value'])){
			$valueParams['value'] = 0;
			return $parentValidation;
		}

		$option = $this ->attributeOptionModel
						->where('id', '=', $valueParams['value'])
						->first();

		
		if(!$option){
			$this->addErrors(
				array(
					'This needs to be a valid option.'
				)
			);
			return false;
		}

		return $parentValidation;
	}
}