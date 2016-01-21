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
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\ScriptRouteConfig;
use \Charcoal\App\Script\ScriptFactory;

/**
 *
 */
class ScriptRoute implements
    AppAwareInterface,
    ConfigurableInterface,
    LoggerAwareInterface,
    RouteInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;
    use LoggerAwareTrait;

    /**
     * Create new script route (CLI)
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
     * ConfigurableTrait > create_config()
     *
     * @param mixed|null $data Optional config data.
     * @return ConfigInterface
     */
    public function createConfig($data = null)
    {
        return new ScriptRouteConfig($data);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $config = $this->config();

        $scriptIdent = $config['ident'];
        $scriptController = $config['controller'];

        $scriptFactory = new ScriptFactory();
        $script = $scriptFactory->create($scriptIdent, [
            'app' => $this->app(),
            'logger' => $this->logger
        ]);

        $script->setData($config['script_data']);

        // Run (invoke) script.
        return $script($request, $response);
    }
}