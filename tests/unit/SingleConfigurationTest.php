<?php
declare(strict_types=1);

namespace Tests\Unit;

use ConfigurationLoader\SingleConfiguration;
use PHPUnit\Framework\TestCase;

class SingleConfigurationTest extends TestCase
{
    public function testSetValue()
    {
        $singleConfiguration = new SingleConfiguration('foo', 'fooValue');
        $singleConfiguration->setValue('fooValue2');

        $this->assertAttributeEquals('fooValue2', 'value', $singleConfiguration);
        $this->assertInstanceOf(SingleConfiguration::class, $singleConfiguration);
    }

    public function testGetValue()
    {
        $singleConfiguration = new SingleConfiguration('foo', 'fooValue');

        $this->assertEquals('fooValue', $singleConfiguration->getValue());
    }

    public function testSetName()
    {
        $singleConfiguration = new SingleConfiguration('foo', 'fooValue');
        $singleConfiguration = $singleConfiguration->setName('foo2');

        $this->assertAttributeEquals('foo2', 'name', $singleConfiguration);
        $this->assertInstanceOf(SingleConfiguration::class, $singleConfiguration);
    }

    public function testGetName()
    {
        $singleConfiguration = new SingleConfiguration('fooName', 'foo');

        $this->assertEquals('fooName', $singleConfiguration->getName());
    }
}
