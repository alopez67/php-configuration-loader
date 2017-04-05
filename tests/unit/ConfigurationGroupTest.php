<?php

namespace Tests\Unit;

use ConfigurationLoader\GroupConfiguration;
use ConfigurationLoader\SingleConfiguration;
use PHPUnit\Framework\TestCase;

class GroupConfigurationTest extends TestCase
{
    public function testAddConfigurationObject()
    {
        $singleConfiguration1 = new SingleConfiguration('foo', 'conf1');
        $singleConfiguration2 = new SingleConfiguration('bar', 'conf2');

        $configurationGroup = new GroupConfiguration('fooGroup');
        $configurationGroup->addConfig($singleConfiguration1);
        $configurationGroup->addConfig($singleConfiguration2);

        $this->assertEquals('conf1', $configurationGroup->get('foo'));
    }

    public function testGetConfiguration()
    {
        $singleConfiguration1 = new SingleConfiguration('foo', 'conf1');
        $singleConfiguration2 = new SingleConfiguration('bar', 'conf2');

        $configurationGroup = [
            $singleConfiguration1,
            $singleConfiguration2
        ];

        $configurationGroupObject = new GroupConfiguration('foo',$configurationGroup);

        $this->assertEquals('conf1', $configurationGroupObject->get('foo'));
        $this->assertNull($configurationGroupObject->get('foo2'));
    }

    public function testSetName()
    {
        $configurationGroup = new GroupConfiguration('foo');
        $configurationGroup = $configurationGroup->setName('foo2');

        $this->assertAttributeEquals('foo2', 'name', $configurationGroup);
        $this->assertInstanceOf(GroupConfiguration::class, $configurationGroup);
    }

    public function testGetName()
    {
        $configurationGroup = new GroupConfiguration('fooName');

        $this->assertEquals('fooName', $configurationGroup->getName());
    }
}
