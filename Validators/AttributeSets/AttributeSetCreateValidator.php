<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\AttributeSets;

use Cookbook\Contracts\Eav\AttributeRepositoryContract;
use Cookbook\Contracts\Eav\EntityTypeRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;


/**
 * AttributeSetCreateValidator class
 * 
 * Validating command for creating attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetCreateValidator extends Validator
{


	/**
	 * Set of rules for validating attribute set
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Set of rules for validating set attributes
	 *
	 * @var array
	 */
	protected $attributeRules;

	/**
	 * Repository for attributes
	 * 
	 * @var Cookbook\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Repository for entity types
	 * 
	 * @var Cookbook\Contracts\Eav\EntityTypeRepositoryContract
	 */
	protected $entityTypeRepository;

	/**
	 * Create new AttributeSetCreateValidator
	 * 
	 * @return void
	 */
	public function __construct(EntityTypeRepositoryContract $entityTypeRepository, AttributeRepositoryContract $attributeRepository)
	{

		$this->entityTypeRepository = $entityTypeRepository;
		$this->attributeRepository = $attributeRepository;

		$this->rules = [
			'code'					=> 'required|unique:attribute_sets,code',
			'entity_type_id'        => ['required_without:entity_type', 'exists:entity_types,id' ],
			'entity_type'           => ['sometimes', 'required'],
			'entity_type.id'        => ['sometimes', 'exists:entity_types,id'],
			'name'					=> 'required',
			'attributes'			=> 'sometimes|array'
		];

		$this->attributeRules = 
		[
			'id'			=> 'required|integer|exists:attributes,id'
		];

		parent::__construct();

		$this->exception->setErrorKey('attribute-set');
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

		$validator = $this->newValidator($command->params, $this->rules, true);

		if( isset($command->params['attributes']) )
		{
			$validator->each('attributes', $this->attributeRules);
		}

		if( ! isset($command->params['entity_type_id']) )
		{
			$this->rules['entity_type'] = 'required';
			if( isset($command->params['entity_type']) && is_string($command->params['entity_type']))
			{
				$this->rules['entity_type'] = 'required|exists:entity_types,code';
			}
			else
			{
				$this->rules['entity_type.id'] = 'required|exists:entity_types,id';
			}
		}

		$this->setValidator($validator);

		$this->validateParams($command->params, null, true);

		if ( ! isset($command->params['entity_type_id']) )
		{
			if( is_string($command->params['entity_type']) )
			{
				$entityType = $this->entityTypeRepository->get(['code' => $command->params['entity_type']]);
				if( empty($entityType) )
				{
					$this->exception->addErrors(['entity_type' => 'Invalid entity type.']);
					throw $this->exception;
				}
				$entityType = $entityType[0];
				$command->params['entity_type_id'] = $entityType->id;
			}
			elseif( is_array($command->params['entity_type']) && isset($command->params['entity_type']['id']))
			{
				$command->params['entity_type_id'] = $command->params['entity_type']['id'];
			}
			else
			{
				$this->exception->addErrors(['entity_type_id' => 'This is required.']);
				throw $this->exception;
			}
		}

		unset($command->params['entity_type']);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

		if( ! empty($command->params['attributes']) )
		{
			$entityType = $this->entityTypeRepository->fetch($command->params['entity_type_id']);

			if($entityType->localized)
			{
				return;
			}

			$attributeIds = [];
			foreach ($command->params['attributes'] as $key => $value)
			{
				if(in_array($value['id'], $attributeIds))
				{
					$this->exception->setErrorKey('attribute-set.attributes.' . $key);
					$this->exception->addErrors('Can\'t use same attribute more than once.');
					$key2 = array_search($value['id'], $attributeIds);
					$this->exception->setErrorKey('attribute-set.attributes.' . $key2);
					$this->exception->addErrors('Can\'t use same attribute more than once.');
				}
				$attributeIds[$key] = $value['id'];
			}

			$attributes = $this->attributeRepository->get(['id' => ['in' => $attributeIds]]);

			foreach ($attributes as $attribute)
			{
				if($attribute->localized)
				{
					$key = array_search($attribute->id, $attributeIds);
					$this->exception->setErrorKey('attribute-set.attributes.' . $key);
					$this->exception->addErrors('Can\'t add localized attribute to entity type that isn\'t localized');
				}
			}
		}
		
		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
		
	}
}