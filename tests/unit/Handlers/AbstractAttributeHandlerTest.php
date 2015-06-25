<?php
use Illuminate\Support\Facades\App;

include_once(realpath(__DIR__.'/../LaravelMocks.php'));

class AbstractAttributeHandlerTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		parent::setUp();


		$laravelMocker = new LaravelMocker();

		// create mock for DB
		$this->db = $laravelMocker->mockConnection();

		$this->query = $laravelMocker->mockQuery();

		$this->attributeManager = App::make('Cookbook\EAV\Managers\AttributeManager');

		// create stub for Abstract Repository
		$this->handler = $this	->getMockBuilder('Cookbook\EAV\Handlers\AbstractAttributeHandler')
								->setConstructorArgs(
									[
										$this->db,
										$this->attributeManager,
										'test_table'
									]
								)
								->getMockForAbstractClass();
	}

	/**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
	protected function getPackageProviders($app)
	{
		return [
			'Cookbook\EAV\EAVServiceProvider'
		];
	}

	public function testConstructor()
	{
		$this->assertInstanceOf('Cookbook\EAV\Handlers\AbstractAttributeHandler', $this->handler);

		$bag = $this->handler->getErrorBag();

		// assert if bag is instance of MessageBag
		$this->assertInstanceOf('Illuminate\Support\MessageBag', $bag);

		$hasErrors = $this->handler->hasErrors();
		// assert if bag is empty
		$this->assertFalse($hasErrors);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can't sweep after attribute that hasn't got ID.
	 */
	public function testSweepAfterAttributeInvalidArgument()
	{
		$this->handler->sweepAfterAttribute(new \stdClass());
	}

	public function testSweepAfterAttribute()
	{
		
		$attribute = new \stdClass();
		$attribute->id = 1;

		$this->query->expects($this->once())
					->method('where')
					->with(
						$this->equalTo('attribute_id'), 
						$this->equalTo('='), 
						$this->equalTo(1)
					)
					->willReturn($this->query);


		$this->query->expects($this->once())
					->method('delete')
					->willReturn(true);


		$this->db 	->expects($this->once())
					->method('table')
					->with($this->equalTo('test_table'))
					->willReturn($this->query);

		$result = $this->handler->sweepAfterAttribute($attribute);

		$this->assertTrue($result);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can't sweep after option that hasn't got ID.
	 */
	public function testSweepAfterOptionIDInvalidArgument()
	{
		$this->handler->sweepAfterOption(new \stdClass());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can't sweep after option that hasn't got attribute ID.
	 */
	public function testSweepAfterOptionAttributeIDInvalidArgument()
	{
		$option = new \stdClass();
		$option->id = 1;
		$this->handler->sweepAfterOption($option);
	}

	public function testSweepAfterOption()
	{
		
		$option = new \stdClass();
		$option->id = 1;
		$option->attribute_id = 2;

		$this->query->expects($this->exactly(2))
					->method('where')
					->withConsecutive(
						[
							$this->equalTo('attribute_id'), 
							$this->equalTo('='), 
							$this->equalTo(2)
						],
						[
							$this->equalTo('value'), 
							$this->equalTo('='), 
							$this->equalTo(1)
						]
					)
					->willReturn($this->query);


		$this->query->expects($this->once())
					->method('delete')
					->willReturn(true);


		$this->db 	->expects($this->once())
					->method('table')
					->with($this->equalTo('test_table'))
					->willReturn($this->query);

		$result = $this->handler->sweepAfterOption($option);

		$this->assertTrue($result);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can't sweep after set attribute that hasn't got attribute set ID.
	 */
	public function testSweepAfterSetAttributeAttributeSetIDInvalid()
	{
		$this->handler->sweepAfterSetAttribute(new \stdClass());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can't sweep after set attribute that hasn't got attribute ID.
	 */
	public function testSweepAfterSetAttributeAttributeIDInvalid()
	{
		$setAttribute = new \stdClass();
		$setAttribute->attribute_set_id = 1;
		$this->handler->sweepAfterSetAttribute($setAttribute);
	}

	public function testSweepAfterSetAttribute()
	{
		
		$setAttribute = new \stdClass();
		$setAttribute->attribute_set_id = 1;
		$setAttribute->attribute_id = 2;

		$this->query->expects($this->once())
					->method('join')
					->with(
						$this->equalTo('entities'),
						$this->equalTo('test_table.entity_id'),
						$this->equalTo('='),
						$this->equalTo('entities.id')
					)
					->willReturn($this->query);

		$this->query->expects($this->exactly(2))
					->method('where')
					->withConsecutive(
						[
							$this->equalTo('entities.attribute_set_id'), 
							$this->equalTo('='), 
							$this->equalTo(1)
						],
						[
							$this->equalTo('test_table.attribute_id'), 
							$this->equalTo('='), 
							$this->equalTo(2)
						]
					)
					->willReturn($this->query);


		$this->query->expects($this->once())
					->method('delete')
					->willReturn(true);


		$this->db 	->expects($this->once())
					->method('table')
					->with($this->equalTo('test_table'))
					->willReturn($this->query);

		$result = $this->handler->sweepAfterSetAttribute($setAttribute);

		$this->assertTrue($result);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage You have to provide at least one entity ID for entity sweep.
	 */
	public function testSweepAfterEntitiesEntityIDsInvalid()
	{
		$this->handler->sweepAfterEntities(0, 0);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage You have to provide attribute ID for entity sweep.
	 */
	public function testSweepAfterEntitiesAttributeIDInvalid()
	{
		$this->handler->sweepAfterEntities(1, 0);
	}

	public function testSweepAfterEntities()
	{
		
		$entityIDs = [1,2];

		$attributeID = 1;

		$this->query->expects($this->once())
					->method('whereIn')
					->with(
						$this->equalTo('entity_id'),
						$this->equalTo([1,2])
					)
					->willReturn($this->query);


		$this->query->expects($this->once())
					->method('where')
					->with(
						$this->equalTo('attribute_id'), 
						$this->equalTo('='), 
						$this->equalTo(1)
					)
					->willReturn($this->query);


		$this->query->expects($this->once())
					->method('delete')
					->willReturn(true);


		$this->db 	->expects($this->once())
					->method('table')
					->with($this->equalTo('test_table'))
					->willReturn($this->query);

		$result = $this->handler->sweepAfterEntities($entityIDs, $attributeID);

		$this->assertTrue($result);
	}

	public function testSweepAfterEntitiesSingleEntity()
	{
		
		$entityIDs = '1';

		$attributeID = 1;

		$this->query->expects($this->once())
					->method('whereIn')
					->with(
						$this->equalTo('entity_id'),
						$this->equalTo([1])
					)
					->willReturn($this->query);


		$this->query->expects($this->once())
					->method('where')
					->willReturn($this->query);


		$this->query->expects($this->once())
					->method('delete')
					->willReturn(true);


		$this->db 	->expects($this->once())
					->method('table')
					->willReturn($this->query);

		$this->handler->sweepAfterEntities($entityIDs, $attributeID);
	}

	public function testGetDefaultValue()
	{
		// provide attribute with default value
		$attribute = new \stdClass();
		$attribute->default_value = 'test value';

		// get default value
		$result = $this->handler->getDefaultValue($attribute);

		// it should be attribute default value
		$this->assertEquals($attribute->default_value, $result);

		// provide attribute without default value
		$attribute->default_value = null;

		// but provide option that is default
		$option = new \stdClass();
		$option->id = 1;
		$option->default = true;
		$options = [$option];

		// get default value
		$result = $this->handler->getDefaultValue($attribute, $options);

		// it should be equal to option ID
		$this->assertEquals($option->id, $result);


		// provide no default value
		// get default value
		$result = $this->handler->getDefaultValue($attribute);

		// it should be null
		$this->assertNull($result);
	}

	public function testRequiredValidation()
	{
		$attribute = new \stdClass();

		$attribute->required = true;
		$attribute->unique = false;
		$attribute->localized = false;

		$value = [
			'language_id' => 0,
			'value' => ''
		];

		$result = $this->handler->updateValue($value, $attribute);

		$this->assertFalse($result);

		$errors = $this->handler->getErrors();

		$this->assertEquals(['This is a required field.'], $errors);
	}

	public function testUniqueValidation()
	{
		$attribute = new \stdClass();

		$attribute->id = 1;
		$attribute->required = false;
		$attribute->unique = true;
		$attribute->localized = false;

		$value = [
			'language_id' => 0,
			'value' => '123',
			'entity_id' => 1,
			'attribute_id' => 1
		];

		$this->query->expects($this->any())
					->method('where')
					->willReturn($this->query);

		$this->query->expects($this->any())
					->method('first')
					->willReturn(true);

		$this->db 	->expects($this->any())
					->method('table')
					->willReturn($this->query);

		$result = $this->handler->updateValue($value, $attribute);

		// result should be true because of empty value
		$this->assertFalse($result);

		$errors = $this->handler->getErrors();

		$this->assertEquals(['This needs to be a unique value.'], $errors);

	}

	public function testLocalizedValidation()
	{
		$attribute = new \stdClass();

		$attribute->id = 1;
		$attribute->required = false;
		$attribute->unique = false;
		$attribute->localized = true;

		$value = [
			'language_id' => 0,
			'value' => '123',
			'entity_id' => 1,
			'attribute_id' => 1
		];

		$result = $this->handler->updateValue($value, $attribute);

		// result should be true because of empty value
		$this->assertFalse($result);

		$errors = $this->handler->getErrors();

		$this->assertEquals(['You need to specify a language.'], $errors);

	}

	public function testCombinedValidation()
	{
		$attribute = new \stdClass();

		$attribute->id = 1;
		$attribute->required = true;
		$attribute->unique = false;
		$attribute->localized = true;

		$value = [
			'language_id' => 0,
			'value' => '',
			'entity_id' => 1,
			'attribute_id' => 1
		];

		$result = $this->handler->updateValue($value, $attribute);

		// result should be true because of empty value
		$this->assertFalse($result);

		$errors = $this->handler->getErrors();

		$this->assertEquals(['This is a required field.', 'You need to specify a language.'], $errors);
	}
}
