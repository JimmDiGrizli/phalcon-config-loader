<?php
namespace GetSky\Phalcon\ConfigLoader\Test;

use GetSky\Phalcon\ConfigLoader\ConfigLoader;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Loader;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class ConfigLoaderTest extends PHPUnit_Framework_TestCase
{
    const TEST_CLASS = 'GetSky\Phalcon\ConfigLoader\ConfigLoader';

    public function testGetAdapters()
    {
        $test = new ConfigLoader();

        $this->assertEquals(
            [
                'ini' => '\Phalcon\Config\Adapter\Ini',
                'json' => '\Phalcon\Config\Adapter\Json',
                'yml' => '\GetSky\Phalcon\ConfigLoader\Adapter\Yaml'
            ],
            $test->getAdapters()
        );
    }

    public function testAddAdapter()
    {
        $test = new ConfigLoader();
        $test->add('yml', '\Phalcon\Config\Adapter\Yaml');

        $this->assertEquals(
            [
                'ini' => '\Phalcon\Config\Adapter\Ini',
                'json' => '\Phalcon\Config\Adapter\Json',
                'yml' => '\Phalcon\Config\Adapter\Yaml'
            ],
            $test->getAdapters()
        );
    }

    public function testRemoveAdapter()
    {
        $test = new ConfigLoader();
        $test->remove('ini');

        $this->assertEquals(
            [
                'json' => '\Phalcon\Config\Adapter\Json',
                'yml' => '\GetSky\Phalcon\ConfigLoader\Adapter\Yaml'
            ],
            $test->getAdapters()
        );
    }

    public function testRemoveAllAdapters()
    {
        $test = new ConfigLoader();
        $test->removeAll();

        $this->assertEquals([], $test->getAdapters());
    }

    /**
     * @dataProvider pathExtensionProvider
     */
    public function testExtractExtension($path, $extension)
    {
        $test = new ConfigLoader();

        $method = new ReflectionMethod(self::TEST_CLASS, 'extractExtension');
        $method->setAccessible(true);

        $result = $method->invoke($test, $path);
        $this->assertEquals($extension, $result);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCreate($path, $instance, $array)
    {
        $test = new ConfigLoader();
        $result = $test->create($path);
        $this->assertInstanceOf($instance, $result);
        $this->assertEquals($array, $result->toArray());
    }

    /**
     * @expectedException \GetSky\Phalcon\ConfigLoader\ExtensionNotFoundException
     */
    public function testExtensionNotFoundException()
    {
        $test = new ConfigLoader();
        $test->create('test');

    }

    /**
     * @expectedException  \GetSky\Phalcon\ConfigLoader\AdapterNotFoundException
     */
    public function testAdapterNotFoundException()
    {
        $test = new ConfigLoader();
        $test->create('test.yaml');

    }

    public function pathExtensionProvider()
    {
        return [
            ['test.yml', 'yml'],
            ['test.json', 'json'],
            ['test.ini', 'ini'],
            ['.ini', 'ini'],
            ['test', null]
        ];
    }

    public function pathProvider()
    {
        return [
            [
                'test.ini',
                'Phalcon\Config\Adapter\Ini',
                ['test' => ['test' => true]]
            ],
            [
                'tests/test.json',
                'Phalcon\Config\Adapter\Json',
                ['test' => ['test' => true]]
            ],
            [
                'tests/test.yml',
                '\GetSky\Phalcon\ConfigLoader\Adapter\Yaml',
                ['test' => ['test' => true]]
            ]
        ];
    }
} 