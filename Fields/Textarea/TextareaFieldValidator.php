<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Fields\Textarea;

use Cookbook\Eav\Fields\AbstractFieldValidator;

/**
 * TextareaFieldValidator class
 * 
 * Validating fields and values of text area type
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class TextareaFieldValidator extends AbstractFieldValidator
{
	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations = ['e', 'ne', 'in', 'nin'];

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_text';

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
			$this->exception->addErrors(array('data' => 'Text area field attributes can\'t have data'));
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
			$this->exception->addErrors(array('data' => 'Text area field attributes can\'t have data'));
			throw $this->exception;
		}

		return true;
	}


}