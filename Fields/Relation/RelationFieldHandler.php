<?php namespace Vizioart\Attributes\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\App;
use Vizioart\Attributes\Repositories\EntityRepository;
use Vizioart\Attributes\Managers\AttributeManager;

class RelationAttributeHandler extends AbstractAttributeHandler {

	/**
	 * entityRepository
	 * 
	 * @var object
	 */
	public $entityRepository;

	/**
	 * Create new AbstractAttributeHandler
	 * 
	 * @param Eloquent $attributeValueModel
	 * 
	 * @return void
	 */
	public function __construct(AttributeManager $attributeManager, 
								Eloquent $attributeValueModel,
								Eloquent $attributeOptionModel,
								Eloquent $setAttributeModel,
								EntityRepository $entityRepository = null){

		// init parent constructor
		parent::__construct($attributeManager, $attributeValueModel, $attributeOptionModel, $setAttributeModel);

		if($entityRepository == null){
			$entityRepository = App::make('EntityRepository');
		}

		$this->entityRepository = $entityRepository;
	}

	/**
	 * Clean all related values for given entity
	 * 
	 * Takes entity id,
	 * and deletes all related values
	 * 
	 * @param integer $entityID
	 * @param integer $attributeID
	 * 
	 * @return boolean
	 * 
	 * @todo Check if there is need for returning false or there will be an exception if something goes wrong
	 */
	public function sweepAfterEntities($entityIDs, $attributeID){
		
		if(!is_array($entityIDs)){
			$entityIDs = array(intval($entityIDs));
		}
		
		$success = $this	->attributeValueModel
							->where(function($query) use($entityIDs, $attributeID){
								$query	->whereIn('entity_id', $entityIDs)
										->where('attribute_id', '=', $attributeID);
							})
							->orWhere(function($query) use($entityIDs, $attributeID){
								$query	->whereIn('value', $entityIDs)
										->where('attribute_id', '=', $attributeID);
							})
							->delete();
		return !!$success;
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
	public function checkAttributeForInsert(array &$params){
		
		if(!isset($params['data']) || !is_array($params['data'])){
			$this->addErrors(
				array(
					'Data needs to be defined for relation.'
				)
			);
			return false;
		}

		$data = $params['data'];

		if(!isset($data['relation_type_id'])){
			$this->addErrors(
				array(
					'Relation type needs to be set.'
				)
			);
			return false;
		}

		$relation_type_id = $data['relation_type_id'];

		$entityType = $this->entityRepository->fetchTypeById($relation_type_id);

		if(!$entityType){
			$this->addErrors(
				array(
					'Invalid relation type.'
				)
			);
			return false;
		}

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
	public function checkAttributeForUpdate(array &$params){
		if(isset($params['data'])){
			unset($params['data']);
		}
		return true;
	}

	/**
	 * Make changes to attribute before handing it to application
	 * 
	 * @param array $attribute
	 * 
	 * @return object
	 */
	public function transformAttribute($attribute){
		$attribute->data = json_decode($attribute->data);
		return $attribute;
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
	public function fetchValue($value, $attribute, $options){
		$value->value = intval($value->value);
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

		$entityIDs = array();

		foreach ($values as $entity_id => $fields) {
			foreach ($fields as $code => $field) {
				if(is_array($field->value)){
					foreach ($field->value as $value) {
						if(intval($value)){
							$entityIDs[] = intval($value);
						}
					}
				}else{
					if(intval($field->value)){
						$entityIDs[] = intval($field->value);
					}
				}
			}
			
		}

		if(empty($entityIDs)){
			return $values;
		}

		$objectIDs = $this->entityRepository->getObjectIDs($entityIDs);

		if(!in_array('relations', $with)){
			return $this->extractObjectIDs($values, $objectIDs);
		}

		$postModel = App::make('PostModel');

		$postFilter = array(
			'id' => array(
				'in' => implode(',', $objectIDs)
			), 
			'locale' => $lang_id
		);

		$postWidth = array(
			'assets' => true
		);
		
		$relations = $postModel->filter($postFilter, $postWidth);

		if(!$relations){
			return $this->extractObjectIDs($values, $objectIDs);
		}
		
		foreach ($values as $entity_id => &$fields) {
			foreach ($fields as $code => &$field) {
				if(is_array($field->value)){
					$relationValues = array();
					foreach ($field->value as $value) {
						foreach ($relations as $relation) {
							if($relation['entity_id'] == $value){
								$relationValues[] = $relation;
								break;
							}
						}
					}
					$field->value = $relationValues;
				}else{
					foreach ($relations as $relation) {
						if($relation['entity_id'] == $field->value){
							$field->value = $relation;
							break;
						}
					}
				}
			}
			
		}

		return $values;
	}

	protected function extractObjectIDs($values, $objectIDs){
		if(!empty($objectIDs)){
			foreach ($values as $entity_id => &$fields) {
				foreach ($fields as $code => &$field) {
					if(is_array($field->value)){
						foreach ($field->value as &$value) {
							if(isset($objectIDs[$value])){
								$value = $objectIDs[$value];
							}
						}
					}else{
						if(isset($objectIDs[$field->value])){
							$field->value = $objectIDs[$field->value];
						}
					}
				}
			}
			return $values;
		}else{
			return $values;
		}
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
			$value = null;
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
		if(is_array($valueParams['value'])){
			foreach ($valueParams['value'] as &$value) {
				$value = intval($value);
			}

			return $valueParams;
		}
		
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

		return $parentValidation;
	}
}