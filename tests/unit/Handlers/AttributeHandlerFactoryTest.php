<?php
// use Illuminate\Support\Facades\App;

class AttributeHandlerFactoryTest extends Orchestra\Testbench\TestCase
{
	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		parent::setUp();
		
		$app = $this->createApplication();

		$app->bind(
			'Cookbook\EAV\Handlers\TextInputAttributeHandler', 
			function($app)
			{
				return new AttributeHandlerMock();
			}
		);

		$this->factory = $app->make('Cookbook\EAV\Handlers\AttributeHandlerFactory');
	}

	/**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
	protected function getEnvironmentSetUp($app)
	{
		$textInput = [
			'label'						=> 'Text Input',
			'table' 					=> 'attribute_values_varchar',
			'handler'					=> 'Cookbook\EAV\Handlers\TextInputAttributeHandler',
			'can_have_default_value'	=> true,
			'can_be_required' 			=> true,
			'can_be_unique'				=> true,
			'can_be_filter'				=> false,
			'can_be_language_dependent'	=> true,
			'has_options'				=> false,
			'is_relation'				=> false,
			'is_asset'					=> false,
			'has_multiple_values'		=> false
		];

		$testInput = [
			'label'						=> 'Test Input',
		];

		$app['config']->set(
			'Cookbook::eav.field_types', 
			[
				'text_input' => $textInput, 
				'test' => $testInput
			]
		);
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
		$this->assertInstanceOf('Cookbook\EAV\Handlers\AttributeHandlerFactory', $this->factory);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage No such field type as: "test2".
	 */
	public function testMakeTypeThatIsNotAvailable()
	{
		$type = $this->factory->make('test2');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Field type must have defined handler.
	 */
	public function testMakeTypeThatDoesNotHaveHandler()
	{
		$type = $this->factory->make('test');
	}

	public function testMakeType()
	{
		$type = $this->factory->make('text_input');
		$this->assertInstanceOf('AttributeHandlerMock', $type);

	}


}

class AttributeHandlerMock 
{

}