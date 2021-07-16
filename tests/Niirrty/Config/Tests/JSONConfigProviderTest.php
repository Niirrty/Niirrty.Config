<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-31
 * @version        0.1.0
 */


namespace Niirrty\Config\Tests;


use Niirrty\Config\Exceptions\ConfigParseException;
use Niirrty\Config\Exceptions\ConfigProviderException;
use Niirrty\Config\Exceptions\ConfigProviderOptionException;
use Niirrty\Config\IConfiguration;
use Niirrty\Config\Provider\JSONConfigProvider;
use Niirrty\IO\Path;
use PHPUnit\Framework\TestCase;


class JSONConfigProviderTest extends TestCase
{


    /** @type JSONConfigProvider */
    private $_provider;

    public function setUp() : void
    {

        $this->_provider = JSONConfigProvider::Init( __DIR__ . '/../../../data/config.json' );

        parent::setUp();

    }


    public function test_getName()
    {

        $this->assertSame( 'JSON', $this->_provider->getName() );

    }

    public function test_isValid()
    {

        $this->assertTrue( $this->_provider->isValid() );

    }

    public function test_getOptions()
    {

        $this->assertSame(
            [ 'file'       => __DIR__ . '/../../../data/config.json',
              'extensions' => [ 'json' ] ],
            $this->_provider->getOptions()
        );

    }

    public function test_getOption()
    {

        $this->assertSame( [ 'json' ], $this->_provider->getOption( 'extensions' ) );
        $this->assertSame( __DIR__ . '/../../../data/config.json', $this->_provider->getOption( 'file' ) );
        $this->assertNull( $this->_provider->getOption( 'no-existing-item' ) );

    }

    public function test_getOptionNames()
    {

        $this->assertSame(
            [ 'file', 'extensions' ],
            $this->_provider->getOptionNames()
        );

    }

    public function test_setOption()
    {

        $this->_provider->setOption( 'extensions', [ 'json', 'jsonx' ] );
        $this->assertSame(
            [ 'file'       => __DIR__ . '/../../../data/config.json',
              'extensions' => [ 'json', 'jsonx' ] ],
            $this->_provider->getOptions()
        );
        $this->_provider->setOption( 'foo', ':-)' );

    }

    public function test_setOptionException1()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setOption( 'extensions', [ 'jsonp' ] );

    }

    public function test_setOptionException2()
    {

        $this->expectException( \TypeError::class );
        $this->_provider->setOption( 'file', null );

    }

    public function test_hasOption()
    {

        $this->assertTrue( $this->_provider->hasOption( 'file' ) );
        $this->assertTrue( $this->_provider->hasOption( 'extensions' ) );
        $this->assertFalse( $this->_provider->hasOption( 'foo' ) );

    }

    public function test_setFile()
    {

        $this->_provider->setFile( __DIR__ . '/../../../data/config-copy.json' );
        $this->assertTrue( $this->_provider->isValid() );
        $this->assertSame( __DIR__ . '/../../../data/config-copy.json', $this->_provider->getFile() );

    }

    public function test_setFileException1()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config.xml' );

    }

    public function test_setExtensionsException1()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setExtensions( [] );

    }

    public function test_setExtensionsException2()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setExtensions( [ 'json', 1 ] );

    }

    public function test_read()
    {

        $config = $this->_provider->read();
        $this->assertInstanceOf( IConfiguration::class, $config );
        $this->assertEquals(
            [ [
                  'name'        => 'default',
                  'description' => 'A optional section description…',
                  'items'       => [
                      [
                          'name'        => 'foo',
                          'description' => 'A optional item description…',
                          'nullable'    => false,
                          'type'        => 'bool',
                          'value'       => false,
                      ],
                      [
                          'name'        => 'bar',
                          'nullable'    => true,
                          'type'        => 'int',
                          'value'       => 1234,
                          'description' => null,
                      ],
                      [
                          'name'        => 'baz',
                          'nullable'    => true,
                          'type'        => 'string',
                          'value'       => null,
                          'description' => null,
                      ],
                  ],
              ] ],
            $config->toArray()
        );

        $config = $this->_provider->read( [ 'unknownSection' ] );

        $this->assertSame( 0, \count( $config ) );

        $config = $this->_provider->read( [] );

        $this->assertSame( 1, \count( $config ) );

        $configFile = __DIR__ . '/../../../data/config-no-existing.json';
        if ( \file_exists( $configFile ) )
        {
            \unlink( $configFile );
        }
        $this->_provider->setFile( $configFile );
        $config = $this->_provider->read();
        $this->assertSame( 0, \count( $config ) );

    }

    public function test_readException1()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-empty.json' );
        $this->_provider->read();

    }

    public function test_readException2()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format.json' );
        $this->_provider->read();

    }

    public function test_readException3()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-data1.json' );
        $this->_provider->read();

    }

    public function test_readException5()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.json';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \file_put_contents( $configFile, \json_encode( [ [ 'name' => 'default', 'items' => [ [ 'foo' => 14 ] ] ] ] ) );
        $this->expectException( ConfigParseException::class );
        $this->_provider->setFile( $configFile );
        $this->_provider->read();
        \unlink( $configFile );

    }

    public function test_readException6()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.json';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \file_put_contents( $configFile,
                            \json_encode( [ [ 'name' => 'default', 'items' => [ [ 'name' => 'abc' ] ] ] ] ) );
        $this->expectException( ConfigParseException::class );
        $this->_provider->setFile( $configFile );
        $this->_provider->read();
        \unlink( $configFile );

    }

    public function test_readException7()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.json';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \file_put_contents( $configFile,
                            \json_encode( [ [ 'name' => 'default', 'items' => [ [ 'name' => 'abc', 'type' => '\\DateTime', 'value' => new \stdClass() ] ] ] ] ) );
        $this->expectException( ConfigParseException::class );
        $this->_provider->setFile( $configFile );
        $this->_provider->read();
        \unlink( $configFile );

    }

    public function test_write()
    {

        $config = $this->_provider->read();
        $newFile = __DIR__ . '/../../../data/config-tmp.json';
        \touch( $newFile );
        $this->_provider->setFile( $newFile );
        $this->_provider->write( $config );
        $this->assertSame( str_replace( "\r\n", "\n", <<<JSON
[
    {
        "name": "default",
        "description": "A optional section description\u2026",
        "items": [
            {
                "name": "foo",
                "description": "A optional item description\u2026",
                "type": "bool",
                "nullable": false,
                "value": false
            },
            {
                "name": "bar",
                "description": null,
                "type": "int",
                "nullable": true,
                "value": 1234
            },
            {
                "name": "baz",
                "description": null,
                "type": "string",
                "nullable": true,
                "value": null
            }
        ]
    }
]
JSON
                           ),
            \file_get_contents( __DIR__ . '/../../../data/config-tmp.json' ) );
        \unlink( $newFile );

    }

    public function test_writeException1()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.json';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \touch( $configFile );
        $config = $this->_provider->read();
        $this->_provider->setFile( $configFile );
        \chmod( $configFile, 0000 );
        $this->expectException( ConfigProviderException::class );
        $this->_provider->write( $config );
        \chmod( $configFile, 0700 );
        \unlink( $configFile );

    }


}

