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
use Congraph\Contracts\Eav\EntityTypeRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Validation\Validator;


/**
 * AttributeSetCreateValidator class
 * 
 * Validating command for creating attribute set
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
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
	public function __construct(EntityTypeRepositoryContract $entityTypeRepository, AttributeRepositoryContract $attributeRepository)
	{

		$this->entityTypeRepository = $entityTypeRepository;
		$this->attributeRepository = $attributeRepository;

		$this->rules = [
			'code'					=> 'required|unique:attribute_sets,code',
			'entity_type_id'        => ['required_without:entity_type', 'exists:entity_types,id' ],
			'name'					=> 'required',
			'primary_attribute_id'	=> 'sometimes|required|exists:attributes,id',
			'primary_attribute'     => [ 'required_without:primary_attribute_id', 'exists:attributes,code' ],
			'attributes'			=> 'sometimes|array',
			'attributes.*.id'		=> 'required_without:attributes.*.code|integer|exists:attributes,id',
			'attributes.*.code'		=> 'required_without:attributes.*.id|string|exists:attributes,code'
		];

		// $this->attributeRules = 
		// [
		// 	'id'			=> ['required_without:attributes.code', 'integer', 'exists:attributes,id'],
		// 	'code'			=> [ 'required_without:attributes.id', 'string', 'exists:attributes,code']
		// ];

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
		if( ! isset($command->params['entity_type_id']) ) {
			$this->rules['entity_type'] = 'required|array';
			if( isset($command->params['entity_type']) && is_string($command->params['entity_type'])) {
				$this->rules['entity_type'] = 'required_without:entity_type_id|exists:entity_types,code';
			}
			else {
				$this->rules['entity_type.id'] = 'required_without:entity_type_id|exists:entity_types,id';
			}
		}

		$validator = $this->newValidator($command->params, $this->rules, true);

		$this->setValidator($validator);

		$this->validateParams($command->params, null, true);

		if( $this->exception->hasErrors() ) {
			throw $this->exception;
		}

		if ( ! isset($command->params['entity_type_id']) ) {
			if( isset($command->params['entity_type']) && is_string($command->params['entity_type']) ) {
				$entityType = $this->entityTypeRepository->get(['code' => $command->params['entity_type']]);
				if( empty($entityType) ) {
					$this->exception->addErrors(['entity_type' => 'Invalid entity type.']);
					throw $this->exception;
				}
				$entityType = $entityType[0];
				$command->params['entity_type_id'] = $entityType->id;
			}
			elseif( isset($command->params['entity_type']) && is_array($command->params['entity_type']) && isset($command->params['entity_type']['id'])) {
				$command->params['entity_type_id'] = $command->params['entity_type']['id'];
			}
			else {
				$this->exception->addErrors(['entity_type_id' => 'This is required.']);
				throw $this->exception;
			}
		}

		unset($command->params['entity_type']);

		if( !isset($command->params[ 'primary_attribute_id']) 
			&& isset($command->params['primary_attribute']) 
			&& is_string($command->params['primary_attribute'])) {
				
			$attribute = $this->attributeRepository->get(['code' => $command->params[ 'primary_attribute']]);
			$attribute = $attribute[0];
			$command->params[ 'primary_attribute_id'] = $attribute->id;
			unset( $command->params['primary_attribute'] );
		}

		$invalidPrimaryAttribute = true;

		if( ! empty($command->params['attributes']) ) {
			$attributeIds = [];
			foreach ($command->params['attributes'] as $key => &$value) {
				if(!isset( $value['id'])) {
					$attributes = $this->attributeRepository->get(['code' => $value['code']]);
					$attribute = $attributes[0];
					$value['id'] = $attribute->id;
				}
				unset( $value['code']) ;
				if(in_array($value['id'], $attributeIds)) {
					$this->exception->setErrorKey('attribute-set.attributes.' . $key);
					$this->exception->addErrors('Can\'t use same attribute more than once.');
				}
				$attributeIds[$key] = $value['id'];

				if($value['id'] === $command->params['primary_attribute_id']) {
					$invalidPrimaryAttribute = false;
				}
			}

			$entityType = $this->entityTypeRepository->fetch($command->params['entity_type_id']);

			if( !$entityType->localized ) {
				$attributes = $this->attributeRepository->get(['id' => ['in' => $attributeIds]]);

				foreach ($attributes as $attribute) {
					if( $attribute->localized ) {
						$key = array_search($attribute->id, $attributeIds);
						$this->exception->setErrorKey('attribute-set.attributes.' . $key);
						$this->exception->addErrors('Can\'t add localized attribute to entity type that isn\'t localized');
					}
				}
			}
			
		}

		if($invalidPrimaryAttribute) {
			$this->exception->setErrorKey('attribute-set.primary_attribute_id');
			$this->exception->addErrors('Invalid primary attribute chosen');
		}
		
		if( $this->exception->hasErrors() ) {
			throw $this->exception;
		}
		
	}
}