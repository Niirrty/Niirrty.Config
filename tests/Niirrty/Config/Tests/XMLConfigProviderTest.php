<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-31
 * @version        0.1.0
 */


namespace Niirrty\Config\Tests;


use Niirrty\Config\Exceptions\ConfigProviderException;
use Niirrty\Config\Exceptions\ConfigProviderOptionException;
use Niirrty\Config\IConfiguration;
use Niirrty\Config\Provider\XMLConfigProvider;
use Niirrty\IO\Path;
use PHPUnit\Framework\TestCase;


class XMLConfigProviderTest extends TestCase
{


    /** @type XMLConfigProvider */
    private $_provider;


    public function setUp()
    {

        \chmod( __DIR__ . '/../../../data/config.xml', 0700 );
        $this->_provider = XMLConfigProvider::Init( __DIR__ . '/../../../data/config.xml' );

        parent::setUp();

    }

    public function tearDown()
    {

        \chmod( __DIR__ . '/../../../data/config.xml', 0700 );
        parent::tearDown();

    }


    public function test_getName()
    {

        $this->assertSame( 'XML', $this->_provider->getName() );

    }

    public function test_isValid()
    {

        $this->assertTrue( $this->_provider->isValid() );

    }

    public function test_getOptions()
    {

        $this->assertSame(
            [ 'file'       => __DIR__ . '/../../../data/config.xml',
              'extensions' => [ 'xml' ] ],
            $this->_provider->getOptions()
        );

    }

    public function test_getOption()
    {

        $this->assertSame( [ 'xml' ], $this->_provider->getOption( 'extensions' ) );
        $this->assertSame( __DIR__ . '/../../../data/config.xml', $this->_provider->getOption( 'file' ) );
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

        $this->_provider->setOption( 'extensions', [ 'xml', 'xhtml' ] );
        $this->assertSame(
            [ 'file'       => __DIR__ . '/../../../data/config.xml',
              'extensions' => [ 'xml', 'xhtml' ] ],
            $this->_provider->getOptions()
        );
        $this->_provider->setOption( 'foo', ':-)' );

    }

    public function test_setOptionException1()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setOption( 'extensions', [ 'xhtml' ] );

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

