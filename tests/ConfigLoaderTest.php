<?php
namespace GetSky\Phalcon\ConfigLoader\Test;

use GetSky\Phalcon\ConfigLoader\ConfigLoader;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Loader;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
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
                'json' => '\Phalcon\Config\Adapter\Json'
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
            ['json' => '\Phalcon\Config\Adapter\Json'],
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
     * @dataProvider pathProvider
     */
    public function testInitNamespace($path, $extension)
    {
        $test = new ConfigLoader();

        $method = new ReflectionMethod(self::TEST_CLASS, 'extractExtension');
        $method->setAccessible(true);

        $result = $method->invoke($test, $path);
        $this->assertEquals($extension, $result);
    }


    public function pathProvider()
    {
        return [
            ['test.yml', 'yml'],
            ['test.json', 'json'],
            ['test.ini', 'ini'],
            ['.ini', 'ini'],
            ['test', null]
        ];
    }
} 