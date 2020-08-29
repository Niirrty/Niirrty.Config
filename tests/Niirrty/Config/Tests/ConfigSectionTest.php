<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-29
 * @version        0.1.0
 */


namespace Niirrty\Config\Tests;


use Niirrty\ArgumentException;
use Niirrty\Config\ConfigItem;
use Niirrty\Config\ConfigSection;
use PHPUnit\Framework\TestCase;


class ConfigSectionTest extends TestCase
{


    /** @type ConfigSection */
    private $_sectionEmpty;

    /** @type ConfigSection */
    private $_sectionNotEmpty;


    public function setUp()
    {

        $this->_sectionEmpty = new ConfigSection( 'emptySection', 'The optional empty section description…' );
        $this->_sectionNotEmpty = new ConfigSection( 'notEmptySection', 'The optional not empty section description…' );
        $this->_sectionNotEmpty->setItem(
            ( new ConfigItem( $this->_sectionNotEmpty, 'boolItem', 'A boolean config item…' ) )
                ->setType( 'bool' )
                ->setValue( false ) );
        $this->_sectionNotEmpty->setItem(
            ( new ConfigItem( $this->_sectionNotEmpty, 'stringItem', 'A string config item…' ) )
                ->setType( 'string' )
                ->setValue( 'blubb' ) )->setIsChanged( false );

        parent::setUp();

    }


    public function test_toArray()
    {

        $this->assertSame(
            [
                'name'        => 'notEmptySection',
                'description' => 'The optional not empty section description…',
                'items'       => [
                    [
                        'name'        => 'boolItem',
                        'description' => 'A boolean config item…',
                        'type'        => 'bool',
                        'nullable'    => false,
                        'value'       => false,
                    ],
                    [
                        'name'        => 'stringItem',
                        'description' => 'A string config item…',
                        'type'        => 'string',
                        'nullable'    => false,
                        'value'       => 'blubb',
                    ],
                ],
            ],
            $this->_sectionNotEmpty->toArray()
        );

    }

    public function test_setItem()
    {

        $this->_sectionNotEmpty->setItem(
            ( new ConfigItem( $this->_sectionNotEmpty, 'intItem', 'A integer config item…' ) )
                ->setType( 'int' )
                ->setValue( 444 )
        );
        $this->assertSame( 3, \count( $this->_sectionNotEmpty ) );
        $this->assertTrue( isset( $this->_sectionNotEmpty[ 'intItem' ] ) );
        $this->assertSame( 444, $this->_sectionNotEmpty->getValue( 'intItem' ) );

    }

    public function test_setValue()
    {

        $this->_sectionNotEmpty->setValue( 'stringItem', ':-)' );
        $this->assertSame( ':-)', $this->_sectionNotEmpty->getValue( 'stringItem' ) );

    }

    public function test_setValueException()
    {

        $this->expectException( ArgumentException::class );
        $this->_sectionNotEmpty->setValue( 'foo', ':-)' );

    }

    public function test_getItem()
    {

        $this->assertInstanceOf( ConfigItem::class, $this->_sectionNotEmpty->getItem( 'boolItem' ) );
        $this->assertNull( $this->_sectionNotEmpty->getItem( 'intItem' ) );

    }

    public function test_getValue()
    {

        $this->assertSame( false, $this->_sectionNotEmpty->getValue( 'boolItem' ) );
        $this->assertSame( 'blubb', $this->_sectionNotEmpty->getValue( 'stringItem' ) );
        $this->assertSame( null, $this->_sectionNotEmpty->getValue( 'intItem' ) );

    }

    public function test_isChanged()
    {

        $this->assertFalse( $this->_sectionNotEmpty->isChanged() );
        $this->_sectionNotEmpty->setIsChanged( true );
        $this->assertTrue( $this->_sectionNotEmpty->isChanged() );

    }

    public function test_hasItem()
    {

        $this->assertTrue( $this->_sectionNotEmpty->hasItem( 'stringItem' ) );
        $this->assertFalse( $this->_sectionEmpty->hasItem( 'stringItem' ) );

    }

    public function test_getIterator()
    {

        $this->assertTrue( \is_iterable( $this->_sectionNotEmpty ) );

    }

    public function test_offsetExists()
    {

        $this->assertTrue( isset( $this->_sectionNotEmpty[ 'boolItem' ] ) );
        $this->assertFalse( isset( $this->_sectionNotEmpty[ 'intItem' ] ) );

    }

    public function test_offsetGet()
    {

        $this->assertSame( 'boolItem', $this->_sectionNotEmpty[ 'boolItem' ]->getName() );
    }

    public function test_offsetSet()
    {

        $this->_sectionNotEmpty[] = ( new ConfigItem( $this->_sectionNotEmpty, 'intItem', 'A integer config item…' ) )
            ->setType( 'int' )
            ->setValue( 123 );
        $this->assertSame( 3, \count( $this->_sectionNotEmpty ) );
        $this->assertTrue( isset( $this->_sectionNotEmpty[ 'intItem' ] ) );
        $this->assertSame( 123, $this->_sectionNotEmpty->getValue( 'intItem' ) );
        $this->_sectionNotEmpty[ 'intItem' ] = -123;
        $this->assertSame( -123, $this->_sectionNotEmpty->getValue( 'intItem' ) );

    }

    public function test_offsetSetException1()
    {

        $this->expectException( ArgumentException::class );
        $this->_sectionNotEmpty[] = 'foo bar';

    }

    public function test_offsetSetException2()
    {

        $this->expectException( ArgumentException::class );
        $this->_sectionNotEmpty[ 'abc' ] =
            ( new ConfigItem( $this->_sectionNotEmpty, 'intItem', 'A integer config item…' ) )
                ->setType( 'int' )
                ->setValue( 123 );

    }

    public function test_offsetUnset()
    {

        unset( $this->_sectionNotEmpty[ 'boolItem' ] );
        $this->assertSame( 1, \count( $this->_sectionNotEmpty ) );

    }

    public function test_count()
    {

        $this->assertSame( 0, \count( $this->_sectionEmpty ) );
        $this->assertSame( 2, \count( $this->_sectionNotEmpty ) );

    }


}

