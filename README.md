[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/slim-routing.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-routing)
[![License](https://img.shields.io/github/license/juliangut/slim-routing.svg?style=flat-square)](https://github.com/juliangut/slim-routing/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/slim-routing.svg?style=flat-square)](https://travis-ci.org/juliangut/slim-routing)
[![Style Check](https://styleci.io/repos/91021757/shield)](https://styleci.io/repos/91021757)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/slim-routing.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/slim-routing)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/slim-routing.svg?style=flat-square)](https://coveralls.io/github/juliangut/slim-routing)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-routing.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-routing/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-routing.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-routing/stats)

# slim-routing

A replacement for Slim's router that adds annotation and configuration based routing as well as expands the possibilities of your route callbacks by handling return types

Thanks to this library, instead of configuring routes by hand one by one and including them into Slim's router you can create mapping files that define and structure your routes and let them be included into the router.

If you're familiar with how Doctrine defines entities mappings you'll feel at home with slim-routing because much as how Doctrine does route mappings are defined either

* On class annotations (in controller classes)
* In routing definition files, currently supported in PHP, JSON, XML and YAML

> Routing gathering and compilation can be quite a heavy load process depending on how many classes/files and routes are defined, specially for annotations. For this reason it's advised to always use [Slim's router cache](https://www.slimframework.com/docs/objects/application.html#slim-default-settings) on production applications and invalidate cache on deployment

Route callbacks can now return `\Jgut\Slim\Routing\Response\ResponseTypeInterface` responses that will be later transformed into the mandatory `Psr\Message\ResponseInterface` in a way that lets you decouple view from controller 

## Installation

### Composer

```
composer require juliangut/slim-routing
```

symfony/yaml to parse yaml files

```
composer require symfony/yaml
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';
```

```php
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Response\PayloadResponseType;
use Jgut\Slim\Routing\Response\Handler\JsonResponseHandler;
use Jgut\Slim\Routing\Router;
use Slim\App;

$app = new App();

$container = $app->getContainer();

$container['router'] = function ($container) {
    $configuration = new Configuration([
        'sources' => ['/path/to/routing/files'],
        'responseHandlers' => [
            PayloadResponseType::class => new JsonResponseHandler(),
        ],
    ]);
    $router = new Router($configuration);
    $router->setContainer($container); // Be sure to set the container

    $routerCacheFile = false;
    if (isset($container->get('settings')['routerCacheFile'])) {
        $routerCacheFile = $container->get('settings')['routerCacheFile'];
    }
    $router->setCacheFile($routerCacheFile);

    return $router;
};

$app->get('/', function(ServerRequestInterface $request, ResponseInterface $response) {
    return (new PayloadResponseType())->setResponse($response)->setPayload(['param' => 'value']);
});

$app->run();
```

### Configuration

* `sources` must be an array containing arrays of configurations to create MappingDriver objects:
    * `type` one of \Jgut\Slim\Routing\Mapping\Driver\DriverFactory constants: `DRIVER_ANNOTATION`, `DRIVER_PHP`, `DRIVER_JSON`, `DRIVER_XML` or `DRIVER_YAML` **defaults to DRIVER_ANNOTATION if no driver**
    * `path` a string path or array of paths to where mapping files are located (files or directories) **REQUIRED if no driver**
    * `driver` an already created \Jgut\Slim\Routing\Mapping\Driver\DriverInterface object **REQUIRED if no type AND path**
* `placeholderAliases` array of additional placeholder aliases. There are some default aliases already available:
  * numeric => `\d+`
  * alpha => `[a-zA-Z]+`
  * alnum => `[a-zA-Z0-9]+`
  * any => `.+`
* `namingStrategy`, instance of \Jgut\Slim\Routing\Naming\NamingInterface (\Jgut\Slim\Routing\Naming\SnakeCase by default)
* `responseHandlers` array of \Jgut\Slim\Routing\Response\ResponseTypeInterface::class => \Jgut\Slim\Routing\Response\Handler\ResponseHandlerInterface or container entry

## Response handling

Ever thought why you should encode output or call template renderer in all your routes?

```
$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->view->render($response, 'profile.html', [
        'name' => $args['name']
    ]);
})->setName('profile');
```

Route callbacks normally respond with a `Psr\Message\ResponseInterface` object, but thanks to slim-routing they can now respond with a more intent expressive ResponseTypeInterface object that will be handled afterwards

Of course normal ResponseInterface responses from route callback will be treated as usual

### Response type

Response types are DTO objects with the needed data to later create a ResponseInterface object. This leaves the presentation logic out of router and allows for cleaner routes and easy presentation logic reuse

```
$app->get('/hello/{name}', function ($request, $response, $args) {
    return ViewResponseType()
        ->setResponse($response)
        ->setTemplate('profile.html')
        ->setParameters(['name' => $args['name']]);
})->setName('profile');
```

If route returns an instance of `\Jgut\Slim\Routing\Response\ResponseTypeInterface` it will be passed to the corresponding handler according to routing configuration

Provided response types:

* `PayloadResponseType` stores simple payload to be transformed for example to JSON
* `ViewResponseType` keeps agnostic template payload so it can be rendered in a handler

### Response type handler

Mapped on configuration's "responseHandlers" key, a response handler will be responsible of returning a `Psr\Message\ResponseInterface` from the received `\Jgut\Slim\Routing\Response\ResponseTypeInterface`

Typically they will agglutinate presentation logic: how to represent the data contained in the response type, such as transform it into json, XML, etc, or render it with a template engine such as Twig or Plates

Provided response types:

* `JsonResponseTypeHandler` receives a PayloadResponseType and returns a JSON response
* `XmlResponseTypeHandler` receives a PayloadResponseType and returns a XML response (requires [spatie/array-to-xml](https://github.com/spatie/array-to-xml))
* `TwigViewResponseTypeHandler` receives a generic ViewResponseType and returns a template rendered thanks to Twig and [Slim's Twig-View](https://github.com/slimphp/Twig-View)

### Routes

Routes can be defined in two basic ways: by setting them in definition files of various types or directly defined in annotations on controller classes

#### Annotations

##### Router (Class level)

Just a mark to identify classes defining routes. Its presence is mandatory on each routing class

```php
use Jgut\Slim\Routing\Mapping\Annotation as JSR

/**
 * @JSR\Router
 */
class Home
{
}
```

##### Group (Class level)

Defines a group in which routes may reside. It is not mandatory but useful when you want to do route grouping or apply middleware to several routes at the same time

```php
use Jgut\Slim\Routing\Mapping\Annotation as JSR

/**
 * @JSR\Router
 * @JSR\Group(
 *     prefix="routePrefix",
 *     parent="parentGroupClassName",
 *     pattern="section/{name}",
 *     placeholders={"name": "[a-z]+"},
 *     middleware={"groupMiddlewareName"}
 * )
 */
class Section
{
}
```

* `prefix`, optional, prefix to be prepended to route names
* `parent`, optional, references a parent class name
* `pattern`, optional, path pattern
* `placeholders`, optional, array of regex/alias for path placeholders, 
* `middleware`, optional, array of middleware to be added to all group routes

##### Route (Method level)

Defines the final routes added to the router

```php
use Jgut\Slim\Routing\Mapping\Annotation as JSR

/**
 * @JSR\Router
 */
class Section
{
    /**
     * @JSR\Route(
     *     name="routeNamme",
     *     methods={"GET", "POST"},
     *     pattern="do/{action}",
     *     placeholders={"action": "[a-z0-9]+"},
     *     middleware={"routeMiddlewareName"},
     *     priority=-10,
     * )
     */
    public function doSomething()
    {
    }
}
```

* `name`, optional, route name so it can be referenced in Slim
* `pattern`, optional, path pattern (defaults to '/')
* `methods`, optional, list of accepted HTTP route methods. "ANY" is a special method that transforms to `[GET, POST, PUT, PATCH, DELETE]`, if ANY is used no other method is allowed in the list (defaults to GET)
* `placeholders`, optional, array of regex/alias for path placeholders
* `middleware`, optional, array of middleware to be added to the route
* `priority`, optional, integer for ordering route registration. The order is global among all loaded routes. Negative routes get loaded first (defaults to 0)

#### Definition files

####### PHP

```php
return [
  [
    // Group
    'prefix' => 'prefix',
    'pattern' => 'group-pattern',
    'placeholders' => ['group-placeholders'],
    'middleware' => ['group-middleware'],
    'routes' => [
      [
        // Route
        'name' => 'routeName',
        'methods' => ['GET', 'POST'],
        'priority' => 0
        'pattern' => 'route-pattern',
        'placeholders' => ['route-placeholders'],
        'middleware' => ['route-middleware'],
        'invokable' => 'callable',
      ],
      [
        // Subgroup
        'pattern' => 'group-pattern',
        'placeholders' => ['group-placeholders'],
        'middleware' => ['group-middleware'],
        'routes' => [
          // Routes/groups ...
        ],
      ],
      // Routes/groups ...
    ],
  ],
  // Routes/groups ...
]
```

####### JSON

```json
[
  {
    // Group
    "prefix": "prefix",
    "pattern": "group-pattern",
    "placeholders": ["group-placeholders"],
    "middleware": ["group-middleware"],
    "routes": [
      {
        // Route
        "name": "routeName",
        "methods": ["GET", "POST"],
        "priority": 0,
        "pattern": "route-pattern",
        "placeholders": ["route-placeholders"],
        "middleware": ["route-middleware"],
        "invokable": "callable",
      },
      {
        // Subgroup
        "pattern": "group-pattern",
        "placeholders": ["group-placeholders"],
        "middleware": ["group-middleware"],
        "routes": [
          // Routes/groups ...
        ]
      }
      // Routes/groups ...
    ]
  }
  // Routes/groups ...
]
```

####### XML

```xml
<?xml version="1.0" encoding="utf-8"?>
<root>
    <group1 prefix="prefix" pattern="group-pattern">
        <placeholders>
            <placeholder1>group-placeholder</group-placeholder1>
        </placeholders>
        <middleware>
            <middleware1>group-middleware</middleware1>
        </middleware>
        <routes>
            <route1 name="routeName" priority="0" pattern="route-pattern">
                <methods>
                    <method1>GET</method1>
                    <method2>POST</method2>
                </methods>
                <placeholders>
                    <placeholder1>route-placeholder</placeholder1>
                </placeholders>
                <middleware>
                    <middleware1>route-middleware</middleware1>
                </middleware>
                <invokable>callable</invokable>,
            </route1>
            <subgroup1 prefix="prefix" pattern="group-pattern">
                <placeholders>
                    <placeholder1>group-placeholder</group-placeholder1>
                </placeholders>
                <middleware>
                    <middleware1>group-middleware</middleware1>
                </middleware>
                <routes>
                    <!-- Routes/groups... -->
                </routes>
            </subgroup1>
            <!-- Routes/groups... -->
        </routes>
    </group1>
    <!-- Routes/groups... -->
</root>
```

####### YAML

```yaml
# Group
- prefix: prefix
  pattern: group-pattern
  placeholders: [group-placeholders]
  middleware: [group-middleware]
  routes:
    # Route
    - name: routeName
      methods: [GET, POST]
      priority: 0
      pattern: route-pattern
      placeholders: [route-placeholders]
      middleware: [route-middleware]
      invokable: callable
    # Subgroup
    - pattern: group-pattern
      placeholders: [group-placeholders]
      middleware: [group-middleware]
      routes:
        # Routes/groups ...
    # Routes/groups ...
# Routes/groups ...
```

##### Group

Defines a group in which routes may reside.

* `routes`, array of routes and/or subgroups (this key identifies a group)
* `prefix`, optional, prefix to be prepended to route names
* `pattern`, optional, path pattern
* `placeholders`, optional, array of regex/alias for path placeholders, 
* `middleware`, optional, array of middleware to be added to all group routes

##### Route

Defines a route added to Slim

* `invokable`, callable to be invoked on route match. Can be a container entry, class name or an array of [class, method]
* `name`, optional, route name so it can be referenced in Slim
* `pattern`, optional, path pattern (defaults to '/')
* `methods`, optional, list of accepted HTTP route methods. "ANY" is a special method that transforms to `[GET, POST, PUT, PATCH, DELETE]`, if ANY is used no other method is allowed (defaults to GET)
* `placeholders`, optional, array of regex for path placeholders
* `middleware`, optional, array of middleware to be added to the route
* `priority`, optional, integer for ordering route registration. The order is global among all loaded routes. Negative routes get loaded first (defaults to 0)

### Route composition

#### Name

Final route name is composed of the concatenation of group prefixes followed by route name according to configured route naming strategy

#### Pattern

Resulting route pattern is composed of the concatenation of group patterns and finally route pattern

#### Placeholders

Resulting placeholders list is composed of all group placeholders if any and route placeholders

It is important to pay attention not to duplicate placeholder names in the resulting pattern as this can't be handled by FastRoute. Check group tree patterns for placeholder names

#### Middleware

Resulting middleware applied to a route will be the result of combining group middleware and route middleware

There is a drawback on defining middleware in any other format but PHP definition files. You cannot use a Closure, only strings, so that middleware must be a reference to a container entry

## Considerations

Important to note is the order in which the middleware is assigned to each route:

* Firstly route middleware will be applied in the order they are defined
* Then group (if any) middleware are to be applied in the same order they are defined
* If route group (if any) has a parent then parent's middleware are applied in the order they are defined, and this goes up until no group parent is defined

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-routing/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-routing/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-routing/blob/master/LICENSE) included with the source code for a copy of the license terms.
