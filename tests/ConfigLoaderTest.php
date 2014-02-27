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
        $test = new ConfigLoader('dev');

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
        $test = new ConfigLoader('dev');
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
        $test = new ConfigLoader('dev');
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
        $test = new ConfigLoader('dev');
        $test->removeAll();

        $this->assertEquals([], $test->getAdapters());
    }

    /**
     * @dataProvider pathExtensionProvider
     */
    public function testExtractExtension($path, $extension)
    {
        $test = new ConfigLoader('dev');

        $method = new ReflectionMethod(self::TEST_CLASS, 'extractExtension');
        $method->setAccessible(true);

        $result = $method->invoke($test, $path);
        $this->assertEquals($extension, $result);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCreateWithoutImportResources(
        $path,
        $instance,
        $env,
        $array
    )
    {
        $test = new ConfigLoader($env);
        $result = $test->create($path, false);
        $this->assertInstanceOf($instance, $result);
        $this->assertEquals($array, $result->toArray());
    }

    /**
     * @dataProvider pathProviderImportResource
     */
    public function testCreateWithImportResources(
        $path,
        $instance,
        $env,
        $array
    )
    {
        $test = new ConfigLoader($env);
        $result = $test->create($path);
        $this->assertInstanceOf($instance, $result);
        $this->assertEquals($array, $result->toArray());
    }

    /**
     * @expectedException \GetSky\Phalcon\ConfigLoader\ExtensionNotFoundException
     */
    public function testExtensionNotFoundException()
    {
        $test = new ConfigLoader('dev');
        $test->create('test');

    }

    /**
     * @expectedException  \GetSky\Phalcon\ConfigLoader\AdapterNotFoundException
     */
    public function testAdapterNotFoundException()
    {
        $test = new ConfigLoader('dev');
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
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => '%res:import.ini',
                        '%res%' => 'import.ini'
                    ]
                ]
            ],
            [
                'tests/test.json',
                'Phalcon\Config\Adapter\Json',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => '%res:import.ini',
                        '%res%' => 'import.ini'
                    ]
                ]
            ],
            [
                'tests/test.yml',
                '\GetSky\Phalcon\ConfigLoader\Adapter\Yaml',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => '%res:import.ini',
                        '%res%' => 'import.ini'
                    ]
                ]
            ],
        ];
    }

    public function pathProviderImportResource()
    {
        return [
            [
                'test.ini',
                'Phalcon\Config\Adapter\Ini',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'dev'
                        ],
                        '%res%' => 'import.ini',
                        'import' => true,
                        'env' => 'dev'
                    ]
                ]
            ],
            [
                'tests/test.json',
                'Phalcon\Config\Adapter\Json',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'dev'
                        ],
                        '%res%' => 'import.ini',
                        'import' => true,
                        'env' => 'dev'
                    ]
                ]
            ],
            [
                'tests/test.yml',
                '\GetSky\Phalcon\ConfigLoader\Adapter\Yaml',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'dev'
                        ],
                        '%res%' => 'import.ini',
                        'import' => true,
                        'env' => 'dev'
                    ]
                ]
            ],
            [
                'test.ini',
                'Phalcon\Config\Adapter\Ini',
                'prod',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'prod'
                        ],
                        '%res%' => 'import.ini',
                        'import' => true,
                        'env' => 'prod'
                    ]
                ]
            ],
            [
                'tests/test.json',
                'Phalcon\Config\Adapter\Json',
                'prod',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'prod'
                        ],
                        '%res%' => 'import.ini',
                        'import' => true,
                        'env' => 'prod'
                    ]
                ]
            ],
            [
                'tests/test.yml',
                '\GetSky\Phalcon\ConfigLoader\Adapter\Yaml',
                'prod',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'prod'
                        ],
                        '%res%' => 'import.ini',
                        'import' => true,
                        'env' => 'prod'
                    ]
                ]
            ],
            [
                'test.ini',
                'Phalcon\Config\Adapter\Ini',
                null,
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => null
                        ],
                        '%res%' => 'import.ini',
                        'import' => true,
                        'env' => null
                    ]
                ]
            ]
        ];
    }
}
