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

Annotation and configuration based Slim framework routing

Routes are defined either by class annotations (in controllers) or in routing definition files (currently php and yaml available) and automatically inserted into Slim's router

## Installation

### Composer

```
composer require juliangut/slim-routing
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';
```

```php
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Manager;
use Slim\App;

$app = new App();

$configuration = new Configuration([
    'sources' => ['/path/to/routing/files'],
]);
$manager = new Manager($configuration);
$manager->registerRoutes($app->getContainer());

$app->run();
```

### Configuration

* `sources`, array of directories (annotations) or files (annotations, php, json or yml) to extract routing from

> Routing load and compilation can be a heavy load process depending on how many classes and routes are defined. For this reason it's advised to use Slim's router caching on production applications and invalidate cache on deployment

### Annotations

#### Router (Class)

Each class that defines routes need this annotation

```php
use Jgut\Slim\Routing\Annotation as JSR

/**
 * @JSR\Router
 */
class Home
{
}
```

#### Group (Class)

Defines a group in which routes may reside. It is not mandatory unless you want to do route grouping or apply middleware to several routes at the same time

```php
use Jgut\Slim\Routing\Annotation as JSR

/**
 * @JSR\Router
 * @JSR\Group(
 *     name="groupName",
 *     group="parentGroupName",
 *     pattern="/section/{name}",
 *     placeholders={"name": "[a-z]+"},
 *     middleware={"groupMiddlewareName"}
 * )
 */
class Section
{
}
```

* `name`, optional, group name so it can be referenced by another route in order to create a group tree
* `group`, optional, references a parent group
* `pattern`, optional, group path pattern
* `placeholders`, optional, array of regex for path placeholders, 
* `middleware`, optional, array of middleware to be added to all group routes

#### Route (Method)

Defines the final routes added to Slim

```php
use Jgut\Slim\Routing\Annotation as JSR

/**
 * @JSR\Router
 */
class Section
{
    /**
     * @JSR\Route(
     *     name="routeNamme",
     *     methods={"GET", "POST"},
     *     pattern="/do/{action}",
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
* `methods`, optional, list of accepted HTTP route methods. "ANY" is a special method that transforms to [GET, POST, PUT, PATCH, DELETE], if ANY is used no other method is allowed (defaults to GET)
* `pattern`, route path pattern
* `placeholders`, optional, array of regex for path placeholders
* `middleware`, optional, array of middleware to be added to the route
* `priority`, optional, integer for ordering route registration. The order is global among all loaded routes. Negative routes get loaded first (defaults to 0)

### Definition files

###### PHP

```php
return [
  [
    // Group
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
      ],
      [
        // Subgroup
        'pattern' => 'group-pattern',
        'placeholders' => ['group-placeholders'],
        'middleware' => ['group-middleware'],
        'routes' => [
          // Routes/groups
          // ...
        ],
      ],
      // Routes/groups
      // ...
    ],
  ],
  // Routes/groups
  // ...
]
```

###### JSON

```json
[
  {
    // Group
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
        "middleware": ["route-middleware"]
      },
      {
        // Subgroup
        "pattern": "group-pattern",
        "placeholders": ["group-placeholders"],
        "middleware": ["group-middleware"],
        "routes": [
          // Routes/groups
          // ...
        ]
      }
      // Routes/groups
      // ...
    ]
  }
  // Routes/groups
  // ...
]
```

###### YAML

Require symfony/yaml to parse yaml files

```
composer require symfony/yaml
```

```yaml
// Group
- pattern: group-pattern
  placeholders: [group-placeholders]
  middleware: [group-middleware]
  routes:
    // Route
    - name: routeName
      methods: [GET, POST]
      priority: 0
      pattern: route-pattern
      placeholders: [route-placeholders]
      middleware: [route-middleware]
    // Subgroup
    - pattern: group-pattern
      placeholders: [group-placeholders]
      middleware: [group-middleware]
      routes:
        // Routes/groups
        // ...
    // Routes/groups
    // ...
// Routes/groups
// ...
```

#### Group

Defines a group in which routes may reside.

* `pattern`, optional, group path pattern
* `placeholders`, optional, array of regex for path placeholders, 
* `middleware`, optional, array of middleware to be added to all group routes

#### Route

Defines the final route added to Slim

* `name`, optional, route name so it can be referenced in Slim
* `methods`, optional, list of accepted HTTP route methods. "ANY" is a special method that transforms to [GET, POST, PUT, PATCH, DELETE], if ANY is used no other method is allowed (defaults to GET)
* `pattern`, route path pattern
* `placeholders`, optional, array of regex for path placeholders
* `middleware`, optional, array of middleware to be added to the route
* `priority`, optional, integer for ordering route registration. The order is global among all loaded routes. Negative routes get loaded first (defaults to 0)

## Route composition

### Pattern

Resulting route pattern is composed of the concatenation of group pattern (referenced by the "group" parameter on annotations) and finally route pattern

It is important to pay attention not to duplicate placeholder names in the resulting pattern as this can't be handled by FastRoute. Check group tree patterns for placeholder names

### Placeholders

Resulting placeholders regex list is composed of all group placeholders (referenced by the "group" parameter on annotations) and finally route placeholders

### Middleware

Resulting middleware applied to a route will be the result of combining group middleware (referenced by the "group" parameter on annotations) and finally route middleware

The only drawback of using middleware with annotations is that every middleware must be registered in the container as they are just a list of strings (services pulled from the container)

## Considerations

Important to note is the order in which the middleware is assigned to each route:

* Firstly route middleware will be applied in the order they are defined
* Then group (if any) middleware are to be applied in the order they are defined
* Finally if there are more groups above (reference by the "group" parameter on annotations) they are applied in the order they are defined, this continues up the group tree until there are no more groups

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/slim-routing/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/slim-routing/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/slim-routing/blob/master/LICENSE) included with the source code for a copy of the license terms.
