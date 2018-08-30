<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Eav\Fields\Datetime;

use Congraph\Eav\Fields\AbstractFieldValidator;
use Congraph\Eav\Managers\AttributeManager;
use Congraph\Core\Exceptions\ValidationException;
use DateTime;

/**
 * DatetimeFieldValidator class
 * 
 * Validating fields and values of type datetime
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class DatetimeFieldValidator extends AbstractFieldValidator
{
	protected $dateFormat = 'Y-m-d\TH:i:sO';

	/**
	 * List of available operations for filtering entities
	 *
	 * @var array
	 */
	protected $availableFilterOperations = ['e', 'ne', 'in', 'nin', 'gt', 'gte', 'lt', 'lte'];

	/**
	 * DB table for SQL
	 *
	 * @var array
	 */
	protected $table = 'attribute_values_datetime';

	/**
	 * Validate attribute value
	 * 
	 * This function can be extended by specific attribute handler
	 * 
	 * @param array $value
	 * @param object $attribute
	 * 
	 * @return boolean
	 */
	public function validateValue($value, $attribute, $entity_id = 0)
	{

		parent::validateValue($value, $attribute, $entity_id);

		if($value && DateTime::createFromFormat($this->dateFormat, $value) === false)
		{
			throw new ValidationException(['Invalid date or time.']);
		}
	}
}