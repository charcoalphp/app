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
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\TemplateRouteConfig;
use \Charcoal\App\Template\TemplateFactory;

/**
 *
 */
class TemplateRoute implements
    AppAwareInterface,
    ConfigurableInterface,
    LoggerAwareInterface,
    RouteInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;
    use LoggerAwareTrait;


    /**
     * Create new template route
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
        return new TemplateRouteConfig($data);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     * @todo Implement "view/default_engine" and "view/default_template".
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $tpl_config = $this->config();

        // Handle explicit redirects
        if ($tpl_config['redirect'] !== null) {
            return $response->withRedirect(
                $request->getUri()->withPath($tpl_config['redirect']),
                $tpl_config['redirect_mode']
            );
        }

        $template_ident = $tpl_config['template'];

        if ($tpl_config['cache']) {
            $container = $this->app()->getContainer();
            $cache_pool = $container['cache'];
            $cache_item = $cache_pool->getItem('template', $template_ident);

            $template_content = $cache_item->get();
            if ($cache_item->isMiss()) {
                $cache_item->lock();
                $template_content = $this->templateContent($tpl_config);

                $cache_item->set($template_content, $tpl_config['cache_ttl']);
            }
        } else {
            $template_content = $this->templateContent($tpl_config);
        }



        $response->write($template_content);

        return $response;
    }

    /**
     * @param TemplateRouteConfig $tpl_config The template route configuration.
     * @return string
     */
    protected function templateContent(TemplateRouteConfig $tpl_config)
    {
        $app_config = $this->app()->config();

        $template_ident = $tpl_config['template'];
        $template_controller = $tpl_config['controller'];

        $fallback_controller = $app_config->get('view/default_controller');

        $template_factory = new TemplateFactory();

        if ($fallback_controller) {
            $template_factory->setDefaultClass($fallback_controller);
        }

        $template = $template_factory->create($template_controller, [
            'app'    => $this->app(),
            'logger' => $this->logger
        ]);

        $template_view = $template->view();
        $template_view->setData([
            'template_ident' => $template_ident,
            'engine_type'    => $tpl_config['engine']
        ]);

        $template->setView($template_view);

        // Set custom data from config.
        $template->setData($tpl_config['template_data']);

        $template_content = $template->renderTemplate($template_ident);

        return $template_content;
    }
}