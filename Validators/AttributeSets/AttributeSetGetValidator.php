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

use Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Exceptions\BadRequestException;
use Illuminate\Support\Facades\Validator;


/**
 * AttributeSetGetValidator class
 * 
 * Validating command for getting attribute sets
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AttributeSetGetValidator
{

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

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
			'name',
			'entity_type_id',
			'created_at'
		];

		$this->defaultSorting = ['-created_at'];
	}


	/**
	 * Validate AttributeSetGetCommand
	 * 
	 * @param Cookbook\Eav\Commands\AttributeSets\AttributeSetGetCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(AttributeSetGetCommand $command)
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
			throw new BadRequestException(['There are no available filters for attributes.']);
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
			return;
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
				throw new BadRequestException(['Sorting by \'' . $criteria . '\' is not allowed.']);
			}
		}
	}

	protected function validateInclude(&$include)
	{
		if( ! empty($include) )
		{
			throw new BadRequestException('There are no available includes for attributes.');
		}

		$include = [];
	}
}