        $this->_provider->setFile( __DIR__ . '/../../../data/config-copy.xml' );
        $this->assertTrue( $this->_provider->isValid() );
        $this->assertSame( __DIR__ . '/../../../data/config-copy.xml', $this->_provider->getFile() );

    }

    public function test_setFileException1()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config.php' );

    }

    public function test_setExtensionsException1()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setExtensions( [] );

    }

    public function test_setExtensionsException2()
    {

        $this->expectException( ConfigProviderOptionException::class );
        $this->_provider->setExtensions( [ 'xml', 1 ] );

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
                          'name'        => 'PageName',
                          'description' => 'A config item description…',
                          'nullable'    => false,
                          'type'        => 'string',
                          'value'       => '¿?¿? ¡!¡! - Foo Bar - !¡!¡ ?¿?¿',
                      ],
                      [
                          'name'        => 'Blub',
                          'description' => "A longer description for the configuration item,\nwith a line break, huuhh :-)",
                          'nullable'    => false,
                          'type'        => 'string',
                          'value'       => "This is also a value, but\nwith a new line!",
                      ],
                      [
                          'name'        => 'Blubber',
                          'nullable'    => true,
                          'type'        => 'string',
                          'value'       => null,
                          'description' => null,
                      ],
                      [
                          'name'        => 'Abc',
                          'nullable'    => false,
                          'type'        => 'bool',
                          'value'       => true,
                          'description' => null,
                      ],
                      [
                          'name'        => 'Def',
                          'nullable'    => false,
                          'type'        => 'int',
                          'value'       => -123,
                          'description' => null,
                      ],
                      [
                          'name'        => 'Ghi',
                          'nullable'    => false,
                          'type'        => 'float',
                          'value'       => 12.3,
                          'description' => null,
                      ],
                      [
                          'name'        => 'Jkl',
                          'nullable'    => false,
                          'type'        => 'array',
                          'value'       => [ 'Foo', 'Bar' ],
                          'description' => null,
                      ],
                      [
                          'name'        => 'Mno',
                          'nullable'    => false,
                          'type'        => '\\DateTime',
                          'value'       => '2017-04-14 12:47:25',
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
        $this->assertSame( 8, \count( $config[ 'default' ] ) );

        $configFile = __DIR__ . '/../../../data/config-no-existing.xml';
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
        $this->_provider->setFile( __DIR__ . '/../../../data/config-empty.xml' );
        $this->_provider->read();

    }

    public function test_readException2()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format.xml' );
        $this->_provider->read();

    }

    public function test_readException3()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-data1.xml' );
        $this->_provider->read();

    }

    public function test_readException4()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format2.xml' );
        $this->_provider->read();

    }

    public function test_readException5()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format3.xml' );
        $this->_provider->read();

    }

    public function test_read2()
    {

        $this->_provider->setFile( __DIR__ . '/../../../data/config-no-nullable.xml' );
        $this->assertInstanceOf( IConfiguration::class, $this->_provider->read() );

    }

    public function test_readException6()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format4.xml' );
        $this->_provider->read();

    }

    public function test_readException7()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format5.xml' );
        $this->_provider->read();

    }

    public function test_readException8()
    {

        $this->expectException( ConfigProviderException::class );
        $this->_provider->setFile( __DIR__ . '/../../../data/config-invalid-format6.xml' );
        $this->_provider->read();

    }

    public function test_write()
    {

        $config = $this->_provider->read();
        $newFile = __DIR__ . '/../../../data/config-tmp.xml';
        $this->_provider->setFile( $newFile );
        $this->_provider->write( $config );
        $this->assertSame( <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Config>
  <Section name="default">
    <Description>A optional section description…</Description>
    <Item name="PageName" type="string" nullable="false">
      <Value>¿?¿? ¡!¡! - Foo Bar - !¡!¡ ?¿?¿</Value>
      <Description>A config item description…</Description>
    </Item>
    <Item name="Blub" type="string" nullable="false">
      <Value>This is also a value, but
with a new line!</Value>
      <Description>A longer description for the configuration item,
with a line break, huuhh :-)</Description>
    </Item>
    <Item name="Blubber" type="string" nullable="true">
      <Value/>
    </Item>
    <Item name="Abc" type="bool" nullable="false" value="true"/>
    <Item name="Def" type="int" nullable="false" value="-123"/>
    <Item name="Ghi" type="float" nullable="false" value="12.3"/>
    <Item name="Jkl" type="array" nullable="false">
      <Value>[&quot;Foo&quot;,&quot;Bar&quot;]</Value>
    </Item>
    <Item name="Mno" type="\DateTime" nullable="false" value="2017-04-14 12:47:25"/>
  </Section>
</Config>

XML
            ,
            \file_get_contents( __DIR__ . '/../../../data/config-tmp.xml' ) );
        \unlink( $newFile );

    }

    public function test_writeException1()
    {

        $rootFolder = Path::Combine( \sys_get_temp_dir(), 'Niirty.Config.Tests' );
        if ( !\is_dir( $rootFolder ) )
        {
            \mkdir( $rootFolder );
        }
        $configFile = $rootFolder . '/config.xml';
        if ( @\is_file( $configFile ) && \file_exists( $configFile ) )
        {
            \chmod( $configFile, 0700 );
            \unlink( $configFile );
        }
        $config = $this->_provider->read();
        $this->_provider->setFile( $configFile );
        \touch( $configFile );
        \chmod( $configFile, 0000 );
        $this->expectException( ConfigProviderException::class );
        $this->_provider->write( $config );
        \chmod( $configFile, 0700 );
        \unlink( $configFile );

    }

    public function test_writeException2()
    {

        $config = $this->_provider->read();
        \chmod( $this->_provider->getFile(), 0000 );
        $this->expectException( ConfigProviderException::class );
        $this->_provider->write( $config );

    }


}

