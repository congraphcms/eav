<?php

class LaravelMocker extends PHPUnit_Framework_TestCase
{
	/**
	 * Get mock for Illuminate\Database\Connection
	 */
	public function mockConnection()
	{
		return $this->getMockBuilder('Illuminate\Database\Connection')
					->disableOriginalConstructor()
					->setMethods(
						[
							'beginTransaction',
							'commit',
							'rollback',
							'table'
						]
					)
					->getMock();
	}

	/**
	 * Get mock for Illuminate\Database\Query\Builder
	 */
	public function mockQuery()
	{
		return $this->getMockBuilder('Illuminate\Database\Query\Builder')
					->disableOriginalConstructor()
					->setMethods(
						[
							'select',
							'join',
							'where',
							'whereIn',
							'get',
							'first',
							'insert',
							'update',
							'delete'
						]
					)
					->getMock();
	}

	
}