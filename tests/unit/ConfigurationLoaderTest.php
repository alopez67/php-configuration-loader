<?php
declare(strict_types=1);

namespace Tests\Unit;

use ConfigurationLoader\ConfigurationLoader;
use ConfigurationLoader\Exception\BadParameterException;
use ConfigurationLoader\Exception\MissingDirectoryException;
use ConfigurationLoader\Exception\MissingFileException;
use ConfigurationLoader\GroupConfiguration;
use ConfigurationLoader\SingleConfiguration;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class ConfigurationLoaderTest extends TestCase
{
    /** @var vfsStreamDirectory $configDirectory */
    protected $configDirectory;

    public function setUp()
    {
        $this->configDirectory = vfsStream::setup('config');
        $configStartsWith = '<?php return ';

        vfsStream::newFile('config1.php')->at($this->configDirectory)->setContent(
            $configStartsWith . "new \\ConfigurationLoader\\SingleConfiguration('config1','config1value');"
        );

        vfsStream::newFile('localtest/config2.php')->at($this->configDirectory)->setContent(
            $configStartsWith . "new \\ConfigurationLoader\\SingleConfiguration('config2','config2value');"
        );

        vfsStream::newFile('localtest/config2.local.php')->at($this->configDirectory)->setContent(
            $configStartsWith . "new \\ConfigurationLoader\\SingleConfiguration('config2','config2valueoverride');"
        );

        vfsStream::newFile('config2/foo.php')->at($this->configDirectory)->setContent(
            $configStartsWith . "new \\ConfigurationLoader\\GroupConfiguration('configGroup1',
                [
                    new \\ConfigurationLoader\\SingleConfiguration('confgroupsingle1', 'config2foo'),
                    new \\ConfigurationLoader\\SingleConfiguration('confgroupsingle2', 'config2foo2'),
                ]
            );"
        );
    }

    public function tearDown()
    {
        $this->configDirectory = null;
    }

    public function testCannotDeclareWithUnexistingDirectory()
    {
        $this->expectException(MissingDirectoryException::class);
        $loader = new ConfigurationLoader('foo/bar/config/foo/bar/config');
    }

    public function testLazyDeclaration()
    {
        $loader = new ConfigurationLoader($this->configDirectory->url());
        $this->assertAttributeEmpty('config', $loader);
    }

    public function testRecognitionDeclaration()
    {
        $loader = new ConfigurationLoader($this->configDirectory->url(), false);

        $this->assertAttributeEquals(
            [
                'config1' => 'config1value',
                'configGroup1' =>
                    [
                        'confgroupsingle1' => 'config2foo',
                        'confgroupsingle2' => 'config2foo2'
                    ]
            ],
            'config',
            $loader
        );
    }

    public function testConfigAccessors()
    {
        $singleConfiguration1 = new SingleConfiguration('foo', 'conf1');
        $singleConfiguration2 = new GroupConfiguration('configGroup1',
            [
                new SingleConfiguration('confgroupsingle1', 'config2foo'),
                new SingleConfiguration('confgroupsingle2', 'config2foo2'),
            ]
        );

        $loader = new ConfigurationLoader($this->configDirectory->url(), false);
        $loader = $loader->setConfig([$singleConfiguration1, $singleConfiguration2]);

        $this->assertInstanceOf(ConfigurationLoader::class, $loader);
        $this->assertEquals(
            [
                'foo' => 'conf1',
                'configGroup1' =>
                    [
                        'confgroupsingle1' => 'config2foo',
                        'confgroupsingle2' => 'config2foo2'
                    ]
            ], $loader->getConfig());

        $this->assertEquals('conf1', $loader->get('foo'));
    }

    public function testConfigPathAccessors()
    {

        $loader = new ConfigurationLoader($this->configDirectory->url());
        $existingDirectory2 = vfsStream::setup('existing2');

        $loader->setConfigPath($existingDirectory2->url());

        $this->assertEquals($existingDirectory2->url(), $loader->getConfigPath());
    }

    public function testFileHasLocal()
    {
        $loader = new ConfigurationLoader($this->configDirectory->url(), false);
        $this->assertEquals(true, $loader->hasLocal('localtest/config2'));
    }

    public function testFileDoesntHaveLocal()
    {
        $loader = new ConfigurationLoader($this->configDirectory->url(), false);
        $this->assertEquals(false, $loader->hasLocal('config1'));
    }

    public function testLoadExistingSingleConfig()
    {
        $loader = new ConfigurationLoader($this->configDirectory->url());
        $loader->load('config1');

        $this->assertAttributeEquals(
            ['config1' => 'config1value'],
            'config',
            $loader
        );
    }

    public function testLoadExistingSingleLocalConfig()
    {
        $loader = new ConfigurationLoader($this->configDirectory->url());
        $loader->load('localtest/config2');

        $this->assertAttributeEquals(
            ['config2' => 'config2valueoverride'],
            'config',
            $loader
        );
    }

    public function testCannotLoadIncorrectSingleConfig()
    {
        $brokenConfigDirectory = vfsStream::setup('broken');
        vfsStream::newFile('broken.php')->at($brokenConfigDirectory)->setContent(
            "<?php return 'foo';"
        );

        $this->expectException(BadParameterException::class);

        $loader = new ConfigurationLoader($brokenConfigDirectory->url());
        $loader->load('broken');
    }

    public function testCannotLoadUnexistingSingleConfig()
    {
        $this->expectException(MissingFileException::class);

        $loader = new ConfigurationLoader($this->configDirectory->url());
        $loader->load('config/foo/bar/config/foo/foo/bar/bar');
    }

    public function testCannotLoadUnexistingConfigDirectory()
    {
        $loader = new ConfigurationLoader($this->configDirectory->url());

        $this->expectException(MissingDirectoryException::class);
        $loader->setConfigPath('config/foo/foo/bar/foo/config/foo');
    }
}
