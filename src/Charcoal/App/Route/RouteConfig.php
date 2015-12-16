<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// From `charcoal-config`
use \Charcoal\Config\AbstractConfig;

// From `charcoal-core`
use \Charcoal\Translation\LanguageAwareInterface;
use \Charcoal\Translation\LanguageAwareTrait;

/**
 * Base "Route" configuration.
 */
class RouteConfig extends AbstractConfig implements LanguageAwareInterface
{
    use LanguageAwareTrait;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * The HTTP methods to wthich this route resolve to.
     * Ex: ['GET', 'POST', 'PUT', 'DELETE']
     * @var string[] $methods
     */
    private $methods = ['GET'];

    /**
     * The identifier (type) of the controller class.
     * @var string $controller
     */
    private $controller;

    /**
     * @var string $group
     */
    private $group;

    /**
     * @param string $ident Route identifier.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @return RouteConfig Chainable
     */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Route ident must be a string'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string $controller Route controller name.
     * @throws InvalidArgumentException If the controller argument is not a string.
     * @return RouteConfig Chainable
     */
    public function set_controller($controller)
    {
        if (!is_string($controller)) {
            throw new InvalidArgumentException(
                'Route controller must be a string'
            );
        }
        $this->controller = $controller;
        return $this;
    }

    /**
     * Get the clas controller. If it is not set, the `ident` will be used by default.
     *
     * @return string
     */
    public function controller()
    {
        if ($this->controller === null) {
            return $this->ident();
        }
        return $this->controller;
    }

    /**
     * @param string[] $methods The available methods for this route. (ex: ['GET']).
     * @return RouteConfig Chainable
     */
    public function set_methods(array $methods)
    {
        $this->methods = [];
        foreach ($methods as $method) {
            $this->add_method($method);
        }
        return $this;
    }

    /**
     * @param string $method The HTTP method to add support for.
     * @throws InvalidArgumentException If the method is not a string or a valid HTTP method.
     * @return RouteConfig Chainable
     */
    public function add_method($method)
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException(
                'Invalid method "%s". Must be a valid HTTP method.'
            );
        }
        $method = strtoupper($method);
        $valid_http_methods = [
            'DELETE',
            'GET',
            'HEAD',
            'POST',
            'PUT',
            'PATCH',
            'OPTIONS'
        ];
        if (!in_array($method, $valid_http_methods)) {
            throw new InvalidArgumentException(
                'Invalid method "%s". Must be a valid HTTP method.'
            );
        }

        $this->methods[] = $method;
        return $this;
    }

    /**
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }
}
