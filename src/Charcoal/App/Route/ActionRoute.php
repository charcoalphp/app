<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigInterface;
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\ActionRouteConfig;

/**
 *
 */
class ActionRoute implements
    AppAwareInterface,
    RouteInterface,
    LoggerAwareInterface,
    ConfigurableInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;
    use LoggerAwareTrait;

    /**
     * Create new action route
     *
     * ### Dependencies
     *
     * **Required**
     *
     * - `config` — ScriptRouteConfig
     * - `app`    — AppInterface
     *
     * **Optional**
     *
     * - `logger` — PSR-3 Logger
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        $this->setConfig($data['config']);
        $this->setApp($data['app']);
    }

    /**
     * ConfigurableTrait > createConfig()
     *
     * @param mixed|null $data Optional config data.
     * @return ConfigInterface
     */
    public function createConfig($data = null)
    {
        return new ActionRouteConfig($data);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $config = $this->config();

        $actionController = $config['controller'];

        $actionFactory = new ActionFactory();
        $action = $actionFactory->create($actionController, [
            'app' => $this->app(),
            'logger' => $this->logger
        ]);

        $action->setData($config['action_data']);

        // Run (invoke) action.
        return $action($request, $response);
    }
}