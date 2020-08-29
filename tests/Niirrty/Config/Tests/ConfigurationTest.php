<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-30
 * @version        0.1.0
 */


namespace Niirrty\Config\Tests;


use Niirrty\ArgumentException;
use Niirrty\Config\ConfigItem;
use Niirrty\Config\ConfigSection;
use Niirrty\Config\Configuration;
use Niirrty\Config\IConfiguration;
use Niirrty\Config\Provider\IConfigProvider;
use Niirrty\Date\DateTime;
use PHPUnit\Framework\TestCase;


class ConfigurationTest extends TestCase
{


    /** @type Configuration */
    private $_config;

    public function setUp()
    {

        $section1 = new ConfigSection( 'default' );
        $section1->setItem( ( new ConfigItem( $section1, 'foo' ) )->setType( '\\DateTime' )
                                                                  ->setValue( '2016-06-24 13:12:00' )
                                                                  ->setIsChanged( false ) );
        $section1->setItem( ( new ConfigItem( $section1, 'bar' ) )->setType( 'int' )
                                                                  ->setValue( 114 )
                                                                  ->setIsChanged( false ) );
        $section2 = new ConfigSection( 'other' );
        $section2->setItem( ( new ConfigItem( $section2, 'foo' ) )->setType( '\\DateTime' )
                                                                  ->setValue( '2006-04-02 10:35:00' )
                                                                  ->setIsChanged( false ) );
        $section2->setItem( ( new ConfigItem( $section2, 'bar' ) )->setType( 'float' )
                                                                  ->setValue( 1.15 )
                                                                  ->setIsChanged( false ) );

        $this->_config = new Configuration(
            new class implements IConfigProvider
            {


                public function getName(): string { return 'dummy'; }

                public function isValid(): bool { return true; }

                public function getOptions(): array { return []; }

                public function getOptionNames(): array { return []; }

                public function setOption( string $name, $value ) { return $this; }

                public function hasOption( string $name ): bool { return false; }

                public function getOption( string $name ): bool { return null; }

                public function read(
                    ?array $sectionNames = null ): IConfiguration
                {
                    return new Configuration( $this );
                }

                public function write( IConfiguration $config ) { return $this; }


            }
        );

        $this->_config[] = $section1;
        $this->_config[] = $section2;

        $this->_config->setIsChanged( false );

        parent::setUp();

    }

    public function test_construct()
    {

        $config = new Configuration(
            $this->_config->getProvider(),
            [
                new ConfigSection( 'abcd' ),
            ]
        );

        $this->assertInstanceOf( IConfiguration::class, $config );

    }

    public function test_constructException()
    {

        $this->expectException( ArgumentException::class );

        new Configuration(
            $this->_config->getProvider(),
            [
                'abcd' => 20,
            ]
        );

    }

    public function test_getIterator()
    {

        $this->assertTrue( \is_iterable( $this->_config ) );

    }

    public function test_offsetExists()
    {

        $this->assertTrue( isset( $this->_config[ 'default' ] ) );
        $this->assertTrue( isset( $this->_config[ 'default::foo' ] ) );
        $this->assertTrue( isset( $this->_config[ 'default::bar' ] ) );
        $this->assertFalse( isset( $this->_config[ 'default::baz' ] ) );
        $this->assertTrue( isset( $this->_config[ 'other::foo' ] ) );
        $this->assertTrue( isset( $this->_config[ 'other::bar' ] ) );
        $this->assertFalse( isset( $this->_config[ 'other::baz' ] ) );
        $this->assertFalse( isset( $this->_config[ 'xyz' ] ) );

    }

    public function test_offsetGet()
    {

        $this->assertSame( 'default', $this->_config[ 'default' ]->getName() );
        $this->assertSame( 'foo', $this->_config[ 'default::foo' ]->getName() );
        $this->assertNull( $this->_config[ 'default::blub' ] );

    }

