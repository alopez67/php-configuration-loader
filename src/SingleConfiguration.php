<?php

namespace ConfigurationLoader;

class SingleConfiguration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $name;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return SingleConfiguration
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return SingleConfiguration
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
