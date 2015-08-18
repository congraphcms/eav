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
			'admin_label',
			'field_type',
			'created_at'
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
		$params = $command->params;

		if( ! empty($params['filter']) )
		{
			$this->validateFilters($params['filter']);
		}

		if( empty($params['offset']) )
		{
			$params['offset'] = 0;
		}
		if( empty($params['limit']) )
		{
			$params['limit'] = 0;
		}
		$this->validatePaging($params['offset'], $params['limit']);
		
		if( ! empty($params['sort']) )
		{
			$this->validateSorting($params['sort']);
		}
		
		if( ! empty($params['include']) )
		{
			$this->validateInclude($params['include']);
		}
	}

	protected function validateFilters($filters)
	{
		if( ! empty($filters) )
		{
			$e = new BadRequestException();
			$e->setErrorKey('attributes.filter');
			$e->addErrors('There are no available filters for attributes.');

			throw $e;
		}

		$filters = [];
	}

	protected function validatePaging($offset = 0, $limit = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);
	}

	protected function validateSorting($sort)
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

	protected function validateInclude($include)
	{
		if( ! empty($include) )
		{
			$e = new BadRequestException();
			$e->setErrorKey('attributes.include');
			$e->addErrors('There are no available includes for attributes.');

			throw $e;
		}

		$include = [];
	}
}