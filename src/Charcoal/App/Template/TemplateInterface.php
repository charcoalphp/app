<?php

namespace Charcoal\App\Template;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppInterface;

/**
 *
 */
interface TemplateInterface
{

    /**
     * @param array $data The template data to set.
     * @return TemplateInterface Chainable
     */
    public function setData(array $data);
}