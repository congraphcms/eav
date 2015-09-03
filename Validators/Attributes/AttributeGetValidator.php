<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Eav\Validators\Attributes;

use Cookbook\Eav\Commands\Attributes\AttributeGetCommand;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Exceptions\BadRequestException;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;


/**
 * AttributeGetValidator class
 * 
 * Validating command for fetching attribute by ID
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeGetValidator extends Validator
{

	/**
	 * Available fields for sorting
	 *
	 * @var array
	 */
	protected $availableSorting;

	/**
	 * Default sorting criteria
	 *
	 * @var array
	 */
	protected $defaultSorting;

	/**
	 * Create new AttributeGetValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{

		$this->availableSorting = [
			'id',
			'code',
			'field_type',
			'created_at'
		];

		$this->availableFilters = [
			'id' 			=> ['e', 'ne', 'lt', 'lte', 'gt', 'gte', 'in', 'nin'],
			'code'			=> ['e', 'ne', 'in', 'nin'],
			'field_type'	=> ['e', 'ne', 'in', 'nin'],
			'localized'		=> ['e', 'ne'],
			'unique'		=> ['e', 'ne'],
			'required'		=> ['e', 'ne'],
			'filterable'	=> ['e', 'ne'],
			'status'		=> ['e', 'ne', 'in', 'nin'],
			'created_at'	=> ['lt', 'lte', 'gt', 'gte']
		];

		$this->defaultSorting = ['-created_at'];

		parent::__construct();
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

		if( ! empty($command->params['filter']) )
		{
			$this->validateFilters($command->params['filter']);
		}

		if( empty($command->params['offset']) )
		{
			$command->params['offset'] = 0;
		}
		if( empty($command->params['limit']) )
		{
			$command->params['limit'] = 0;
		}
		$this->validatePaging($command->params['offset'], $command->params['limit']);
		
		if( ! empty($command->params['sort']) )
		{
			$this->validateSorting($command->params['sort']);
		}
		
		// if( ! empty($command->params['include']) )
		// {
		// 	$this->validateInclude($command->params['include']);
		// }
	}

	protected function validateFilters(&$filters)
	{
		if( ! empty($filters) )
		{
			foreach ($filters as $field => &$filter) {
				if( ! array_key_exists($field, $this->availableFilters) )
				{
					$e = new BadRequestException();
					$e->setErrorKey('attributes.filter');
					$e->addErrors('Filtering by \'' . $field . '\' is not allowed.');

					throw $e;
				}
				if( ! is_array($filter) )
				{
					if( ! in_array('e', $this->availableFilters[$field]) )
					{
						$e = new BadRequestException();
						$e->setErrorKey('attributes.filter');
						$e->addErrors('Filter operation is not allowed.');

						throw $e;
					}

					continue;
				}

				foreach ($filter as $operation => &$value) {
					if( ! in_array($operation, $this->availableFilters[$field]) )
					{
						$e = new BadRequestException();
						$e->setErrorKey('attributes.filter');
						$e->addErrors('Filter operation is not allowed.');

						throw $e;
					}

					if($operation == 'in' || $operation == 'nin')
					{
						if( ! is_array($value) )
						{
							$value = explode(',', strval($value));
						}
					}
				}
			}

			return;
		}

		$filters = [];
	}

	protected function validatePaging(&$offset = 0, &$limit = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);
	}

	protected function validateSorting(&$sort)
	{
		if( empty($sort) )
		{
			$sort = $this->defaultSorting;
		}

		if( ! is_array($sort) )
		{
			$sort = [$sort];
		}

		foreach ($sort as $criteria)
		{
			if( empty($criteria) )
			{
				continue;
			}

			if( $criteria[0] === '-' )
			{
				$criteria = substr($criteria, 1);
			}

			if( ! in_array($criteria, $this->availableSorting) )
			{
				$e = new BadRequestException();
				$e->setErrorKey('attributes.sort');
				$e->addErrors('Sorting by \'' . $criteria . '\' is not allowed.');

				throw $e;
			}
		}
	}
}