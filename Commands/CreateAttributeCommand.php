<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Commands;

use Illuminate\Http\Request;

/**
 * CreateAttributeCommand class
 * 
 * Command for creating attribute
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CreateAttributeCommand
{

	/**
	 * attribute code
	 * 
	 * @var string
	 */
	public $code;

	/**
	 * attribute admin label
	 * 
	 * @var string
	 */
	public $admin_label;

	/**
	 * attribute admin notice
	 * 
	 * @var string
	 */
	public $admin_notice;

	/**
	 * attribute field type
	 * 
	 * @var string
	 */
	public $field_type;

	/**
	 * is attribute localized
	 * 
	 * @var boolean
	 */
	public $localized;

	/**
	 * attribute default value
	 * 
	 * @var string
	 */
	public $default_value;

	/**
	 * is attribute unique
	 * 
	 * @var boolean
	 */
	public $unique;

	/**
	 * is attribute required
	 * 
	 * @var boolean
	 */
	public $required;

	/**
	 * is attribute filterable
	 * 
	 * @var boolean
	 */
	public $filterable;

	/**
	 * attribute status
	 * 
	 * @var string
	 */
	public $status;

	/**
	 * attribute translations
	 * 
	 * @var array
	 */
	public $translations;

	/**
	 * attribute options
	 * 
	 * @var array
	 */
	public $options;

	/**
	 * attribute data
	 * 
	 * @var array
	 */
	public $data;

	/**
	 * attribute created timestamp
	 * 
	 * @var array
	 */
	public $created_at;

	/**
	 * attribute updated timestamp
	 * 
	 * @var array
	 */
	public $updated_at;

	/**
	 * attribute updated timestamp
	 * 
	 * @var array
	 */
	public $request;


	/**
	 * Create new CreateAttributeCommand
	 * 
	 * @param string 	$code
	 * @param string 	$admin_label
	 * @param string 	$admin_notice
	 * @param string 	$field_type
	 * @param boolean 	$localized
	 * @param string 	$default_value
	 * @param boolean 	$unique
	 * @param boolean 	$required
	 * @param boolean 	$filterable
	 * @param string 	$status
	 * @param array 	$translations
	 * @param array 	$options
	 * @param array 	$data
	 * 
	 * @return void
	 */
	public function __construct(Request $request)
	{
		// inject dependencies
		// $this->code = $code;
		// $this->admin_label = $admin_label;
		// $this->admin_notice = $admin_notice;
		// $this->field_type = $field_type;
		// $this->localized = $localized;
		// $this->default_value = $default_value;
		// $this->unique = $unique;
		// $this->required = $required;
		// $this->filterable = $filterable;
		// $this->status = $status;
		// $this->translations = $translations;
		// $this->options = $options;
		// $this->data = $data;
		
		$this->request = $request;

	}
}
