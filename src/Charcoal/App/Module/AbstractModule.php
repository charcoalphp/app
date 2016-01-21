<?php

namespace Charcoal\App\Module;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\Middleware\MiddlewareManager;
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;

/**
 *
 */
abstract class AbstractModule implements
    AppAwareInterface,
    ConfigurableInterface,
    LoggerAwareInterface,
    ModuleInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;
    use LoggerAwareTrait;

    /**
     * @param array $data Module dependencies.
     */
    public function __construct(array $data)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        $this->setApp($data['app']);
    }

    /**
     * @return void
     */
    public function setup()
    {
        $this->setupMiddlewares();
        $this->setupRoutes();
    }

    /**
     * @return void
     */
    protected function setupMiddlewares()
    {
        $middlewares = $this->config['middlewares'];
        if ($middlewares === null || count($middlewares === 0)) {
            return;
        }
        $middleware_manager = new MiddlewareManager([
            'logger' => $this->logger,
            'config' => $middlewares,
            'app' => $this->app()
        ]);
        return $middleware_manager->setup_middlewares();
    }


    /**
     * Set up the app's "global" routes, via a RouteManager
     *
     * @return void
     */
    public function setupRoutes()
    {
        $config = $this->config();
        $routes = $config['routes'];
        if ($routes === null || count($routes) === 0) {
            return;
        }
        $route_manager = new RouteManager([
            'logger' => $this->logger,
            'config' => $routes,
            'app' => $this->app()
        ]);
        return $route_manager->setupRoutes();
    }

    /**
     * @param array $data Optiona configuration data.
     * @return ConfigInterface
     */
    abstract public function createConfig(array $data = null);
}