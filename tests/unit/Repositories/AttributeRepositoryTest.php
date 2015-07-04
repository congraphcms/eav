<?php
use Illuminate\Support\Facades\App;

include_once(realpath(__DIR__.'/../LaravelMocks.php'));

class AttributeRepositoryTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		parent::setUp();


		$laravelMocker = new LaravelMocker();

		$app = $this->createApplication();

		// create mock for DB
		$this->db = $laravelMocker->mockConnection();

		$this->query = $laravelMocker->mockQuery();

		$this->repo = App::make('Cookbook\Eav\Repositories\AttributeRepository');

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
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

	protected function getPackageProviders($app)
	{
		return ['Cookbook\Eav\EavServiceProvider'];
	}

	public function testConstructor()
	{
		$this->assertInstanceOf('Cookbook\Eav\Repositories\AttributeRepository', $this->repo);

		
	}
}