    public function test_offsetSet()
    {

        $section = new ConfigSection( 'new' );
        $section->setItem( ( new ConfigItem( $section, 'abc' ) )->setType( 'string' )
                                                                ->setValue( ':-)' )
                                                                ->setIsChanged( false ) );
        $this->_config[] = $section;
        $this->assertSame( 3, \count( $this->_config ) );
        $this->assertTrue( isset( $this->_config[ 'new' ] ) );
        $this->assertTrue( isset( $this->_config[ 'new::abc' ] ) );
        $this->assertFalse( isset( $this->_config[ 'old' ] ) );
        $this->assertFalse( isset( $this->_config[ 'old::abc' ] ) );
        $this->_config[ 'default::bar' ] = 110;
        $this->assertSame( 110, $this->_config[ 'default::bar' ]->getValue() );
        $this->_config[ 'default::sdf' ] = ConfigItem::Create( $this->_config[ 'default' ], 'sdf', 'bool', false );
        $this->assertSame( false, $this->_config[ 'default::sdf' ]->getValue() );
        $this->_config[ 'lsmf::sdf' ] = ConfigItem::Create( new ConfigSection( 'lsmf' ), 'sdf', 'bool', true );
        $this->assertSame( true, $this->_config[ 'lsmf::sdf' ]->getValue() );

    }

    public function test_offsetSetException1()
    {

        $this->expectException( ArgumentException::class );
        $this->_config[] = 'foo bar';

    }

    public function test_offsetSetException2()
    {

        $this->expectException( ArgumentException::class );
        $this->_config[ 'abc' ] = 14;

    }

    public function test_offsetSetException3()
    {

        $this->expectException( ArgumentException::class );
        $this->_config[ 'abcde' ] = new ConfigSection( 'xxyyzz' );

    }

    public function test_offsetSetException4()
    {

        $this->expectException( ArgumentException::class );
        $this->_config[ 'default::fff' ] = ConfigItem::Create( $this->_config[ 'default' ], 'sdf', 'bool', false );

    }

    public function test_offsetUnset()
    {

        unset( $this->_config[ 'new' ] );
        $this->assertFalse( isset( $this->_config[ 'new' ] ) );
        unset( $this->_config[ 'default::bar' ] );
        $this->assertFalse( isset( $this->_config[ 'default::bar' ] ) );
        $this->assertTrue( isset( $this->_config[ 'default' ] ) );

    }

    public function test_count()
    {

        $this->assertSame( 2, \count( $this->_config ) );

    }

    public function test_toArray()
    {

        $this->assertEquals(
            [
                [
                    'name'        => 'default',
                    'description' => null,
                    'items'       => [
                        [
                            'name'        => 'foo',
                            'description' => null,
                            'type'        => '\\DateTime',
                            'nullable'    => false,
                            'value'       => DateTime::Parse( '2016-06-24 13:12:00' ),
                        ],
                        [
                            'name'        => 'bar',
                            'description' => null,
                            'type'        => 'int',
                            'nullable'    => false,
                            'value'       => 114,
                        ],
                    ],
                ],
                [
                    'name'        => 'other',
                    'description' => null,
                    'items'       => [
                        [
                            'name'        => 'foo',
                            'description' => null,
                            'type'        => '\\DateTime',
                            'nullable'    => false,
                            'value'       => DateTime::Parse( '2006-04-02 10:35:00' ),
                        ],
                        [
                            'name'        => 'bar',
                            'description' => null,
                            'type'        => 'float',
                            'nullable'    => false,
                            'value'       => 1.15,
                        ],
                    ],
                ],
            ],
            $this->_config->toArray()
        );

    }

    public function test_getProvider()
    {

        $this->assertInstanceOf( IConfigProvider::class, $this->_config->getProvider() );

    }

    public function test_setItem()
    {

        $this->assertSame(
            -555,
            $this->_config->setItem(
                ConfigItem::Create( $this->_config[ 'default' ], 'abc', 'int', -555 )
            )->getValue( 'default', 'abc' )
        );

    }

