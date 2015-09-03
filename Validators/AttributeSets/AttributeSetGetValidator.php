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

use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Exceptions\BadRequestException;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;


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
class AttributeSetGetValidator extends Validator
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
			'name',
			'entity_type_id',
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

	protected function validateFilters($filters)
	{
		if( ! empty($filters) )
		{
			$e = new BadRequestException();
			$e->setErrorKey('attribute-sets.filter');
			$e->addErrors('There are no available filters for attribute sets.');

			throw $e;
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
			
			if( $criteria[0] === '-' )
			{
				$criteria = substr($criteria, 1);
			}

			if( ! in_array($criteria, $this->availableSorting) )
			{
				$e = new BadRequestException();
				$e->setErrorKey('attribute-sets.sort');
				$e->addErrors('Sorting by \'' . $criteria . '\' is not allowed.');

				throw $e;
			}
		}
	}

}