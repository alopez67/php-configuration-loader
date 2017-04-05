<?php

namespace ConfigurationLoader;

use ConfigurationLoader\Exception\BadParameterException;
use ConfigurationLoader\Exception\MissingDirectoryException;
use ConfigurationLoader\Exception\MissingFileException;

/**
 * Class ConfigurationLoader
 *
 * @package ConfigurationLoader
 */
class ConfigurationLoader
{
    /**
     * @var string
     */
    protected $configPath;

    /**
     * @var array
     */
    protected $config;

    /**
     * ConfigurationLoader constructor.
     *
     * @param string $configPath
     * @param bool   $lazy
     *
     * @throws MissingDirectoryException
     */
    public function __construct(string $configPath = 'config', bool $lazy = true)
    {
        $configPath = rtrim($configPath, '/');
        if (!file_exists($configPath) || !is_dir($configPath)) {
            throw new MissingDirectoryException("Path '" . $configPath . "' does not lead to a directory.");
        }

        $this->configPath = $configPath;
        $this->config = [];

        if (!$lazy) {
            $this->loadDirectory();
        }
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function hasLocal(string $file) : bool
    {
        $response = false;
        $fileLocation = sprintf('%s/%s', $this->configPath, $file);

        if (file_exists($fileLocation . '.local.php')) {
            $response = true;
        }

        return $response;
    }

    /**
     * @param string $file
     *
     * @return mixed
     *
     * @throws BadParameterException
     * @throws MissingFileException
     */
    public function load(string $file)
    {
        $file .= $this->hasLocal($file) ? '.local.php' : '.php';
        $fileLocation = sprintf('%s/%s', $this->configPath, $file);

        if (!file_exists($fileLocation)) {
            throw new MissingFileException("Configuration file '" . $file . "' does not exist.");
        } else {
            $loadedConfig = include $fileLocation;
        }

        if($loadedConfig instanceof ConfigurationInterface) {
            $confValue = $this->addConfig($loadedConfig);
        } else {
            throw new BadParameterException('File ' . $file . ' does not return a proper Configuration object.');
        }

        return $confValue;
    }

    /**
     * @return ConfigurationLoader
     *
     * @throws BadParameterException
     * @throws MissingFileException
     */
    public function loadDirectory() : ConfigurationLoader
    {
        $directoryIterator = new \RecursiveDirectoryIterator($this->configPath);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $filesList = new \RegexIterator($iterator, '/(?!.*?local)^.*\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($filesList as $file) {
            $this->load(
                str_replace(
                    [$this->configPath . '/','.php'],
                    ['', ''],
                    $file[0]
                )
            );
        }

        return $this;
    }

    /**
     * @param $key
     *
     * @return array|null
     */
    public function get($key)
    {
        $return = null;

        if (isset($this->config[$key])) { $return = $this->config[$key]; }

        return $return;
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * @param string $configPath
     *
     * @throws MissingDirectoryException
     *
     * @return string
     */
    public function setConfigPath($configPath)
    {
        $configPath = rtrim($configPath, '/');

        if (!file_exists($configPath) || !is_dir($configPath)) {
            throw new MissingDirectoryException("Path '" . $configPath . "' does not lead to a directory.");
        }

        $this->configPath = rtrim($configPath, '/');

        return $this;
    }

    /**
     * @param ConfigurationInterface $configuration
     *
     * @return mixed
     */
    public function addConfig(ConfigurationInterface $configuration)
    {
        if ($configuration instanceof SingleConfiguration) {
            $append = $configuration->getValue();
        } else {
            $append = [];

            /** @var GroupConfiguration $configuration */
            foreach ($configuration->getConfig() as $c) {
                /** @var $c SingleConfiguration */
                $append[$c->getName()] = $c->getValue();
            }
        }

        $this->config[$configuration->getName()] = $append;

        return $append;
    }

    /**
     * @param array $config
     *
     * @return ConfigurationLoader
     */
    public function setConfig(array $config) : ConfigurationLoader
    {
        $this->config = [];

        foreach ($config as $c) {
            $this->addConfig($c);
        }

        return $this;
    }

    /**
     * @param $key
     *
     * @return array|mixed
     */
    public function getConfig($key = null) : array
    {
        return isset($this->config[$key]) ? $this->config[$key] : $this->config;
    }
}
