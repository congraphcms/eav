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

use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;

/**
 * EntityTypeUpdateValidator class
 * 
 * Validating command for updating entity type
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EntityTypeUpdateValidator extends Validator
{


	/**
	 * Set of rules for validating attribute set
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Repository for entity types
	 * 
	 * @var Cookbook\Contracts\Eav\EntityTypeRepositoryContract
	 */
	protected $entityTypeRepository;

	/**
	 * Create new EntityTypeUpdateValidator
	 * 
	 * @return void
	 */
	public function __construct(EntityTypeRepositoryContract $entityTypeRepository)
	{
		$this->entityTypeRepository = $entityTypeRepository;

		$this->rules = [
			'code'					=> ['sometimes', 'required', 'unique:entity_types,code', 'regex:/^[0-9a-z-_]*$/'],
			'endpoint'				=> ['sometimes', 'required', 'unique:entity_types,endpoint', 'regex:/^[0-9a-z-_]*$/'],
			'name'					=> 'sometimes|min:3|max:250',
			'plural_name'			=> 'sometimes|min:3|max:250',
			'multiple_sets'			=> 'sometimes|boolean',
			'default_point_id'		=> ['sometimes', 'exists:workflow_points,id'],
			'default_point'			=> 'sometimes|array',
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
		$entityType = $this->entityTypeRepository->fetch($command->id);
		$command->params['id'] = $command->id;
		if(isset($command->params['default_point']))
		{
			$this->rules['default_point.id'] = ['required', 'exists:workflow_points,id'];
		}

		$this->validateParams($command->params, $this->rules, true);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

		if( ! isset($command->params['default_point_id']) && isset($command->params['default_point']) )
		{
			$command->params['default_point_id'] = $command->params['default_point']['id'];
		}

		unset($command->params['default_point']);
	}
}