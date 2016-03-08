<?php

namespace Charcoal\App;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Config\AbstractConfig;

use \Charcoal\App\Config\CacheConfig;
use \Charcoal\App\Config\LoggerConfig;

/**
 * Charcoal App configuration
 */
class AppConfig extends AbstractConfig
{
    /**
     * @var string $timezone
     */
    private $timezone;

    /**
     * @var string $projectName
     */
    private $projectName;

    /**
     * @var string $basePath
     */
    private $basePath;

    /**
     * @var string $publicPath
     */
    private $publicPath;

    /**
     * @var boolean $devMode
     */
    private $devMode = false;

    /**
     * @var array $routes
     */
    private $routes = [];

    /**
     * @var array $routables
     */
    private $routables = [];

    /**
     * @var array $modules
     */
    private $modules = [];

    /**
     * @var CacheConfig $cache
     */
    private $cache;

    /**
     * @var LoggerConfig $logger
     */
    private $logger;

    /**
     * @var ViewConfig $view
     */
    protected $view;

    /**
     * @var array $databases
     */
    private $databases = [];

    /**
     * @var string $defaultDatabase
     */
    private $defaultDatabase;

    /**
     * Default app-config values.
     *
     * @return array
     */
    public function defaults()
    {
        $baseDir = rtrim(realpath(__DIR__.'/../../../'), '/').'/';

        return [
            'project_name'     => '',
            'base_path'        => $baseDir,
            'public_path'      => null,
            'timezone'         => 'UTC',
            'routes'           => [],
            'routables'        => [],
            'modules'          => [],
            'translator'       => [],
            'cache'            => [],
            'logger'           => [],
            'view'             => [],
            'databases'        => [],
            'default_database' => 'default',
            'dev_mode'         => false
        ];
    }

    /**
     * Set the application's absolute root path.
     *
     * Resolves symlinks with realpath() and ensure trailing slash.
     *
     * @deprecated In favor of {self::setBasePath()}
     * @todo  Add `trigger_error('...', E_USER_DEPRECATED)`: "setROOT() is deprecated. Use setBasePath() instead."
     * @param string $path The absolute path to the application's root directory.
     * @return AppConfig Chainable
     */
    public function setROOT($path)
    {
        $this->setBasePath($path);

        return $this;
    }

    /**
     * Retrieve the application's absolute root path.
     *
     * @deprecated In favor of {self::basePath()}
     * @todo  Add `trigger_error('...', E_USER_DEPRECATED)`: "ROOT() is deprecated. Use basePath() instead."
     * @return string The absolute path to the application's root directory.
     */
    public function ROOT()
    {
        return $this->basePath();
    }

    /**
     * Set the application's absolute root path.
     *
     * Resolves symlinks with realpath() and ensure trailing slash.
     *
     * @param string $path The absolute path to the application's root directory.
     * @return AppConfig Chainable
     */
    public function setBasePath($path)
    {
        $this->basePath = rtrim(realpath($path), '\\/').'/';

        return $this;
    }

    /**
     * Retrieve the application's absolute root path.
     *
     * @return string The absolute path to the application's root directory.
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Set the application's absolute path to the public web directory.
     *
     * @param string $path The path to the application's public directory.
     * @return AppConfig Chainable
     */
    public function setPublicPath($path)
    {
        $this->publicPath = rtrim(realpath($path), '\\/').'/';

        return $this;
    }

    /**
     * Retrieve the application's absolute path to the public web directory.
     *
     * @return string The absolute path to the application's public directory.
     */
    public function publicPath()
    {
        if (!isset($this->publicPath)) {
            return $this->basePath().DIRECTORY_SEPARATOR.'www/';
        }

        return $this->publicPath;
    }

    /**
     * Retrieve the application's fully qualified base URL to the public web directory.
     *
     * @return string The base URI to the application's web directory.
     * @todo   [mcaskill 2016-03-08] Deprecate {@see self::$URL} in favor of 'base_url'; {@see \Slim\Http\Uri}.
     */
    public function baseUrl()
    {
        if (isset($this->URL)) {
            return $this->URL;
        }

        return '';
    }

