[![PHP version](https://img.shields.io/badge/PHP-%3E%3D8.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/slim-routing.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-routing)
[![License](https://img.shields.io/github/license/juliangut/slim-routing.svg?style=flat-square)](https://github.com/juliangut/slim-routing/blob/master/LICENSE)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/slim-routing.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-routing/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/slim-routing.svg?style=flat-square)](https://packagist.org/packages/juliangut/slim-routing/stats)

# slim-routing

A replacement for Slim's router that adds Attribute and configuration based routing as well as expands the possibilities of your route callbacks by handling different response types

Thanks to this library, instead of configuring routes by hand one by one and including them into Slim's routing you can create mapping files that define and structure your routes and let them be included automatically instead

Additionally, if you're familiar with Symfony's definition of routes through Attributes you'll feel at home with slim-routing because route mappings can be defined the same way as well

* On class Attributes (i.e. controller classes)
* In routing definition files, currently supported in PHP, JSON, XML and YAML

> Route gathering and compilation can be quite a heavy process depending on how many classes/files and routes are defined, specially in the case of attributes. For this reason it's strongly advised to always use route collector's cache and Slim's [route expression caching](https://www.slimframework.com/docs/v4/objects/routing.html#route-expressions-caching) on production applications and invalidate cache on deployment

Thanks to slim-routing route callbacks can now return `\Jgut\Slim\Routing\Response\ResponseType` objects that will be ultimately transformed into the mandatory `Psr\Message\ResponseInterface` in a way that lets you fully decouple view from the route

## Installation

### Composer

```
composer require juliangut/slim-routing
```

symfony/yaml to parse yaml routing files

```
composer require symfony/yaml
```

spatie/array-to-xml to return XML responses

```
composer require spatie/array-to-xml
```

slim/twig-view to return Twig rendered responses

```
composer require slim/twig-view
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';
```

```php
use Jgut\Slim\Routing\AppFactory;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Response\ResponseType;
use Jgut\Slim\Routing\Response\Handler\JsonResponseHandler;
use Jgut\Slim\Routing\Strategy\RequestHandler;
use Psr\Http\Message\ServerRequestInterface;

$configuration = new Configuration([
    'sources' => ['/path/to/routing/files'],
]);
AppFactory::setRouteCollectorConfiguration($configuration);

// Instantiate the app
$app = AppFactory::create();


// Register custom invocation strategy to handle ResponseType objects
$invocationStrategy = new RequestHandler(
    [
        PayloadResponse::class => JsonResponseHandler::class,
    ],
    $app->getResponseFactory(),
    $app->getContainer()
);
$routeCollector = $app->getRouteCollector();
$routeCollector->setDefaultInvocationStrategy($invocationStrategy);

$cache = new PSR16Cache();
$routeCollector->setCache($cache);

// Recommended if you want to add more routes manually
$routeCollector->registerRoutes();

// Additional routes if needed
$app->get('/', function(ServerRequestInterface $request): ResponseType {
    return new PayloadResponse(['param' => 'value'], $request);
});

$app->run();
```

### Configuration

* `sources` must be an array containing arrays of configurations to create MappingDriver objects:
    * `type` one of \Jgut\Slim\Routing\Mapping\Driver\DriverFactory constants: `DRIVER_ATTRIBUTE`, `DRIVER_PHP`, `DRIVER_JSON`, `DRIVER_XML`, `DRIVER_YAML` or `DRIVER_ANNOTATION` **if no driver, defaults to DRIVER_ATTRIBUTE**
    * `path` a string path or array of paths to where mapping files are located (files or directories) **REQUIRED if no driver**
    * `driver` an already created \Jgut\Slim\Routing\Mapping\Driver\DriverInterface object **REQUIRED if no type AND path**
* `trailingSlash` boolean, indicates whether to append a trailing slash on route pattern (true) or remove it completely (false), by default. False by default
* `placeholderAliases` array of additional placeholder aliases. There are some default aliases already available:
  * numeric => `\d+`
  * alpha => `[a-zA-Z]+`
  * alnum => `[a-zA-Z0-9]+`
  * slug -> `[a-zA-Z0-9-]+`
  * uuid -> `[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}`
  * mongoid -> `[0-9a-f]{24}`
  * any => `[^}]+`
* `namingStrategy`, instance of \Jgut\Slim\Routing\Naming\Strategy (\Jgut\Slim\Routing\Naming\SnakeCase by default)

In the case of Attributes mapping all classes in source paths will be traversed in search of routing definitions

## Response handling

Ever wondered why you should encode output or call template renderer in every single route? or even why respond with a ResponseInterface object in the end?

```php
$app->get('/hello/{name}', function (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    return $this->view->render(
        $response,
        'greet.html',
        [
            'name' => $args['name']
        ]
    );
})->setName('greet');
```

Route callbacks normally respond with a `Psr\Message\ResponseInterface` object, but thanks to slim-routing they can now respond with a string, null or even better with a more intent expressive ResponseType object that will be handled afterward

```php
$app->get(
    '/hello/{name}', 
    fn ($args): string => 'Hello ' . $args['name'],
)->setName('greet');
```

Of course normal ResponseInterface responses from route callback will be treated as usual

### ResponseType aware invocation strategies

For the new response handling to work you need to register a new invocation strategy provided by this library, there are four strategies provided out of the box that plainly mimic the ones provided by Slim

* `Jgut\Slim\Routing\Strategy\RequestHandler`
* `Jgut\Slim\Routing\Strategy\RequestResponse`
* `Jgut\Slim\Routing\Strategy\RequestResponseArgs`
* `Jgut\Slim\Routing\Strategy\RequestResponseNamedArgs`

### Response type

Response types are Value Objects with the needed data to later produce a ResponseInterface object. This leaves the presentation logic out of routes allowing for cleaner routes and easy presentation logic reuse

```php
use Jgut\Slim\Routing\Response\ResponseType;
use Jgut\Slim\Routing\Response\ViewResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app->get(
    '/hello/{name}', 
    fn (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseType 
        => new ViewResponse('greet.html', ['name' => $args['name']], $request, $response),
)->setName('greet');
```

If a route returns an instance of `\Jgut\Slim\Routing\Response\ResponseType` it will be passed to the corresponding handler according to configuration

There are two response types already provided:

* `PayloadResponse` stores simple payload data to be later transformed for example into JSON or XML
* `ViewResponse` keeps agnostic template parameters, so they can be rendered in a handler

You can easily create your own.

### Response type handler

Mapped on invocation strategy, a response handler will be responsible for returning a `Psr\Message\ResponseInterface` from the received `\Jgut\Slim\Routing\Response\ResponseType`

Typically, they will agglutinate presentation logic: how to represent the data contained in the response type, such as transform it into JSON, XML, etc. or render it with a template engine such as Twig

Register response type handlers on invocation strategy creation or

```php
use Jgut\Slim\Routing\Response\PayloadResponse;
use Jgut\Slim\Routing\Response\Handler\JsonResponseHandler;

$invocationStrategy->setResponseHandler(PayloadResponse::class, JsonResponseHandler::class);
```

Provided response types handlers:

* `JsonResponseHandler` receives a PayloadResponse and returns a JSON response
* `XmlResponseHandler` receives a PayloadResponse and returns a XML response (requires [spatie/array-to-xml](https://github.com/spatie/array-to-xml))
* `TwigViewResponseHandler` receives a generic ViewResponse and returns a template rendered thanks to [slim/twig-view](https://github.com/slimphp/Twig-View)

You can create your own response type handlers to compose specifically formatted response (JSON:API, ...) or use another template engines (Plates, ...)

### Parameter transformation

Route parameters can be transformed before arriving to route callable. The most common use of this feature would be to transform IDs into actual object/entity used in the callable

To achieve this you need to provide a `\Jgut\Slim\Routing\Transformer\ParameterTransformer` instance

For example, you would want to transform parameters into Doctrine entities

```php
use Jgut\Slim\Routing\Transformer\ParameterTransformer;
use Slim\Exception\HttpNotFoundException;

final class UserEntityTransformer implements ParameterTransformer
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    protected function supports(string $parameter, string $type) : bool
    {
        return $parameter === 'user' && $type === UserEntity::class;
    }

    protected function transform(string $parameter, string $type, mixed $value): mixed
    {
        $user = $this->entityManager->getRepository($type)->find($value);
        if ($user === null) {
            throw new HttpNotFoundException('User not found');
        }

        return $user;
    }
}
```

_Mind that a single transformer can transform one or several parameters_

### Route mapping

Routes can be defined in two basic ways: by writing them down in definition files of various formats or directly defined in attributes on controller classes

#### Attributes

##### Group (Class level)

Defines a group in which routes may reside. It is not mandatory but useful when you want to do route grouping or apply middleware to several routes at the same time

```php
use Jgut\Slim\Routing\Mapping\Attribute\Group;

#[Group(
    prefix: 'routePrefix',
    parent: Area::class,
    pattern: 'section/{section}',
    placeholders: ['section': 'alpha'],
    arguments: ['scope' => 'public'],
 )]
class Section {}
```

* `prefix`, optional, prefix to be prepended to route names
* `parent`, optional, references a parent group name
* `pattern`, optional, path pattern, prepended to route patterns. Do not use placeholders in the pattern
* `placeholders`, optional, array of regex/alias for pattern placeholders,
* `arguments`, optional, array of arguments to attach to final route 

##### Route (Method level)

Defines the final routes added to Slim

```php
use Jgut\Slim\Routing\Mapping\Attribute\Route;

class Section
{
    #[Route(
        name: 'routeName',
        methods: ['GET', 'POST'],
        pattern: 'user/{user}',
        placeholders: ['user': 'alnum'],
        arguments: ['scope': 'admin.read']
        xmlHttpRequest: true,
        priority: -10,
    )]
    public function userAction() {}
}
```

* `name`, optional, route name so it can be referenced in Slim
* `methods`, optional, list of accepted HTTP route methods. ºANY" is a special method that transforms to `[GET, POST, PUT, PATCH, DELETE]`, if ANY is used no other method is allowed in the list (defaults to GET)
* `pattern`, optional, path pattern (defaults to '/'). Do not use placeholders in the pattern
* `placeholders`, optional, array of regex/alias for pattern placeholders
* `arguments`, optional, array of arguments to attach to the route
* `xmlHttpRequest`, request should be AJAX, false by default
* `priority`, optional, integer for ordering route registration. The order is global among all loaded routes. Negative routes get loaded first (defaults to 0)

##### Middleware (Class and Method level)

Defines middleware to apply. Several can be added

```php
use Jgut\Slim\Routing\Mapping\Attribute\Group;
use Jgut\Slim\Routing\Mapping\Attribute\Middleware;
use Jgut\Slim\Routing\Mapping\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

#[Group()]
#[Middleware(SectionMiddleware::class)]
class Section
{
    #[Route(methods: ['GET'], pattern: 'user')]
    #[Middleware(UserMiddleware::class)]
    public function userAction(): ResponseInterface {}
}
```

* `middleware`, MiddlewareInterface class that will be extracted from the container

##### Transformer (Class and Method level)

Defines route transformers. Several can be added

```php
use Jgut\Slim\Routing\Mapping\Attribute\Group;
use Jgut\Slim\Routing\Mapping\Attribute\Route;
use Jgut\Slim\Routing\Mapping\Attribute\Transformer;
use Psr\Http\Message\ResponseInterface;

#[Group()]
#[Transformer(transformer: SectionEntityTransfomer::class)]
class Section
{
    #[Route(methods: ['GET'], pattern: 'user/{user}')]
    #[Transformer(
        transformer: UserEntityTransfomer::class),
        parameters: ['user': User::class],
    )]
    public function userAction($request, $response, $user): ResponseInterface {}
}
```

* `transformer`, ParameterTransformer class that will be extracted from the container
* `parameters`, optional, array of definitions of parameters
* 
#### Definition files

###### PHP

```php
return [
  [
    // Group
    'prefix' => 'prefix',
    'pattern' => 'group-pattern',
    'placeholders' => [
        'group-placeholder' => 'type',
    ],
    'arguments' => [
        'group-argument' => 'value',
    ],
    'parameters' => [
        'group-parameters' => 'type',
    ],
    'transformers' => ['group-transformer'],
    'middlewares' => ['group-middleware'],
    'routes' => [
      [
        // Route
        'name' => 'routeName',
        'xmlHttpRequest' => true,
        'methods' => ['GET', 'POST'],
        'priority' => 0,
        'pattern' => 'route-pattern',
        'placeholders' => [
            'route-placeholder' => 'type',
        ],
        'parameters' => [
            'route-parameters' => 'type',
        ],
        'transformers' => ['route-transformer'],
        'arguments' => [
            'route-argument' => 'value',
        ],
        'middlewares' => ['route-middleware'],
        'invokable' => 'callable',
      ],
      [
        // Subgroup
        'pattern' => 'subgroup-pattern',
        'placeholders' => [
            'subgroup-placeholder' => 'type',
        ],
        'arguments' => [
            'subgroup-argument' => 'value',
        ],
        'middlewares' => ['subgroup-middleware'],
        'routes' => [
          // Routes/groups ...
        ],
      ],
      // Routes/groups ...
    ],
  ],
  // Routes/groups ...
];
```

###### XML

```xml
<?xml version="1.0" encoding="utf-8"?>
<root>
    <group1 prefix="prefix" pattern="group-pattern">
        <placeholders>
            <group-placeholder1>type</group-placeholder1>
        </placeholders>
        <arguments>
            <group-argument1>value</group-argument1>
        </arguments>
        <parameters>
          <group-parameter1>type</group-parameter1>
        </parameters>
        <transformers>
          <transformer1>group-transformer</transformer1>
        </transformers>
        <middlewares>
            <middleware1>group-middleware</middleware1>
        </middlewares>
        <routes>
            <route1 name="routeName" priority="0" pattern="route-pattern">
                <xmlHttpRequest>true</xmlHttpRequest>
                <methods>
                    <method1>GET</method1>
                    <method2>POST</method2>
                </methods>
                <placeholders>
                    <route-placeholder1>type</route-placeholder1>
                </placeholders>
                <parameters>
                    <route-parameter1>type</route-parameter1>
                </parameters>
                <transformers>
                     <transformer1>route-ransformer</transformer1>
                </transformers>
                <arguments>
                    <route-argument1>value</route-argument1>
                </arguments>
                <middlewares>
                    <middleware1>route-middleware</middleware1>
                </middlewares>
                <invokable>callable</invokable>
            </route1>
            <subgroup1 prefix="prefix" pattern="subgroup-pattern">
                <placeholders>
                    <subgroup-placeholder1>type</subgroup-placeholder1>
                </placeholders>
                <argument>
                    <subgroup-argument1>value</subgroup-argument1>
                </argument>
                <middlewares>
                    <middleware1>subgroup-middleware</middleware1>
                </middlewares>
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

###### JSON

_Mind comments are not valid standard JSON_

```json
[
  {
    // Group
    "prefix": "prefix",
    "pattern": "group-pattern",
    "placeholders": [{
      "group-placeholder": "type"
    }],
    "arguments": [{
      "group-argument": "value"
    }],
    "parameters": [{
      "group-parameter": "type"
    }],
    "transformers": ["group-transformer"],
    "middlewares": ["group-middleware"],
    "routes": [
      {
        // Route
        "name": "routeName",
        "xmlHttpRequest": true,
        "methods": ["GET", "POST"],
        "priority": 0,
        "pattern": "route-pattern",
        "placeholders": [{
          "route-placeholder": "type"
        }],
        "parameters": [{
          "route-parameter": "type"
        }],
        "transformers": ["route-transformer"],
        "arguments": [{
          "route-argument": "value"
        }],
        "middlewares": ["route-middleware"],
        "invokable": "callable"
      },
      {
        // Subgroup
        "pattern": "subgroup-pattern",
        "placeholders": [{
          "subgroup-placeholder": "type"
        }],
        "arguments": [{
          "subgroup-argument": "value"
        }],
        "middlewares": ["subgroup-middleware"],
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

###### YAML

```yaml
# Group
- prefix: prefix
  pattern: group-pattern
  placeholders: 
    - group-placeholder: type
  arguments: 
    - group-argument: value
  parameters:
    - group-parameter: type
  transformers: [group-ransformer]
  middlewares: [group-middleware]
  routes:
    # Route
    - name: routeName
      xmlHttpRequest: true
      methods: [GET, POST]
      priority: 0
      pattern: route-pattern
      placeholders:
        - route-placeholder: type
      parameters:
        - route-parameter: type
      transformers: [route-ransformer]
      arguments:
        - route-argument: value
      middlewares: [route-middleware]
      invokable: callable
    # Subgroup
    - pattern: subgroup-pattern
      placeholders: 
        - subgroup-placeholder: type
      arguments: 
        - subgroup-argument: value
      middlewares: [subgroup-middleware]
      routes:
        # Routes/groups ...
    # Routes/groups ...
# Routes/groups ...
```

##### Group

Defines a group in which routes may reside

* `routes`, array of routes and/or subgroups (this key identifies a group)
* `prefix`, optional, prefix to be prepended to route names
* `pattern`, optional, path pattern, prepended to route patterns. Do not use placeholders in the pattern
* `placeholders`, optional, array of regex/alias for pattern placeholders,
* `parameters`, optional, array of definitions of parameters, to be used in transformer
* `transformers`, optional, array of ParameterTransformer class that will be extracted from the container
* `arguments`, optional, array of arguments to attach to final route
* `middlewares`, optional, array of MiddlewareInterface class to be added to all group routes

##### Route

Defines a route added to Slim

* `invokable`, callable to be invoked on route match. Can be a container entry, class name or an array of [class, method]
* `name`, optional, route name so it can be referenced in Slim
* `pattern`, optional, path pattern (defaults to '/'). Do not use placeholders in the pattern
* `placeholders`, optional, array of regex/alias for pattern placeholders
* `xmlHttpRequest`, request should be AJAX, false by default
* `methods`, optional, list of accepted HTTP route methods. "ANY" is a special method that transforms to `[GET, POST, PUT, PATCH, DELETE]`, if ANY is used no other method is allowed (defaults to GET)
* `parameters`, optional, array of definitions of parameters, to be used in transformer
* `transformers`, optional, array of ParameterTransformer class that will be extracted from the container
* `arguments`, optional, array of arguments to attach to the route
* `middlewares`, optional, array of MiddlewareInterface class to be added to the route
* `priority`, optional, integer for ordering route registration. The order is global among all loaded routes. Negative routes get loaded first (defaults to 0)

#### Annotations

_Annotations are deprecated and will be removed eventually. Use Attribute mapping when possible_

You need to require Doctrine's annotation package

```
composer require doctrine/annotations
```

##### Group (Class level)

Defines a group in which routes may reside. It is not mandatory but useful when you want to do route grouping or apply middleware to several routes at the same time

```php
use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * @JSR\Group(
 *     prefix="routePrefix",
 *     parent=Area::class,
 *     pattern="section/{section}",
 *     placeholders={"section": "[a-z]+"},
 *     arguments={"scope": "public"}
 *     parameters={"section": "\Namespace\To\Section"},
 *     transformers={"\Namespace\To\GroupTransformer"}
 *     middleware={"\Namespace\To\GroupMiddleware"}
 * )
 */
class Section {}
```

* `prefix`, optional, prefix to be prepended to route names
* `parent`, optional, references a parent group name
* `pattern`, optional, path pattern, prepended to route patterns
* `placeholders`, optional, array of regex/alias for pattern placeholders,
* `parameters`, optional, array of definitions of parameters, to be used in route transformer
* `transformers`, optional, array of ParameterTransformer class that will be extracted from the container
* `arguments`, optional, array of arguments to attach to final route
* `middlewares`, optional, array of MiddlewareInterface class to be added to all group routes

##### Route (Method level)

Defines the final routes added to Slim

```php
use Jgut\Slim\Routing\Mapping\Annotation as JSR;

class Section
{
    /**
     * @JSR\Route(
     *     name="routeName",
     *     xmlHttpRequest=true,
     *     methods={"GET", "POST"},
     *     pattern="user/{user}",
     *     placeholders={"user": "[a-z0-9]+"},
     *     arguments={"scope": "admin.read"}
     *     parameters={"user": "\Namespace\To\User"},
     *     transformers={"\Namespace\To\RouteTransformer"},
     *     middleware={"Namespace\To\RouteMiddleware"},
     *     priority=-10
     * )
     */
    public function userAction() {}
}
```

* `name`, optional, route name so it can be referenced in Slim
* `pattern`, optional, path pattern (defaults to '/')
* `placeholders`, optional, array of regex/alias for pattern placeholders
* `xmlHttpRequest`, request should be AJAX, false by default
* `methods`, optional, list of accepted HTTP route methods. "ANY" is a special method that transforms to `[GET, POST, PUT, PATCH, DELETE]`, if ANY is used no other method is allowed in the list (defaults to GET)
* `parameters`, optional, array of definitions of parameters, to be used in transformer
* `transformers`, optional, array of ParameterTransformer class that will be extracted from the container
* `arguments`, optional, array of arguments to attach to the route
* `middlewares`, optional, array of MiddlewareInterface class to be added to the route
* `priority`, optional, integer for ordering route registration. The order is global among all loaded routes. Negative routes get loaded first (defaults to 0)

### Route composition

Using grouping with juliangut/slim-routing is a little different to how default Slim's router works

Groups are never really added to the router (in the sense you can add them in Slim with `$app->group(...)`) but routes are a composition of definitions that makes the final route

#### Name

Final route name is composed of the concatenation of group prefixes followed by route name according to configured route _naming strategy_

#### Pattern

Resulting route pattern is composed of the concatenation of group patterns if any and finally route pattern

#### Placeholders

Resulting placeholders list is composed of all group placeholders if any and route placeholders

It is important to pay attention not to duplicate placeholder names in the resulting pattern as this can't be handled by FastRoute. Check group tree patterns for placeholder names

#### Arguments

Resulting route arguments is composed of all group arguments if any and route arguments

#### Middlewares

Resulting middlewares added to a route will be the result of combining group middleware and route middleware and are applied to the route in the following order, so that final middleware execution order will be the same as expected in any Slim app:

* Firstly route middleware will be set to the route **in the order they are defined**
* Then group middleware (if any) are to be set into the route **in the same order they are defined**
* If group has a parent then parent's middleware are set **in the order they are defined**, and this goes up until no parent group is left

#### Transformers and parameters

Resulting transformers list is a combination of all group transformers and parameters if any and route transformers and parameters

As with placeholders, it is important to pay attention not to duplicate parameter names as child groups and routes will replace previous parameters

## Migration from 2.x

* Minimum PHP version is now 8.0
* Minimum Slim version is now 4.7
* PHP8 Attributes have been introduced for routing
* Annotations have been deprecated and its use is highly discouraged
* @Router Annotation use is not needed
* Middleware parameter of @Group and @Route Annotations have changed to "middlewares"
* ParameterTransformer methods and signatures have changed
* AbstractTransformer has been removed, simply implement ParameterTransformer
* Routing transformers now accept an array instead of a single reference
* Groups now support transformers
* Some internal methods have changed their signatures

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-routing/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-routing/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-routing/blob/master/LICENSE) included with the source code for a copy of the license terms.
