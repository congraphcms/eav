<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Validators\AttributeSets;

use Congraph\Contracts\Eav\AttributeRepositoryContract;
use Congraph\Contracts\Eav\AttributeSetRepositoryContract;
use Congraph\Contracts\Eav\EntityTypeRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Exceptions\NotFoundException;
use Congraph\Core\Validation\Validator;


/**
 * AttributeSetUpdateValidator class
 * 
 * Validating command for updating attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetUpdateValidator extends Validator
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
	 * Repository for attribute sets
	 * 
	 * @var Congraph\Contracts\Eav\AttributeSetRepositoryContract
	 */
	protected $attributeSetRepository;

	/**
	 * Repository for attributes
	 * 
	 * @var Congraph\Contracts\Eav\AttributeRepositoryContract
	 */
	protected $attributeRepository;

	/**
	 * Repository for entity types
	 * 
	 * @var Congraph\Contracts\Eav\EntityTypeRepositoryContract
	 */
	protected $entityTypeRepository;

	/**
	 * Create new AttributeSetCreateValidator
	 * 
	 * @return void
	 */
	public function __construct(AttributeSetRepositoryContract $attributeSetRepository, EntityTypeRepositoryContract $entityTypeRepository, AttributeRepositoryContract $attributeRepository)
	{

		$this->attributeSetRepository = $attributeSetRepository;
		$this->entityTypeRepository = $entityTypeRepository;
		$this->attributeRepository = $attributeRepository;

		$this->rules = [
			'code'					=> 'sometimes|required|unique:attribute_sets,code',
			'name'					=> '',
			'primary_attribute_id'	=> 'sometimes|exists:attributes,id',
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
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(RepositoryCommand $command)
	{
		$attributeSet = $this->attributeSetRepository->fetch($command->id);
		$command->params['id'] = $command->id;
		$validator = $this->newValidator($command->params, $this->rules);

		if( isset($command->params['attributes']) )
		{
			$validator->each('attributes', $this->attributeRules);
		}
		$this->setValidator($validator);

		$this->validateParams($command->params, null, true);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

		$primaryAttributeId = (isset($command->params['primary_attribute_id']))?$command->params['primary_attribute_id']:$attributeSet->primary_attribute_id;

		$invalidPrimaryAttribute = true;

		if( ! empty($command->params['attributes']) )
		{
			$entityType = $this->entityTypeRepository->fetch($attributeSet->entity_type_id);

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
					continue;
				}
				$attributeIds[$key] = $value['id'];

				if($value['id'] == $primaryAttributeId)
				{
					$invalidPrimaryAttribute = false;
				}
			}

			$attributes = $this->attributeRepository->get(['id' => ['in' => $attributeIds]]);

			foreach ($attributes as $attribute)
			{
				if( $attribute->localized && ! $entityType->localized )
				{
					$key = array_search($attribute->id, $attributeIds);
					$this->exception->setErrorKey('attribute-set.attributes.' . $key);
					$this->exception->addErrors('Can\'t add localized attribute to entity type that isn\'t localized');
				}
			}
		}
		else
		{
			foreach ($attributeSet->attributes as $key => $value)
			{
				if($value->id == $primaryAttributeId)
				{
					$invalidPrimaryAttribute = false;
				}
			}
		}

		if($invalidPrimaryAttribute)
		{
			$this->exception->setErrorKey('attribute-set.primary_attribute_id');
			$this->exception->addErrors('Invalid primary attribute chosen');
		}
		
		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
	}
}