    public function test_setItemException1()
    {

        $this->expectException( ArgumentException::class );

        $this->_config->setItem(
            ConfigItem::Create( new ConfigSection( '' ), 'abc', 'int', -555 )
        );

    }

    public function test_setItem2()
    {

        $this->_config->setItem(
            ConfigItem::Create( new ConfigSection( 'abcd' ), 'abc', 'int', 14 )
        );

        $this->assertSame(
            565,
            $this->_config->setItem(
                ConfigItem::Create( new ConfigSection( 'abcd' ), 'abc', 'int', 565 )
            )->getValue( 'abcd', 'abc' )
        );

    }

    public function test_setValue()
    {

        $this->assertSame( 112, $this->_config->setValue( 'default', 'bar', 112 )->getValue( 'default', 'bar' ) );

    }

    public function test_setValueException1()
    {

        $this->expectException( ArgumentException::class );
        $this->_config->setValue( 'foo', 'bar', ':-)' );

    }

    public function test_setValueException2()
    {

        $this->expectException( ArgumentException::class );
        $this->_config->setValue( 'default', 'blubb', ':-)' );

    }

    public function test_setSection()
    {

        $this->assertFalse( isset( $this->_config[ 'oOo' ] ) );
        $this->_config->setSection( new ConfigSection( 'oOo' ) );
        $this->assertTrue( isset( $this->_config[ 'oOo' ] ) );

    }

    public function test_getSection()
    {

        $this->assertSame( 'default', $this->_config->getSection( 'default' )->getName() );
        $this->assertNull( $this->_config->getSection( 'blubb' ) );

    }

    public function test_getItem()
    {

        $this->assertSame( 'foo', $this->_config->getItem( 'default', 'foo' )->getName() );
        $this->assertNull( $this->_config->getItem( 'default', 'abc' ) );
        $this->assertNull( $this->_config->getItem( 'xyz', 'abc' ) );

    }

    public function test_getValue()
    {

        $this->assertSame( '2016-06-24 13:12:00',
                           $this->_config->getValue( 'default', 'foo' )->format( 'Y-m-d H:i:s' ) );
        $this->assertSame( 114,
                           $this->_config->getValue( 'default', 'bar' ) );
        $this->assertSame( '2006-04-02 10:35:00',
                           $this->_config->getValue( 'other', 'foo' )->format( 'Y-m-d H:i:s' ) );
        $this->assertSame( 1.15,
                           $this->_config->getValue( 'other', 'bar' ) );
        $this->assertNull( $this->_config->getValue( 'default', 'abc' ) );
        $this->assertNull( $this->_config->getValue( 'xyz', 'abc' ) );

    }

    public function test_isChanged()
    {

        $this->assertFalse( $this->_config->isChanged() );
        $this->_config[ 'default::bar' ] = 77;
        $this->assertTrue( $this->_config->isChanged() );

    }

    public function test_setIsChanged()
    {

        $this->assertFalse( $this->_config->isChanged() );
        $this->_config->setIsChanged( true );
        $this->assertTrue( $this->_config->isChanged() );

    }

    public function test_hasItem()
    {

        $this->assertTrue( $this->_config->hasItem( 'default', 'foo' ) );
        $this->assertTrue( $this->_config->hasItem( 'default', 'bar' ) );
        $this->assertTrue( $this->_config->hasItem( 'other', 'foo' ) );
        $this->assertTrue( $this->_config->hasItem( 'other', 'bar' ) );
        $this->assertFalse( $this->_config->hasItem( 'default', 'yxz' ) );
        $this->assertFalse( $this->_config->hasItem( 'abc', 'bar' ) );

    }

    public function test_hasSection()
    {

        $this->assertTrue( $this->_config->hasSection( 'default' ) );
        $this->assertTrue( $this->_config->hasSection( 'other' ) );
        $this->assertFalse( $this->_config->hasSection( 'abc' ) );

    }


}

