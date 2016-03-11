<?php

namespace Charcoal\App\ServiceProvider;

use \RuntimeException;

// Dependencies from 'Pimple'
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Dependency from 'charcoal-config'
use \Charcoal\Config\GenericConfig;

// Dependencies from `charcoal-translation`
use \Charcoal\Language\Language;
use \Charcoal\Language\LanguageRepository;
use \Charcoal\Translation\Catalog\Catalog;
use \Charcoal\Translation\TranslationConfig;

// Intra-Module `charcoal-app` dependencies
use \Charcoal\App\Config\TranslatorConfig;

/**
 * Translator Service Provider
 *
 * Configures and provides a Symfony Translator service to a container.
 *
 * ## Services
 * - `translator`
 *
 * ## Helpers
 * - `translator/config` `\Charcoal\App\Config\TranslatorConfig`
 *
 * ## Requirements / Dependencies
 * - `config` A `ConfigInterface` must have been previously registered on the container.
 */
class TranslatorServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @todo   [mcaskill 2016-02-11] Implement translation drivers (Database, File, Yandex)
     *         similar to CacheServiceProvider.
     * @todo   [mcaskill 2016-03-07] Implement a 'setCurrentLanguage' that will update
     *         the environment's locale and our Container's TranslationConfig singeton.
     *         For now, you need to call:
     *
     *         ```php
     *         // Handles setlocale()
     *         $container['translator/config']->setCurrentLanguage($lang);
     *
     *         // Handles ConfigurableTranslationTrait
     *         $container['translator/locales']->setCurrentLanguage($lang);
     *         ```
     * @param  Container $container The container instance.
     * @throws RuntimeException If no active languages are provided or translations are not valid.
     * @return void
     */
    public function register(Container $container)
    {
        $container['translator/config'] = function (Container $container) {
            $appConfig = $container['config'];
            $translatorConfig = new TranslatorConfig();

            if ($appConfig->has('translator')) {
                $translatorConfig->merge($appConfig->get('translator'));
            }

            if ($appConfig->has('locales')) {
                $translatorConfig->setLocales($appConfig->get('locales'));
            }

            if ($appConfig->has('translations')) {
                $translatorConfig->setTranslations($appConfig->get('translations'));
            }

            return $translatorConfig;
        };

        $container['translator/language-repository'] = function (Container $container) {
            $config = $container['translator/config']->locales();

            $loader = new LanguageRepository();
            $loader->setDependencies($container)->setPaths($config['repositories']);

            return $loader;
        };

        $container['translator/locales'] = function (Container $container) {
            $repo   = $container['translator/language-repository'];
            $config = $container['translator/config']->locales();
            $langs  = array_filter($config['languages'], function ($lang) {
                return (!isset($lang['active']) || $lang['active']);
            });

            if (count($config['languages'])) {
                $index = $repo->load(array_keys($langs));

                foreach ($langs as $ident => $data) {
                    $lang = new Language();

                    if (!is_array($data)) {
                        $data = [];
                    }

                    if (isset($index[$ident])) {
                        $data = array_merge($index[$ident], $data);
                    }

                    if (!isset($data['ident'])) {
                        $lang->setIdent($ident);
                    }

                    $lang->setData($data);

                    $langs[$lang->ident()] = $lang;
                }

                $config['languages'] = $langs;
            } else {
                throw new RuntimeException('At least one language must be active (e.g., `$langCode => $langInfo`).');
            }

            $object = new TranslationConfig($config);

            return $object;
        };

        $container['translator/catalog'] = function (Container $container) {
            $config = $container['translator/config']->translations();

            /**
             * Build the array of Language objects from the `TranslationConfig`-filtered list
             * to prevent any bad apples from slipping through.
             */
            $translations = [];
            if (isset($config['messages'])) {
                if (is_array($config['messages'])) {
                    $translations = $config['messages'];
                } else {
                    throw new RuntimeException(
                        'Global translations must be an associative array (e.g., `$ident => $translations`).'
                    );
                }
            }

            $catalog = new Catalog($translations);

            if (isset($config['paths'])) {
                $loader = new GenericConfig();
                $paths  = $config['paths'];

                $languages = $container['translator/locales']->availableLanguages();
                $basePath  = $container['config']->get('base_path');

                if (!is_array($paths)) {
                    $paths = [ $paths ];
                }

                foreach ($paths as &$path) {
                    $path = rtrim($path, '/\\').DIRECTORY_SEPARATOR;

                    if (false === strpos($path, $basePath)) {
                        $path = rtrim($basePath.$path, '/');
                    }

                    foreach ($languages as $langCode) {
                        $exts = [ 'ini', 'json', 'php' ];
                        while ($exts) {
                            $ext = array_pop($exts);
                            $cfg = sprintf('%1$s/messages.%2$s.%3$s', $path, $langCode, $ext);

                            if (file_exists($cfg)) {
                                $loader->addFile($cfg);
                            }
                        }

                        if (count($loader->keys())) {
                            foreach ($loader as $ident => $message) {
                                $catalog->addEntryTranslation($ident, $langCode, $message);
                            }
                        }
                    }
                }
            }

            return $catalog;
        };

        $container['translator'] = function (Container $container) {
            return [];
        };

        /**
         * @todo Figure this shit out!
         */
        TranslationConfig::setInstance($container['translator/locales']);
    }
}
