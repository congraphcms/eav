<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\EntityTypes;

use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;


/**
 * EntityTypeCreateValidator class
 * 
 * Validating command for creating entity type
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeCreateValidator extends Validator
{


	/**
	 * Set of rules for validating attribute set
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Create new EntityTypeCreateValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{

		$this->rules = [
			'code'					=> ['required', 'unique:entity_types,code', 'regex:/^[0-9a-z-_]*$/'],
			'endpoint'				=> ['required', 'unique:entity_types,endpoint', 'regex:/^[0-9a-z-_]*$/'],
			'name'					=> 'required|min:3|max:250',
			'plural_name'			=> 'required|min:3|max:250',
			'multiple_sets'			=> 'sometimes|boolean',
			'has_workflow'			=> 'sometimes|boolean',
			'localized'				=> 'sometimes|boolean'
		];

		parent::__construct();

		$this->exception->setErrorKey('entity-type');
	}


	/**
	 * Validate RepositoryCommand
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(RepositoryCommand $command)
	{
		if( ! empty($command->params['has_workflow']) )
		{
			$this->rules['workflow_id'] = ['required_without:workflow', 'exists:workflows,id'];
			$this->rules['workflow'] = ['sometimes', 'array'];
			if(isset($command->params['workflow']))
			{
				$this->rules['workflow.id'] = ['required', 'exists:workflows,id'];
			}
			
			$this->rules['default_point_id'] = ['required_without:default_point', 'exists:workflows,id'];
			$this->rules['default_point'] = ['sometimes', 'array'];
			if(isset($command->params['default_point']))
			{
				$this->rules['default_point.id'] = ['required', 'exists:workflow_points,id'];
			}
			$this->rules['localized_workflow'] = ['sometimes', 'boolean'];

		}

		$this->validateParams($command->params, $this->rules, true);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

		if( ! empty($command->params['has_workflow']) )
		{
			if ( ! isset($command->params['workflow_id']) )
			{
				$command->params['workflow_id'] = $command->params['workflow']['id'];
			}

			if ( ! isset($command->params['default_point_id']) )
			{
				$command->params['default_point_id'] = $command->params['default_point']['id'];
			}
		}
		unset($command->params['workflow']);
		unset($command->params['default_point']);
		
	}
}