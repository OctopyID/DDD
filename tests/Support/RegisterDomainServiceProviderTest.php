<?php

namespace Octopy\DDD\Tests\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Mockery\MockInterface;
use Octopy\DDD\Support\RegisterDomainServiceProvider;
use Octopy\DDD\Tests\TestCase;
use ReflectionClass;
use ReflectionException;

class RegisterDomainServiceProviderTest extends TestCase
{
    /**
     * @var RegisterDomainServiceProvider
     */
    protected RegisterDomainServiceProvider $auto;

    /**
     * @return void
     */
    protected function setUp() : void
    {
        parent::setUp();

        /**
         * @var Application $app
         */
        $app = $this->mock(Application::class, function (MockInterface $mock) {
            $mock->shouldReceive('path')->andReturn(
                __DIR__ . '/../App'
            );

            $mock->shouldReceive('getNamespace')->andReturn('Octopy\DDD\Tests\App');
        });

        $this->auto = new RegisterDomainServiceProvider($app);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testItCanFindServiceProvider() : void
    {
        $this->assertCount(2, $this->auto->discover());

        $this->auto->discover()->each(function (ReflectionClass $class) {
            $this->assertInstanceOf(ServiceProvider::class, $class->newInstanceWithoutConstructor());
        });
    }
}
