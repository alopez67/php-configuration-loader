<?php

namespace ConfigurationLoader;

class GroupConfiguration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * ConfigurationGroup constructor.
     *
     * @param string $name
     * @param array $config
     */
    protected $config;

    public function __construct(string $name, array $config = [])
    {
        $this->name = $name;
        $this->setConfig($config);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return GroupConfiguration
     */
    public function setName(string $name): GroupConfiguration
    {
        $this->name = $name;

        return $this;
    }

    public function get($key)
    {
        return isset($this->config[$key]) ? $this->config[$key]->getValue() : null;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(array $config) : GroupConfiguration
    {
        $this->config = [];

        foreach ($config as $c) {
            $this->addConfig($c);
        }

        return $this;
    }

    public function addConfig(SingleConfiguration $singleConfiguration) : GroupConfiguration
    {
        $this->config[$singleConfiguration->getName()] = $singleConfiguration;

        return $this;
    }
}
