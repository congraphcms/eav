<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


return array(
	/**
	 * List of data types supported by application
	 * 
	 * Each data type need to have unique key that will be used by cookbook administration
	 * to create suitable directive for input of this type
	 * 
	 * Warning: 
	 * keys are written in database with each attribute creation
	 * every change to keys in this config file will produce error with attributes
	 * that were created earlier
	 * 
	 * Data type properties:
	 * label                       - Label that will be used as human readable description 
	 *                               for this data type
	 * table                       - database table in which values of this type will be written
	 * handler                     - full name of class that will be used as handler for 
	 *                               this attribute
	 * handler_name                - name of the handler that is used for laravel app container
	 * value_model                 - full name of eloquent class that is used as value model
	 * can_have_default_value      - boolean flag whether this data type can have default value
	 * can_be_required             - boolean flag whether this data type can be required
	 * can_be_unique               - boolean flag whether this data type can be unique
	 * can_be_filter               - boolean flag whether this data type can be filterable
	 * can_be_localized            - boolean flag whether this data type can be localized
	 * has_options                 - boolean flag whether this data type can have options
	 * is_relation                 - boolean flag whether this data type is relation
	 * is_asset                    - boolean flag whether this data type is asset
	 * has_multiple_values         - boolean flag whether this data type have multiple values (array of values)
	 * 
	 * 
	 * Check documentation for what is needed to develop for one data type
	 */
	'field_types' => array(
		/**
		 * Simple text input
		 * 
		 * Administration will render this input as HTML5 text input
		 * values will be written as strings in attribute_values_text table
		 * 
		 * It's an open field that can be required, unique, sortable and filterable
		 */
		'text' => array(
			'label'						=> 'Text',
			'table' 					=> 'attribute_values_text',
			'handler'					=> 'Cookbook\Eav\Fields\Text\TextFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Text\TextFieldValidator',
			'handler_name'				=> 'TextFieldHandler',
			'can_have_default_value'	=> true,
			'can_be_unique'				=> true,
			'can_be_localized'			=> true,
			'can_be_filter'				=> true,
			'can_be_searchable'			=> true,
			'has_options'				=> false,
			'has_multiple_values'		=> false,
			'sortable'					=> true
		),

		'boolean' => array(
			'label'						=> 'Boolean',
			'table' 					=> 'attribute_values_integer',
			'handler'					=> 'Cookbook\Eav\Fields\Boolean\BooleanFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Boolean\BooleanFieldValidator',
			'handler_name'				=> 'BooleanFieldHandler',
			'can_have_default_value'	=> false,
			'can_be_unique'				=> false,
			'can_be_localized'			=> true,
			'can_be_filter'				=> true,
			'can_be_searchable'			=> false,
			'has_options'				=> false,
			'has_multiple_values'		=> false,
			'sortable'					=> true
		),

		// /**
		//  * Simple text area
		//  * 
		//  * Administration will render this input as HTML5 text area
		//  * values will be written as strings in attribute_values_text table
		//  * 
		//  * It's an open field that can be required, unique, sortable and filterable
		//  */
		// 'text_area' => array(
		// 	'label'						=> 'Text Area',
		// 	'table' 					=> 'attribute_values_text',
		// 	'handler'					=> 'Cookbook\Eav\Fields\Textarea\TextareaFieldHandler',
		// 	'validator'					=> 'Cookbook\Eav\Fields\Textarea\TextareaFieldValidator',
		// 	'handler_name'				=> 'TextareaFieldHandler',
		// 	'can_have_default_value'	=> true,
		// 	'can_be_unique'				=> true,
		// 	'can_be_localized'			=> true,
		// 	'has_options'				=> false,
		// 	'has_multiple_values'		=> false,
		// 	'sortable'					=> true
		// ),

		/**
		 * Select input
		 * 
		 * Administration will render this input as HTML5 select input
		 * values will be written as integers (ID of selected option) 
		 * in attribute_values_integer table
		 * 
		 * It's a choise field that can be required and filterable
		 * It has options (options for HTML5 select)
		 */
		'select' => array(
			'label'						=> 'Select',
			'table' 					=> 'attribute_values_integer',
			'handler'					=> 'Cookbook\Eav\Fields\Select\SelectFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Select\SelectFieldValidator',
			'handler_name'				=> 'SelectFieldHandler',
			'can_have_default_value'	=> false,
			'can_be_unique'				=> false,
			'can_be_localized'			=> true,
			'can_be_filter'				=> true,
			'can_be_searchable'			=> false,
			'has_options'				=> true,
			'has_multiple_values'		=> false,
			'sortable'					=> true
		),

		/**
		 * Integer field
		 */
		'integer' => array(
			'label'						=> 'Integer Number',
			'table' 					=> 'attribute_values_integer',
			'handler'					=> 'Cookbook\Eav\Fields\Integer\IntegerFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Integer\IntegerFieldValidator',
			'handler_name'				=> 'IntegerFieldHandler',
			'can_have_default_value'	=> true,
			'can_be_unique'				=> true,
			'can_be_localized'			=> true,
			'can_be_filter'				=> true,
			'can_be_searchable'			=> false,
			'has_options'				=> false,
			'has_multiple_values'		=> false,
			'sortable'					=> true
		),

		/**
		 * Decimal field
		 */
		'decimal' => array(
			'label'						=> 'Decimal Number',
			'table' 					=> 'attribute_values_decimal',
			'handler'					=> 'Cookbook\Eav\Fields\Decimal\DecimalFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Decimal\DecimalFieldValidator',
			'handler_name'				=> 'DecimalFieldHandler',
			'can_have_default_value'	=> true,
			'can_be_unique'				=> true,
			'can_be_localized'			=> true,
			'can_be_filter'				=> true,
			'can_be_searchable'			=> false,
			'has_options'				=> false,
			'has_multiple_values'		=> false,
			'sortable'					=> true
		),

		/**
		 * Datetime field
		 */
		'datetime' => array(
			'label'						=> 'Date & Time',
			'table' 					=> 'attribute_values_datetime',
			'handler'					=> 'Cookbook\Eav\Fields\Datetime\DatetimeFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Datetime\DatetimeFieldValidator',
			'handler_name'				=> 'DatetimeFieldHandler',
			'can_have_default_value'	=> false,
			'can_be_unique'				=> true,
			'can_be_localized'			=> true,
			'can_be_filter'				=> true,
			'can_be_searchable'			=> false,
			'has_options'				=> false,
			'has_multiple_values'		=> false,
			'sortable'					=> true
		),

		/**
		 * Relation field
		 */
		'relation' => array(
			'label'						=> 'Relation',
			'table' 					=> 'attribute_values_integer',
			'handler'					=> 'Cookbook\Eav\Fields\Relation\RelationFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Relation\RelationFieldValidator',
			'handler_name'				=> 'RelationFieldHandler',
			'can_have_default_value'	=> false,
			'can_be_unique'				=> false,
			'can_be_localized'			=> true,
			'can_be_filter'				=> true,
			'can_be_searchable'			=> false,
			'has_options'				=> false,
			'has_multiple_values'		=> false,
			'sortable'					=> false
		),

		/**
		 * Asset field
		 */
		'asset' => array(
			'label'						=> 'Asset',
			'table' 					=> 'attribute_values_integer',
			'handler'					=> 'Cookbook\Eav\Fields\Asset\AssetFieldHandler',
			'validator'					=> 'Cookbook\Eav\Fields\Asset\AssetFieldValidator',
			'handler_name'				=> 'AssetFieldHandler',
			'can_have_default_value'	=> false,
			'can_be_unique'				=> false,
			'can_be_localized'			=> true,
			'can_be_filter'				=> false,
			'can_be_searchable'			=> false,
			'has_options'				=> false,
			'has_multiple_values'		=> false,
			'sortable'					=> false
		),

		// /**
		//  * Rich text area
		//  * 
		//  * Administration will render this input as TinyMCE Rich text editor
		//  * values will be written as strings in attribute_values_text table
		//  * 
		//  * It's an open field that can be required, unique and filterable
		//  */
		// 'rich_text' => array(
		// 	'label'						=> 'Rich Text',
		// 	'table' 					=> 'attribute_values_text',
		// 	'handler'					=> 'Vizioart\Attributes\Handlers\RichTextAttributeHandler',
		// 	'handler_name'				=> 'RichTextAttributeHandler',
		// 	'value_model'				=> 'Vizioart\Attributes\Models\AttributeValueText',
		// 	'can_have_default_value'	=> true,
		// 	'can_be_required' 			=> true,
		// 	'can_be_unique'				=> true,
		// 	'can_be_filter'				=> false,
		// 	'can_be_language_dependent'	=> true,
		// 	'has_options'				=> false,
		// 	'is_relation'				=> false,
		// 	'is_asset'					=> false,
		// 	'has_multiple_values'		=> false
		// ),

		// /**
		//  * Date input
		//  * 
		//  * Administration will render this input as HTML5 date input
		//  * values will be written as dates in attribute_values_datetime table
		//  * 
		//  */
		// 'date_input' => array(
		// 	'label'						=> 'Date Input',
		// 	'table' 					=> 'attribute_values_datetime',
		// 	'handler'					=> 'Vizioart\Attributes\Handlers\DateInputAttributeHandler',
		// 	'handler_name'				=> 'DateInputAttributeHandler',
		// 	'value_model'				=> 'Vizioart\Attributes\Models\AttributeValueDatetime',
		// 	'can_have_default_value'	=> false,
		// 	'can_be_required' 			=> true,
		// 	'can_be_unique'				=> false,
		// 	'can_be_filter'				=> false,
		// 	'can_be_language_dependent'	=> true,
		// 	'has_options'				=> false,
		// 	'is_relation'				=> false,
		// 	'is_asset'					=> false,
		// 	'has_multiple_values'		=> false
		// ),

		

		// /**
		//  * Entity relation input
		//  * 
		//  * Administration will render this input as Angular-UI Select
		//  * values will be written as integers (ID of selected entity) 
		//  * in attribute_values_relations table
		//  * 
		//  * It's a choise field that can be required and filterable
		//  */
		// 'relation' => array(
		// 	'label'						=> 'Relation',
		// 	'table' 					=> 'attribute_values_relations',
		// 	'handler'					=> 'Vizioart\Attributes\Handlers\RelationAttributeHandler',
		// 	'handler_name'				=> 'RelationAttributeHandler',
		// 	'value_model'				=> 'Vizioart\Attributes\Models\AttributeValueRelation',
		// 	'can_have_default_value'	=> false,
		// 	'can_be_required' 			=> true,
		// 	'can_be_unique'				=> false,
		// 	'can_be_filter'				=> false,
		// 	'can_be_language_dependent'	=> true,
		// 	'has_options'				=> false,
		// 	'is_relation'				=> true,
		// 	'is_asset'					=> false,
		// 	'has_multiple_values'		=> false
		// ),

		// /**
		//  * Entity relation collection input
		//  * 
		//  * Administration will render this input as custom select directive
		//  * that gives user a choice of entities and a sortable 
		//  * list of selected entities in collection
		//  * 
		//  * values will be written as integers (ID of selected entity) 
		//  * in attribute_values_relations table
		//  * 
		//  * It's a multiple choise field that can be required and filterable
		//  */
		// 'relation_collection' => array(
		// 	'label'						=> 'Relation Collection',
		// 	'table' 					=> 'attribute_values_relations',
		// 	'handler'					=> 'Vizioart\Attributes\Handlers\RelationAttributeHandler',
		// 	'handler_name'				=> 'RelationAttributeHandler',
		// 	'value_model'				=> 'Vizioart\Attributes\Models\AttributeValueRelation',
		// 	'can_have_default_value'	=> false,
		// 	'can_be_required' 			=> true,
		// 	'can_be_unique'				=> false,
		// 	'can_be_filter'				=> false,
		// 	'can_be_language_dependent'	=> true,
		// 	'has_options'				=> false,
		// 	'is_relation'				=> true,
		// 	'is_asset'					=> false,
		// 	'has_multiple_values'		=> true
		// ),

		// /**
		//  * Asset input
		//  * 
		//  * Administration will render this input as custom asset directive
		//  * that gives user a choice to upload new file or choose one of already 
		//  * uploaded files, it also displays selected file (name or thumbnail if it's an image)
		//  * 
		//  * values will be written as integers (ID of selected file) 
		//  * in attribute_values_assets table
		//  * 
		//  * It's a choise field that can be required
		//  */
		// 'asset' => array(
		// 	'label'						=> 'Asset',
		// 	'table' 					=> 'attribute_values_assets',
		// 	'handler'					=> 'Vizioart\Attributes\Handlers\AssetAttributeHandler',
		// 	'handler_name'				=> 'AssetAttributeHandler',
		// 	'value_model'				=> 'Vizioart\Attributes\Models\AttributeValueAsset',
		// 	'can_have_default_value'	=> false,
		// 	'can_be_required' 			=> true,
		// 	'can_be_unique'				=> false,
		// 	'can_be_filter'				=> false,
		// 	'can_be_language_dependent'	=> true,
		// 	'has_options'				=> false,
		// 	'is_relation'				=> false,
		// 	'is_asset'					=> true,
		// 	'has_multiple_values'		=> false
		// ),

		// /**
		//  * Asset collection input
		//  * 
		//  * Administration will render this input as custom asset collection directive
		//  * that gives user a choice to upload new files or choose one or more of already 
		//  * uploaded files, it also displays sortable list of selected files
		//  * (name or thumbnail if it's an image)
		//  * 
		//  * values will be written as integers (ID of selected file) 
		//  * in attribute_values_assets table
		//  * 
		//  * It's a choise field that can be required
		//  */
		// 'asset_collection' => array(
		// 	'label'						=> 'Asset Collection',
		// 	'table' 					=> 'attribute_values_assets',
		// 	'handler'					=> 'Vizioart\Attributes\Handlers\AssetAttributeHandler',
		// 	'handler_name'				=> 'AssetAttributeHandler',
		// 	'value_model'				=> 'Vizioart\Attributes\Models\AttributeValueAsset',
		// 	'can_have_default_value'	=> false,
		// 	'can_be_required' 			=> true,
		// 	'can_be_unique'				=> false,
		// 	'can_be_filter'				=> false,
		// 	'can_be_language_dependent'	=> true,
		// 	'has_options'				=> false,
		// 	'is_relation'				=> false,
		// 	'is_asset'					=> true,
		// 	'has_multiple_values'		=> true
		// ),
	)

);