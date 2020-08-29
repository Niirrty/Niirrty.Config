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
use Niirrty\Config\Provider\PHPConfigProvider;
use Niirrty\IO\Path;
use Niirrty\IO\Vfs\VfsHandler;
use Niirrty\IO\Vfs\VfsManager;
use PHPUnit\Framework\TestCase;


class PHPConfigProviderTest extends TestCase
{


    /** @type PHPConfigProvider */
    private $_provider;


    public function setUp()
    {

        $this->_provider = PHPConfigProvider::Init( __DIR__ . '/../../../data/config.php' );

        parent::setUp();

    }

    public function tearDown()
    {

        \chmod( __DIR__ . '/../../../data', 0700 );
        parent::tearDown();

    }


    public function test_setFile()
    {

        $this->_provider->setFile( __DIR__ . '/../../../data/config-copy.php' );
        $this->assertTrue( $this->_provider->isValid() );
        $this->assertSame( __DIR__ . '/../../../data/config-copy.php', $this->_provider->getFile() );

        $vfsManager = ( new VfsManager() )->addHandler(
            new VfsHandler( 'data', 'data', '://', __DIR__ . '/../../../data' )
        );

        $this->_provider->setFile( 'data://config.php', $vfsManager );
        $this->assertTrue( $this->_provider->isValid() );
        $this->assertSame( __DIR__ . '/../../../data/config.php', $this->_provider->getFile() );

        $this->_provider->setFile( 'data://config.php' );
        $this->assertTrue( $this->_provider->isValid() );
        $this->assertSame( __DIR__ . '/../../../data/config.php', $this->_provider->getFile() );

    }

    public function test_setFileException1()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data' );

    }

    public function test_setFileException2()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data1/foo.php' );

    }

    public function test_setFileException3()
    {

        $this->expectException( ConfigProviderOptionException::class );
        \chmod( __DIR__ . '/../../../data', 0000 );
        $this->_provider->setFile( __DIR__ . '/../../../data/foo.php' );

    }

    public function test_setOption()
    {

        $this->_provider->setOption( 'foo', 12345 );
        $this->assertSame( 12345, $this->_provider->getOption( 'foo' ) );

    }

    public function test_setFileException4()
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
        $this->_provider->setExtensions( [ 'php', 1 ] );

    }

    public function test_setExtensionsException3()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setExtensions( [ 'abc' ] );

    }

    public function test_getExtensions()
    {

        $this->assertSame( [ 'php' ], $this->_provider->getExtensions() );

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

        $provider = PHPConfigProvider::Init( __DIR__ . '/../../../data/config2.php' );

        $config = $provider->read();
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

        $this->assertSame( 1, \count( $config ) );

        $configFile = __DIR__ . '/../../../data/new-config.php';

        if ( \file_exists( $configFile ) )
        {
            \unlink( $configFile );
        }

        $provider = PHPConfigProvider::Init( $configFile );

        $config = $provider->read();
        $this->assertInstanceOf( IConfiguration::class, $config );
        $this->assertSame( 0, \count( $config ) );

    }

    public function test_readException2()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format.php' );
        $this->_provider->read();

    }

    public function test_readException3()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-data1.php' );
        $this->_provider->read();

    }

    public function test_readException4()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.php';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \file_put_contents( $configFile, \json_encode( [ [ 'name' => 'default' ] ] ) );
        \chmod( $configFile, 0000 );
        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( $configFile );
        $this->_provider->read();
        \chmod( $configFile, 0700 );
        \unlink( $configFile );

    }

    public function test_readException5()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.php';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \file_put_contents( $configFile, \json_encode( [ [ 'name' => 'default', 'items' => [ [ 'foo' => 14 ] ] ] ] ) );
        $this->expectException( ConfigProviderException::class );
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
        $configFile = $rootFolder . '/config.php';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \file_put_contents( $configFile,
                            \json_encode( [ [ 'name' => 'default', 'items' => [ [ 'name' => 'abc' ] ] ] ] ) );
        $this->expectException( ConfigProviderException::class );
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
        $configFile = $rootFolder . '/config.php';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \file_put_contents( $configFile,
                            \json_encode( [ [ 'name' => 'default', 'items' => [ [ 'name' => 'abc', 'type' => '\\DateTime', 'value' => new \stdClass() ] ] ] ] ) );
        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( $configFile );
        $this->_provider->read();
        \unlink( $configFile );

    }

    public function test_readException8()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-data2.php' );
        $this->_provider->read();

    }

    public function test_readException9()
    {

        $this->expectException( ConfigParseException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-data3.php' );
        $this->_provider->read();

    }

    public function test_readException10()
    {

        $this->expectException( ConfigParseException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-data4.php' );
        $this->_provider->read();

    }

    public function test_write()
    {

        $config = $this->_provider->read();
        $newFile = __DIR__ . '/../../../data/config-tmp.php';
        \touch( $newFile );
        $this->_provider->setFile( $newFile );
        $this->_provider->write( $config );
        $this->assertSame( <<<PHP
<?php
return [
   'default' => [
      'description' => 'A optional section description…',
      'items' => [
         'foo' => [
            'description' => 'A optional item description…',
            'nullable' => false,
            'type' => 'bool',
            'value' => false,
         ],
         'bar' => [
            'nullable' => true,
            'type' => 'int',
            'value' => 1234,
         ],
         'baz' => [
            'nullable' => true,
            'type' => 'string',
            'value' => null,
         ]
      ]
   ]
];

PHP
            ,
            \file_get_contents( __DIR__ . '/../../../data/config-tmp.php' ) );
        \unlink( $newFile );

    }

    public function test_writeException1()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.php';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        \touch( $configFile );
        \chmod( $configFile, 0400 );
        $config = $this->_provider->read();
        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( $configFile );
        $this->_provider->write( $config );
        \chmod( $configFile, 0700 );
        \unlink( $configFile );

    }


}

