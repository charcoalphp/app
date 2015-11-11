Charcoal App
============

`Charcoal\App` is a framework to create _Charcoal_ applications with **Slim**. It is actually a small layer on top of Slim to load the proper routes / controllers and middlewares from a configuration file.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-app.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-app)

# How to install
The preferred (and only supported) way of installing _charcoal-app_ is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-app
```

## Dependencies
- `PHP 5.5+`
  - Older versions of PHP are deprecated, therefore not supported.
- [`locomotivemtl/charcoal-view`](https://github.com/locomotivemtl/charcoal-view)
  - Template controllers will typically load a View object and render a template. 
  - This brings a dependency on [`mustache/mustache`](https://github.com/bobthecow/mustache.php).
- [`slim/slim`](https://github.com/slimphp/Slim)
  - The main app, container and router are provided by Slim.
  - Its dependencies are:
    -  `pimple/pimple`
    -  `psr/http-message`
    -  `nikic/fast-route`

> 👉 Development dependencies are described in the [Development](#development) section of this README file.

# Components
The main components of charcoal-app are _App_, _Module_, _Route_, _RequestController_, _Middleware_ and _Ui_.

## App
- The *App* loads the root onfiguration.
  - **App**: _implements_ `\Charcoal\App\App`
  - **Config**: `\Charcoal\App\AppConfig`
    - The `AppConfig` expects a key called `modules`
      - Each modules have an ident and a sub-configuration (`ModuleConfig`)
  - **Container**: Dependencies are expected to be in a `Pimple` container
  
- The *App* has one method: `setup()` wich:
  - Accepts a `\Slim\App` as a parameter.
  - Instanciate a `ModuleManager` which:
    - Loop all `modules` from the `AppConfiguration` and create new *Modules* according to the configuration.
    - (The Module creation is done statically via it's `setup()` abstract method)

> The `App` is entirely optional. Modules could be loaded without going through a `ModuleManager`.

## Module

- A *Module* loads its configuration from the root config
  - **Module**: _implements_ `Charcoal\App\ModuleInterface` 
  - **Config**: `\Charcoal\App\ModuleConfig`
    - The `ModuleConfig 

- A *Module* requires:
  - A parent **Container**
  - A `\Slim\App`

- A *Module* defines:
  - **Routes**: which defines a path to load and a `RequestController` configuration.
  - **Middlewares**: which are TBD.

## Routes and RequestController
All routes are actually handled by the *Slim* app. Charcoal Routes are just *definition* of a route:
- An identifier, which typically matches the controller.
- A RouteConfig structure, which contains:
  - The `type` of  `RequestController`
    - This can be `TemplateController` or `ActionController`
  - The `controller` ident

### Route API


> 👉 Slim's routing is actually provided by [FastRoute](https://github.com/nikic/FastRoute)

Example of routes configuration:
```json
{
  
}
```

To manage the module's route.

There are 3 types of `RequestController`:
- `ActionController`: typically executes an action (return JSON) from a _POST_ request.
- `ScriptController`: typically ran from the CLI interface.
- `TemplateController`: typically  load a template from a _GET_ request.
- 
## Middleware
Middleware is not yet implemented in `Charcoal\App`. The plan is to use the PSR7-middleware system, which is a callable with the signature:
```
use \Psr\Http\Message\RequestInterface as RequestInterface;
use \Psr\Http\Message\ResponseInterfac as ResponseInterface;

middleware(RequestInterface $request, ResponseInterface $response) : ResponseInterface
```

## Summary
- An _App_ is a collection of _Modules_, which are a collection of _Routes_ and _Middlewares_.
- _Routes_ are just definitions that match a path to a _RequestController_
  - There are 2 types of _RequestController_: _Templates_ and _Actions_

## Configuration examples

Example of a module configuration:
```json
{
    "routes":{
        "templates":{
            "foo/bar":{},
            "foo/baz/{:id}":{
                "controller":"foo/baz",
                "methods":["GET", "POST"]
            }
        },
        "actions":{
            "foo/bar":{}
        }
    },
    "middlewares":{
    
    }
}
```

# Usage
Typical Front-Controller:
```php
include '../vendor/autoload.php';

$container = new \Slim\Container();

$container['config'] = function() {
    $config = new \Charcoal\App\AppConfig();
    $config->add_file('../config/config.php');
    return $config;
};

$slim = new \Slim\App($container);

$app = new \Charcoal\App($slim);
$app->setup();

$slim->run();
```

Without an App / ModuleManager:
```php
// ...
$slim = new \Slim\App($container);

\Charcoal\Admin\Module::setup($slim);
\Charcoal\Messaging\Module::setup($slim);
\Foobar\Module::setup($slim);

$slim->run();
```
This achieves the same result, excepts the *Modules* were not loaded from the root configuration but hard-coded.

Without Module to handle routes and middlewares:
```php
// ...
$slim = new \Slim\App($container);

$container['controller_loader'] = function($c) {
    
};

// Add middleware manually
// $slim->add('\Foobar\Middleware\Foo');

$slim->get('/', function($request, $response, $args) {
    $container = $this->getContainer();
    $request_controller = $container['controller_loader']->get('/');
    return $request_controller($request, $response, $args);
});

$slim->post('/', function() {
    
});
```

## Classes
- `\Charcoal\App\AbstractModule`
- `\Charcoal\App\App`
- `\Charcoal\App\AppConfig`
- `\Charcoal\App\GenericModule`
- `\Charcoal\App\ModuleInterface`
- `\Charcoal\App\ModuleManager`
- `\Charcoal\App\RequestController`
- `\Charcoal\App\RouteConfig`
- `\Charcoal\App\RouteManager`

# Development

To install the development environment:
```shell
$ npm install
$ composer install
```

## Development dependencies
- `npm`
- `grunt` (install with `npm install grunt-cli`)
- `composer`
- `phpunit`

## Coding Style

The Charcoal-App module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
  - Method names MUST be declared in `snake_case`.
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), except for the PSR-1 requirement.
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
  - Add DocBlocks for all classes, methods, and functions;
  - For type-hinting, use `boolean` (instead of `bool`), `integer` (instead of `int`), `float` (instead of `double` or `real`);
  - Omit the `@return` tag if the method does not return anything.
- Naming conventions
  - Read the [phpcs.xml](phpcs.xml) file for all the details.

> Coding style validation / enforcement can be performed with `grunt phpcs`. An auto-fixer is also available with `grunt phpcbf`.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.1
_Unreleased_
- Initial release
