<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Text;

use Cookbook\Eav\Fields\AbstractFieldValidator;
use Cookbook\Eav\Managers\AttributeManager;

/**
 * Abstract Field Validator class
 * 
 * Base class for all feild validators
 * 
 * @uses  		Cookbook\Contracts\Eav\FieldValidatorContract
 * @uses  		Cookbook\Eav\Managers\AttributeManager
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class TextFieldValidator extends AbstractFieldValidator
{


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
	public function validateAttributeForInsert(array &$params)
	{
		if( ! empty($params['data']) )
		{
			$this->exception->addErrors(array('data' => 'Text field attributes can\'t have data'));
			throw $this->exception;
			
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
	public function validateAttributeForUpdate(array &$params)
	{
		if( ! empty($params['data']) )
		{
			$this->exception->addErrors(array('data' => 'Text field attributes can\'t have data'));
			throw $this->exception;
		}

		return true;
	}


}