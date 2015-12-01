<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\App\Route\RouteConfig;

/**
 *
 */
class TemplateRouteConfig extends RouteConfig
{
    /**
     * The template ident (to load)
     * @var string $template
     */
    private $template;

    /**
     * The view engine ident to use
     * Ex: "mustache", ""
     * @var string $engine
     */
    private $engine;

    /**
     * Additional template data
     * @var array $template_data
     */
    private $template_data = [];

    /**
     * @param string|null $template The template identifier.
     * @throws InvalidArgumentException If the tempalte parameter is not null or not a string.
     * @return TemplateRouteConfig Chainable
     */
    public function set_template($template)
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
    public function default_template()
    {
        return $this->ident();
    }

    /**
     * @param string|null $engine The engine identifier (mustache, php, or mustache-php).
     * @throws InvalidArgumentException If the engine is not null or not a string.
     * @return TemplateRouteConfig Chainable
     */
    public function set_engine($engine)
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
            return $this->default_engine();
        }
        return $this->engine;
    }

    /**
     * @return string
     */
    public function default_engine()
    {
        return 'mustache';
    }

    /**
     * Set the template custom data.
     *
     * @param array $template_data The route template data.
     * @return TemplateRouteConfig Chainable
     */
    public function set_template_data(array $template_data)
    {
        $this->template_data = $template_data;
        return $this;
    }

    /**
     * Get the template custom data.
     *
     * @return array
     */
    public function template_data()
    {
        return $this->template_data;
    }
}
