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

    public function testClearConfig()
    {
        $test = new ConfigLoader('prod');
        $result = $test->clear($test->create('../../tests/test.ini'), $test->create('../../tests/means.ini'));

        $this->assertEquals($result, new Config(['foo' => 1]));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCreateWithoutImportResources(
        $path,
        $env,
        $array
    )
    {
        $test = new ConfigLoader($env);
        $result = $test->create($path, false);
        $this->assertInstanceOf("Phalcon\\Config", $result);
        $this->assertEquals($array, $result->toArray());
    }

    /**
     * @dataProvider pathProviderImportResource
     */
    public function testCreateWithImportResources(
        $path,
        $env,
        $array
    )
    {
        $test = new ConfigLoader($env);
        $result = $test->create($path);
        $this->assertInstanceOf("Phalcon\\Config", $result);
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
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => '%res:import.ini',
                        '%res%' => 'import.ini',
                        '%module%' => '::SERVICES',
                        'modules' => '%module:::SERVICES',
                        'import' => false
                    ]
                ]
            ],
            [
                'tests/test.json',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => '%res:import.ini',
                        '%res%' => 'import.ini',
                        '%module%' => '::SERVICES',
                        'modules' => '%module:::SERVICES',
                        'import' => false
                    ]
                ]
            ],
            [
                'tests/test.yml',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => '%res:import.ini',
                        '%res%' => 'import.ini',
                        '%module%' => '::SERVICES',
                        'modules' => '%module:::SERVICES',
                        'import' => false
                    ]
                ]
            ],
            [
                'test_%environment%.ini',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => '%res:import.ini',
                        '%res%' => 'import.ini',
                        '%module%' => '::SERVICES',
                        'modules' => '%module:::SERVICES'
                    ]
                ]
            ]
        ];
    }

    public function pathProviderImportResource()
    {
        return [
            [
                'test.ini',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'dev'
                        ],
                        'import' => false,
                        'env' => 'dev',
                        'module' => true,
                        'modules' => [
                            'module' => true
                        ]
                    ]
                ]
            ],
            [
                'tests/test.json',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'dev'
                        ],
                        'import' => false,
                        'env' => 'dev',
                        'module' => true,
                        'modules' => [
                            'module' => true
                        ]
                    ]
                ]
            ],
            [
                'tests/test.yml',
                'dev',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'dev'
                        ],
                        'import' => false,
                        'env' => 'dev',
                        'module' => true,
                        'modules' => [
                            'module' => true
                        ]
                    ]
                ]
            ],
            [
                'test.ini',
                'prod',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'prod'
                        ],
                        'import' => false,
                        'env' => 'prod',
                        'module' => true,
                        'modules' => [
                            'module' => true
                        ]
                    ]
                ]
            ],
            [
                'tests/test.json',
                'prod',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'prod'
                        ],
                        'import' => false,
                        'env' => 'prod',
                        'module' => true,
                        'modules' => [
                            'module' => true
                        ]
                    ]
                ]
            ],
            [
                'tests/test.yml',
                'prod',
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => 'prod'
                        ],
                        'import' => false,
                        'env' => 'prod',
                        'module' => true,
                        'modules' => [
                            'module' => true
                        ]
                    ]
                ]
            ],
            [
                'test.ini',
                null,
                [
                    'test' => [
                        'test' => true,
                        'exp' => [
                            'import' => true,
                            'env' => null
                        ],
                        'import' => false,
                        'env' => null,
                        'module' => true,
                        'modules' => [
                            'module' => true
                        ]
                    ]
                ]
            ]
        ];
    }
}
