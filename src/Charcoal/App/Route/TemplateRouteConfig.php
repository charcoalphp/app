<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// Dependency from 'charcoal-app'
use \Charcoal\App\App;

// Local namespace dependencies
use \Charcoal\App\Route\RouteConfig;

/**
 *
 */
class TemplateRouteConfig extends RouteConfig
{
    /**
     * The template ident (to load).
     * @var string $template
     */
    private $template;

    /**
     * The view engine ident to use.
     * Ex: "mustache", ""
     * @var string $engine
     */
    private $engine;

    /**
     * Additional template data.
     * @var array $templateData
     */
    private $templateData = [];

    /**
     * Redirect URL.
     * @var string $redirect
     */
    private $redirect;

    /**
     * Redirect Mode (HTTP status code).
     * @var integer $redirectMode
     */
    private $redirectMode = 301;

    /**
     * Enable route-level caching for this template.
     * @var boolean $cache
     */
    private $cache = false;

    /**
     * If using cache, the time-to-live, in seconds, of the cache. (0 = no limit).
     * @var integer $cache_ttl
     */
    private $cache_ttl = 0;

    /**
     * @param string|null $template The template identifier.
     * @throws InvalidArgumentException If the tempalte parameter is not null or not a string.
     * @return TemplateRouteConfig Chainable
     */
    public function setTemplate($template)
    {
        if ($template === null) {
            $this->template = null;
            return $this;
        }
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'Template must be a string (the template ident)'
            );
        }
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function template()
    {
        if ($this->template === null) {
            return $this->ident();
        }
        return $this->template;
    }

    /**
     * @return string
     */
    public function defaultController()
    {
        $config = App::instance()->config();

        if ( $config->has('view.default_controller') ) {
            return $config->get('view.default_controller');
        }
    }

    /**
     * @param string|null $engine The engine identifier (mustache, php, or mustache-php).
     * @throws InvalidArgumentException If the engine is not null or not a string.
     * @return TemplateRouteConfig Chainable
     */
    public function setEngine($engine)
    {
        if ($engine === null) {
            $this->engine = null;
            return $this;
        }
        if (!is_string($engine)) {
            throw new InvalidArgumentException(
                'Engine must be a string (the engine ident)'
            );
        }
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return string
     */
    public function engine()
    {
        if ($this->engine === null) {
            return $this->defaultEngine();
        }
        return $this->engine;
    }

    /**
     * @return string
     */
    public function defaultEngine()
    {
        $config = App::instance()->config();

        if ( $config->has('view.default_engine') ) {
            return $config->get('view.default_engine');
        } else {
            return 'mustache';
        }
    }

    /**
     * Set the template data for the view.
     *
     * @param array $templateData The route template data.
     * @return TemplateRouteConfig Chainable
     */
    public function setTemplateData(array $templateData)
    {
        $this->templateData = $templateData;
        return $this;
    }

    /**
     * Get the template data for the view.
     *
     * @return array
     */
    public function templateData()
    {
        return $this->templateData;
    }

    /**
     * @param string $redirect Points to a route.
     * @return TemplateRouteConfig Chainable
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * @return string redirect route
     */
    public function redirect()
    {
        return $this->redirect;
    }

    /**
     * Set the redirect HTTP status mode. (Must be 3xx)
     *
     * @param mixed $redirectMode The HTTP status code.
     * @throws InvalidArgumentException If the redirect mode is not 3xx.
     * @return TemplateRouteConfig Chainable
     */
    public function setRedirectMode($redirectMode)
    {
        $redirectMode = (int)$redirectMode;
        if ($redirectMode < 300 || $redirectMode  >= 400) {
            throw new InvalidArgumentException(
                'Invalid HTTP status for redirect mode'
            );
        }

        $this->redirectMode = $redirectMode;
        return $this;
    }

    /**
     * @return integer
     */
    public function redirectMode()
    {
        return $this->redirectMode;
    }

    /**
     * @param boolean $cache The cache enabled flag.
     * @return TemplateRouteConfig Chainable
     */
    public function setCache($cache)
    {
        $this->cache = !!$cache;
        return $this;
    }

    /**
     * @return boolean
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * @param integer $ttl The cache Time-To-Live, in seconds.
     * @return TemplateRouteConfig Chainable
     */
    public function setCacheTtl($ttl)
    {
        $this->cache_ttl = (int)$ttl;
        return $this;
    }

    /**
     * @return integer
     */
    public function cacheTtl()
    {
        return $this->cache_ttl;
    }
}