    /**
     * @param string $timezone The timezone string.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return CharcoalConfig Chainable
     */
    public function setTimezone($timezone)
    {
        if (!is_string($timezone)) {
            throw new InvalidArgumentException(
                'Timezone must be a string.'
            );
        }
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Retrieve the application's default timezone.
     *
     * Will be used by the PHP date and date-time functions.
     *
     * @return string
     */
    public function timezone()
    {
        if (isset($this->timezone)) {
            return $this->timezone;
        } else {
            return 'UTC';
        }
    }

    /**
     * Sets the project name.
     *
     * @param string|null $projectName The project name.
     * @throws InvalidArgumentException If the project argument is not a string (or null).
     * @return CharcoalConfig Chainable
     */
    public function setProjectName($projectName)
    {
        if ($projectName === null) {
            $this->projectName = null;
            return $this;
        }
        if (!is_string($projectName)) {
            throw new InvalidArgumentException(
                'Project name must be a string'
            );
        }
        $this->projectName = $projectName;
        return $this;
    }

    /**
     * @return string
     */
    public function projectName()
    {
        if ($this->projectName === null) {
            return $this->url();
        }
        return $this->projectName;
    }

    /**
     * @param boolean $devMode The "dev mode" flag.
     * @return CharcoalConfig Chainable
     */
    public function setDevMode($devMode)
    {
        $this->devMode = !!$devMode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function devMode()
    {
        return !!$this->devMode;
    }

    /**
     * @param array $translator The translator configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setTranslator(array $translator)
    {
        if (!isset($this->translator)) {
            $this->translator = [];
        }

        $toIterate = [ 'locales', 'translations' ];
        foreach ($translator as $key => $val) {
            if (in_array($key, $toIterate) && isset($this->translator[$key])) {
                $this->translator[$key] = array_merge($this->translator[$key], $val);
            } else {
                $this->translator[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * @param array $view The view configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setView(array $view)
    {
        if (!isset($this->view)) {
            $this->view = [];
        }

        $this->view = array_merge($this->view, $view);

        return $this;
    }

    /**
     * @param array $routes The route configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setRoutes(array $routes)
    {
        if (!isset($this->routes)) {
            $this->routes = [];
        }

        $toIterate = [ 'templates', 'actions', 'scripts' ];
        foreach ($routes as $key => $val) {
            if (in_array($key, $toIterate) && isset($this->routes[$key])) {
                $this->routes[$key] = array_merge($this->routes[$key], $val);
            } else {
                $this->routes[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * @param array $routables The routable configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setRoutables(array $routables)
    {
        $this->routables = $routables;
        return $this;
    }

    /**
     * @return array
     */
    public function routables()
    {
        return $this->routables;
    }

    /**
     * Set the configuration modules.
     *
     * The modules are defined in a `key`=>`\Charcoal\App\Module\ModuleConfig` structure.
     *
     * @param array $modules The module configuration structure to set.
     * @return AppConfig Chainable
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * @return array
     */
    public function modules()
    {
        return $this->modules;
    }

    /**
     * @param array|CacheConfig $cache The application global cache config.
     * @throws InvalidArgumentException If the argument is not an array or a config.
     * @return AppConfig Chainable
     */
    public function setCache($cache)
    {
        if ($cache instanceof CacheConfig) {
            $this->cache = $cache;
            $this->cache->addDelegate($this);
        } elseif (is_array($cache)) {
            $this->cache = new CacheConfig($cache, [$this]);
        } else {
            throw new InvalidArgumentException(
                'Cache must be an array of config options or a CacheConfig object.'
            );
        }
        return $this;
    }

    /**
     * Get the application's global `CacheConfig`.
     *
     * @return CacheConfig
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * @param array|LoggerConfig $logger The global logger config.
     * @throws InvalidArgumentException If the argument is not an array or a config.
     * @return AppConfig Chainable
     */
    public function setLogger($logger)
    {
        if ($logger instanceof LoggerConfig) {
            $this->logger = $logger;
            $this->logger->addDelegate($this);
        } elseif (is_array($logger)) {
            $this->logger = new LoggerConfig($logger, [$this]);
        } else {
            throw new InvalidArgumentException(
                'Logger must be an array of config options or a LoggerConfig object.'
            );
        }
        return $this;
    }

    /**
     * Get the application's global `LoggerConfig`
     *
     * @return LoggerConfig
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * @param array $databases The avaiable databases config.
     * @return Config Chainable
     */
    public function setDatabases(array $databases)
    {
        $this->databases = $databases;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method and no databases were set.
     * @return array
     */
    public function databases()
    {
        if ($this->databases === null) {
            throw new Exception(
                'Databases are not set.'
            );
        }
        return $this->databases;
    }

    /**
     * @param string $ident The ident of the database to return the configuration of.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @throws Exception If trying to access an invalid database.
     * @return array
     */
    public function databaseConfig($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Default database must be a string.'
            );
        }
        $databases = $this->databases();
        if (!isset($databases[$ident])) {
            throw new Exception(
                sprintf('No database configuration matches "%s".', $ident)
            );
        }
        return $databases[$ident];
    }

    /**
     * @param string $defaultDatabase The default database ident.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return CharcoalConfig Chainable
     */
    public function setDefaultDatabase($defaultDatabase)
    {
        if (!is_string($defaultDatabase)) {
            throw new InvalidArgumentException(
                'Default database must be a string.'
            );
        }
        $this->defaultDatabase = $defaultDatabase;
        return $this;
    }

    /**
     * @param string $ident  The database ident.
     * @param array  $config The database options.
     * @throws InvalidArgumentException If the arguments are invalid.
     * @return CharcoalConfig Chainable
     */
    public function addDatabase($ident, array $config)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Database ident must be a string.'
            );
        }

        if ($this->databases === null) {
            $this->databases = [];
        }
        $this->databases[$ident] = $config;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method before a setter.
     * @return mixed
     */
    public function defaultDatabase()
    {
        if ($this->defaultDatabase === null) {
            throw new Exception(
                'Default database is not set.'
            );
        }
        return $this->defaultDatabase;
    }
}
