<?php

namespace Charcoal\App\Module;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;

/**
 *
 */
class ModuleManager extends AbstractManager
{
    /**
     * @var array $modules
     */
    private $modules = [];

    /**
     * @param array $modules The list of modules to add.
     * @return ModuleManager Chainable
     */
    public function setModules(array $modules)
    {
        foreach ($modules as $moduleIdent => $moduleConfig) {
            $this->addModule($moduleIdent, $moduleConfig);
        }
        return $this;
    }

    /**
     * @param string                $moduleIdent  The module identifier.
     * @param array|ConfigInterface $moduleConfig The module configuration data.
     * @return ModuleManager Chainable
     */
    public function addModule($moduleIdent, array $moduleConfig)
    {
        $this->modules[$moduleIdent] = $moduleConfig;
        return $this;
    }

    /**
     * @return void
     */
    public function setupModules()
    {
        $modules = $this->config();
        $moduleFactory = new ModuleFactory();
        foreach ($modules as $moduleIdent => $moduleConfig) {
            $module = $moduleFactory->create($moduleIdent, [
                'app'    => $this->app(),
                'logger' => $this->logger
            ]);
            // Merge custom data to config
            $module->config()->merge($moduleConfig);
            $module->setup();
        }
    }
}
