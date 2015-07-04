<?php
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class AttributeManagerTest extends Orchestra\Testbench\TestCase
{
	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		parent::setUp();
		

		$this->manager = App::make('Cookbook\Eav\Managers\AttributeManager');
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
		$app['config']->set('Cookbook::eav.field_types', ['test' => '123']);
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
			'Cookbook\Eav\EavServiceProvider'
		];
	}

	public function testConstructor()
	{
		$this->assertInstanceOf('Cookbook\Eav\Managers\AttributeManager', $this->manager);
	}

	public function testGetFieldTypes()
	{
		$types = $this->manager->getFieldTypes();

		$this->assertTrue(is_array($types));

		$this->assertArrayHasKey('test', $types);

		$this->assertEquals('123', $types['test']);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage No such field type as: "test2".
	 */
	public function testGetFieldTypeThatIsNotAvailable()
	{
		$type = $this->manager->getFieldType('test2');
	}

	public function testGetFieldType()
	{
		$type = $this->manager->getFieldType('test');

		$this->assertEquals('123', $type);
	}
}