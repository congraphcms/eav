<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Validators;

use Cookbook\EAV\Commands\AttributeGetCommand;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Exceptions\BadRequestException;
use Illuminate\Support\Facades\Validator;


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
class AttributeGetValidator
{

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

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
	}


	/**
	 * Validate AttributeGetCommand
	 * 
	 * @param Cookbook\EAV\Commands\AttributeGetCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(AttributeGetCommand $command)
	{
		$params = $command->request->all();

		$this->validateFilters($params['filter']);
		$this->validatePaging($params['offset'], $params['limit']);
		$this->validateSorting($params['sort']);
		$this->validateInclude($params['include']);
	}

	protected function validateFilters(&$filters)
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

	protected function validatePaging(&$offset, &$limit)
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

	protected function validateInclude(&$include)
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