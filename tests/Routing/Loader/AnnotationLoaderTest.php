<?php

/*
 * slim-routing (https://github.com/juliangut/slim-routing).
 * Slim framework routing.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Loader;

use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Loader\AnnotationLoader;
use Jgut\Slim\Routing\Naming\SnakeCase;
use PHPUnit\Framework\TestCase;

/**
 * Annotation loader tests.
 */
class AnnotationLoaderTest extends TestCase
{
    /**
     * @var AnnotationLoader
     */
    protected $loader;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        $configuration->expects(self::any())
            ->method('getNamingStrategy')
            ->will(self::returnValue(new SnakeCase()));
        /* @var Configuration $configuration */

        $this->loader = new AnnotationLoader($configuration);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Path "\non\existing\path" does not exist
     */
    public function testInvalidPath()
    {
        $this->loader->load(['\non\existing\path']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Routes can not be defined in constructor or destructor in class .+$/
     */
    public function testConstructorDefinedRoute()
    {
        $this->loader->load([__DIR__ . '/../Files/Annotation/Invalid/ConstructorDefined/ConstructorDefinedRoute.php']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Routes can not be defined in private or protected methods in class .+$/
     */
    public function testPrivateDefinedRoute()
    {
        $this->loader->load([__DIR__ . '/../Files/Annotation/Invalid/PrivateDefined/PrivateDefinedRoute.php']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Class .+ does not define any route$/
     */
    public function testNoRoutesRoute()
    {
        $this->loader->load([__DIR__ . '/../Files/Annotation/Invalid/NoRoutes']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Referenced group "unknown" on class .+ is not defined$/
     */
    public function testUnknownGroupRoute()
    {
        $this->loader->load([__DIR__ . '/../Files/Annotation/Invalid/UnknownGroup']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Circular reference detected with group "circular" on class .+$/
     */
    public function testCircularReferenceRoute()
    {
        $this->loader->load([__DIR__ . '/../Files/Annotation/Invalid/CircularReference']);
    }

    public function testEmptyMethods()
    {
        $routes = $this->loader->load([__DIR__ . '/../Files/Annotation/Valid']);

        $loaded = [
            [
                'name' => 'abstract_grouped_four',
                'priority' => 0,
                'methods' => ['GET'],
                'pattern' => '/abstract/dependent/four',
                'placeholders' => [],
                'middleware' => ['fourMiddleware', 'dependentMiddleware', 'abstractMiddleware'],
                'invokable' => ['Jgut\\Slim\\Routing\\Tests\\Files\\Annotation\\Valid\\DependentRoute', 'actionFour'],
            ],
            [
                'name' => '',
                'priority' => 0,
                'methods' => ['GET'],
                'pattern' => '/grouped/{section}/two/{id}',
                'placeholders' => [
                    'section' => '[A-Za-z]+',
                ],
                'middleware' => ['twoMiddleware', 'groupedMiddleware'],
                'invokable' => ['Jgut\\Slim\\Routing\\Tests\\Files\\Annotation\\Valid\\GroupedRoute', 'actionTwo'],
            ],
            [
                'name' => '',
                'priority' => 0,
                'methods' => ['GET'],
                'pattern' => '/grouped/{section}/three/{id}',
                'placeholders' => [
                    'section' => '[A-Za-z]+',
                    'id' => '\\d+',
                ],
                'middleware' => ['threeMiddleware', 'groupedMiddleware'],
                'invokable' => ['Jgut\\Slim\\Routing\\Tests\\Files\\Annotation\\Valid\\GroupedRoute', 'actionThree'],
            ],
            [
                'name' => 'one',
                'priority' => -10,
                'methods' => ['GET', 'POST'],
                'pattern' => '/one/{id}',
                'placeholders' => [
                    'id' => '[0-9]+',
                ],
                'middleware' => ['oneMiddleware'],
                'invokable' => ['Jgut\\Slim\\Routing\\Tests\\Files\\Annotation\\Valid\\SingleRoute', 'actionOne'],
            ],
        ];

        self::assertEquals($loaded, $routes);
    }
}
