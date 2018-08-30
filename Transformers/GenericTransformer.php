<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\EAV\Transformers;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * GenericTransformer abstract class
 * 
 * Transforms different objects to valid types and arrays
 * 
 * @uses     	Illuminate\Database\Eloquent\Model
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class GenericTransformer {

	/**
	 * Attributes of current model used, always used as array
	 * 
	 * @var Array
	 */
	public $attributes = array();

	/**
	 * Type of object - 'array' | 'object'
	 * 
	 * @var string
	 */
	public $type = 'array';

	/**
	 * Rules for transformation, key(name of property) value(rules for that property) 
	 * 
	 * @var Array
	 */
	public $rules = array();

	/**
	 * Currently transformed object
	 * 
	 * @var Array | Object
	 */
	public $transformed = array();

	/**
	 * Collection of transformed objects
	 * 
	 * @var Array
	 */
	public $collection = array();

	/**
	 * Flag for strict transformation
	 * If transformation is strict, only properies that are keyed in $rules 
	 * will be copied in transformed object
	 * 
	 * @var boolean
	 */
	public $strict = false;

	/**
	 * Constructor
	 */
	public function __construct(){

	}

	/**
	 * Extract all properties from object to array
	 * 
	 * @param Array | Object $model
	 * 
	 * @return Array
	 */
	public function fetchAttributes($model){
		// if it's eloquent model use getAttributes getter
		if($model instanceof Eloquent){
			$this->attributes = $model->getAttributes();
		}else{
			$this->attributes = (array) $model;
		}

		return $this->attributes;
	}

	/**
	 * Getter for current object attributes
	 * 
	 * @return Array
	 */
	public function getAttributes(){
		return $this->attributes;
	}

	/**
	 * Transform given model with already defined rules
	 * 
	 * @param  Array | Object $model
	 * @param  string $as (optional) - do you want transformed object as array or object 
	 * @return Array | Object
	 */
	public function transform($model, $as = ''){

		// set type
		if(is_string($as) && !empty($as)){
			$this->type = $as;
		}else{
			if(is_array($model)){
				$this->type = 'array';
			}else{
				$this->type = 'object';
			}
		}

		// fetch attributes
		$this->fetchAttributes($model);

		// create object or array
		$this->createTransformedObject();

		// and return it
		return $this->transformed;
	}

	/**
	 * Transform given collection by already assigned rules,
	 * You can also list new transformed objects by some property,
	 * keep in mind that this property or combination of properies should be unique
	 * otherwise some objects will be overwritten
	 * 
	 * @param  Array $collection - collection of models
	 * @param  string | Array $listBy
	 * @return Array
	 * @throws InvalidArgumentException
	 */
	public function transformCollection($collection, $listBy = ''){
		$this->collection = array();

		// go through all models in collection
		foreach ($collection as $key => $model) {

			// transform each model
			$this->transform($model);


			// check if $listBy is set
			// if it's string (it means only one property is used)
			if(is_string($listBy) && !empty($listBy)){
				// simply assign that key in our collection with transformed model
				$this->collection[$this->getKey($listBy)] = $this->transformed;
			}
			// if $listBy is array we need to create more complex structure
			elseif(is_array($listBy) && !empty($listBy)){

				// reset all helper vars
				unset($item);
				$item = null;
				$listByKey = 0;

				// iterate through properties used for listing
				for($i = 0; $i < count($listBy); $i++) {
					
					// if it's first property that we list by
					// item is set to be whole collection
					if($i == 0){
						$item = &$this->collection;
					}
					// for every next propery we will use already assigned $item var
					// !!! this part of code will be executed only after first iteration
					// helper vars have values from previous iteration
					else{
						// check if $listByKey from previous iteration exists in $item
						// and create empty array if it doesn't
						if(!array_key_exists($listByKey, $item)){
							$item[$listByKey] = array();
						}

						// set $item var to reference to new object
						$item = &$item[$listByKey];
					}

					// get propery with current key
					$listByKey = $this->getKey($listBy[$i]);


					// you can use only properties with values that can be array key
					if(is_integer($listByKey) || (is_string($listByKey) && !empty($listByKey))){
						// if this is the last listBy property
						// we will assign transformed object to $item
						if($i == count($listBy) - 1){
							$item[$listByKey] = $this->transformed;
						}
					}
					// otherwise we will throw exception
					else{
						throw new \InvalidArgumentException('Invalid listBy');
					}
						
				}
			}else{
				$this->collection[$key] = $this->transformed;
			}
		}

		

		return $this->collection;
	}

	/**
	 * Creates transformed object by already assigned rules and attributes
	 * and populates transfored with it
	 * 
	 * @return void
	 */
	protected function createTransformedObject(){

		// determine transformed object type
		if($this->type == 'array'){
			$this->transformed = array();
		}else{
			$this->transformed = new \stdClass;
		}

		// go through attributes and transform them
		foreach ($this->attributes as $key => $value) {
			$this->transformAttribute($key, $value);
		}
	}

	/**
	 * Transform attribute value by rules
	 * 
	 * @param  string $key
	 * @param  mixed $value
	 * @return void
	 */
	protected function transformAttribute($key, $value){
		// if rules for this property are set
		// go through rules
		if(isset($this->rules[$key]) && !empty($this->rules[$key])){
			$value = $this->transformProp($value, $this->rules[$key]);
			$this->assignProp($key, $value);
		}
		// else if it's not strict transformation copy the property
		elseif(!$this->strict){
			$this->assignProp($key, $value);
		}
	}

	/**
	 * [transformProp description]
	 * @param  mixed $value
	 * @param  Array $rule
	 * @return mixed
	 */
	public function transformProp($value, $rule){

		// if some other transformer is assigned for this property
		// delegate transformation to that transformer
		if(isset($rule['transformer']) && $rule['transformer'] instanceof GenericTransformer){
			// if property is collection use collection transformation
			if(!empty($rule['collection'])){

				// use listBy for collection transformation
				if(!empty($rule['list_by'])){
					$listBy = $rule['list_by'];
				}else{
					$listBy = '';
				}

				// transform property collection
				$value = $rule['transformer']->transformCollection($value, $listBy);
			}
			// else use simple transform
			else{
				$value = $rule['transformer']->transform($value);
			}
			
		}

		// if it's set strict type for property, cast to that type
		if(isset($rule['type'])){
			$value = $this->castTypeProp($value, $rule['type']);
		}

		// if it's set some custom transformation for property, perform it
		if(isset($rule['transform'])){
			$value = $this->makeTransformationProp($value, $rule['transform']);
		}

		// return property value
		return $value;
	}

	/**
	 * Make custom transformations
	 * custom transformation functions should be prefixed with 'transformation'
	 * 
	 * @param  mixed $value
	 * @param  string $transformation
	 * @return mixed
	 */
	protected function makeTransformationProp($value, $transformation = ''){
		// make method name
		$method = 'transform' . ucfirst($transformation);

		// use that method and return new value
		return $this->changeProp($value, $method);
	}

	/**
	 * Cast strict types
	 * @param  mixed $value
	 * @param  string $type
	 * @return mixed
	 */
	protected function castTypeProp($value, $type = ''){
		// make method name
		$method = 'cast' . ucfirst($type);
		// use cast method and return new value
		return $this->changeProp($value, $method);
	}

	/**
	 * Make a change to prop with given method 
	 * 
	 * @param  mixed $value
	 * @param  string $method
	 * @return mixed
	 */
	protected function changeProp($value, $method = ''){
		// if method exists perform the change
		if(!empty($method) && method_exists($this, $method)){
			$value = $this->$method($value);
		}

		// return new value
		return $value;
	}

	/**
	 * Assign prop for appropriate transformed object type
	 * 
	 * @param  string $prop
	 * @param  mixed $value
	 * @return void
	 */
	protected function assignProp($prop, $value){
		if($this->type == 'array'){
			$this->transformed[$prop] = $value;
		}else{
			$this->transformed->$prop = $value;
		}
	}

	/**
	 * Getter for transformed object properties
	 * @param  string $key
	 * @return mixed
	 */
	public function getKey($key){


		if($this->type == 'array'){
			if(array_key_exists($key, $this->transformed)){
				return $this->transformed[$key];
			}else{
				return false;
			}
		}else{
			if(property_exists($this->transformed, $key)){
				return $this->transformed->$key;
			}else{
				return false;
			}
		}
	}

	/**
	 * Getter for original object attributes
	 * @param  string $key
	 * @return mixed
	 */
	public function getAttribute($key){
		if(array_key_exists($key, $this->transformed)){
			return $this->transformed[$key];
		}else{
			return false;
		}
	}


	/**
	 * Cast integer
	 * @param  mixed $value
	 * @return int
	 */
	protected function castInt($value){
		return intval($value);
	}

	/**
	 * Cast boolean
	 * @param  mixed $value
	 * @return bool
	 */
	protected function castBool($value){
		return (bool) $value;
	}

	/**
	 * Cast array
	 * @param  mixed $value
	 * @return Array
	 */
	protected function castArray($value){
		return (array) $value;
	}

	/**
	 * Cast string
	 * @param  mixed $value
	 * @return string
	 */
	protected function castString($value){
		return strval($value);
	}
}