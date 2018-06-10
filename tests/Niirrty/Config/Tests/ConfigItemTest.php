<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-27
 * @version        0.1.0
 */


namespace Niirrty\Config\Tests;


use Niirrty\ArgumentException;
use Niirrty\Config\ConfigItem;
use Niirrty\Config\ConfigSection;
use Niirrty\Date\DateTime;
use Niirrty\Date\Time;
use Niirrty\IArrayable;
use PHPUnit\Framework\TestCase;


class ConfigItemTest extends TestCase
{


   /** @type \Niirrty\Config\ConfigItem */
   private $_itemBool;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemBoolNullable;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemInt;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemIntNullable;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemFloat;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemFloatNullable;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemString;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemStringNullable;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemArray;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemArrayNullable;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemDateTime;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemDateTimeNullable;
   /** @type \Niirrty\Config\ConfigItem */
   private $_itemOther;


   public function setUp()
   {

      $section = new ConfigSection( 'default' );

      $this->_itemBool = ( new ConfigItem( $section, 'boolItem', 'A item description…' ) )
         ->setIsNullable( false ) ->setType( 'bool' ) ->setValue( true ) ->setIsChanged( false );

      $this->_itemBoolNullable = ( new ConfigItem( $section, 'boolItemNullable' ) )
         ->setIsNullable( true ) ->setType( 'bool' ) ->setValue( false ) ->setIsChanged( false );

      $this->_itemInt = ( new ConfigItem( $section, 'intItem' ) )
         ->setIsNullable( false ) ->setType( 'int' ) ->setValue( 12 ) ->setIsChanged( false );

      $this->_itemIntNullable = ( new ConfigItem( $section, 'intItemNullable' ) )
         ->setIsNullable( true ) ->setType( 'int' ) ->setValue( -1234 ) ->setIsChanged( false );

      $this->_itemFloat = ( new ConfigItem( $section, 'floatItem' ) )
         ->setIsNullable( false ) ->setType( 'float' ) ->setValue( 12.34 ) ->setIsChanged( false );

      $this->_itemFloatNullable = ( new ConfigItem( $section, 'floatItemNullable' ) )
         ->setIsNullable( true ) ->setType( 'float' ) ->setValue( .1234 ) ->setIsChanged( false );

      $this->_itemString = ( new ConfigItem( $section, 'stringItem' ) )
         ->setIsNullable( false ) ->setType( 'string' ) ->setValue( 'a string…' ) ->setIsChanged( false );

      $this->_itemStringNullable = ( new ConfigItem( $section, 'stringItemNullable' ) )
         ->setIsNullable( true ) ->setType( 'string' ) ->setValue( '.1234' ) ->setIsChanged( false );

      $this->_itemArray = ( new ConfigItem( $section, 'arrayItem' ) )
         ->setIsNullable( false ) ->setType( 'array' ) ->setValue( [ 'Foo', 'Bar', 'Baz' ] ) ->setIsChanged( false );

      $this->_itemArrayNullable = ( new ConfigItem( $section, 'arrayItemNullable' ) )
         ->setIsNullable( true ) ->setType( 'array' ) ->setValue( null ) ->setIsChanged( false );

      $this->_itemDateTime = ( new ConfigItem( $section, 'DateTimeItem' ) )
         ->setIsNullable( false ) ->setType( '\\DateTime' ) ->setValue( '2018-02-14 00:00:00' ) ->setIsChanged( false );

      $this->_itemDateTimeNullable = ( new ConfigItem( $section, 'DateTimeItemNullable' ) )
         ->setIsNullable( true ) ->setType( '\\DateTime' ) ->setValue( new \DateTime( '2017-12-12 12:00:00' ) )
                                                           ->setIsChanged( false );

      $this->_itemOther = ( new ConfigItem( $section, 'OtherItem' ) )
         ->setIsNullable( false ) ->setType( '\\Niirrty\Date\\Time' )
                                  ->setValue( Time::Parse( '01:22:00' ) )
                                  ->setIsChanged( false );

      parent::setUp();

   }

   public function test_toArray()
   {

      $this->assertSame(
         [ 'name'        => 'boolItem',
           'description' => 'A item description…',
           'type'        => 'bool',
           'nullable'    => false,
           'value'       => true ],
         $this->_itemBool->toArray() );
      $this->assertSame(
         [ 'name'        => 'boolItemNullable',
           'description' => null,
           'type'        => 'bool',
           'nullable'    => true,
           'value'       => false ],
         $this->_itemBoolNullable->toArray() );
      $this->assertSame(
         [ 'name'        => 'intItem',
           'description' => null,
           'type'        => 'int',
           'nullable'    => false,
           'value'       => 12 ],
         $this->_itemInt->toArray() );
      $this->assertSame(
         [ 'name'        => 'intItemNullable',
           'description' => null,
           'type'        => 'int',
           'nullable'    => true,
           'value'       => -1234 ],
         $this->_itemIntNullable->toArray() );
      $this->assertSame(
         [ 'name'        => 'floatItem',
           'description' => null,
           'type'        => 'float',
           'nullable'    => false,
           'value'       => 12.34 ],
         $this->_itemFloat->toArray() );
      $this->assertSame(
         [ 'name'        => 'floatItemNullable',
           'description' => null,
           'type'        => 'float',
           'nullable'    => true,
           'value'       => .1234 ],
         $this->_itemFloatNullable->toArray() );
      $this->assertSame(
         [ 'name'        => 'stringItem',
           'description' => null,
           'type'        => 'string',
           'nullable'    => false,
           'value'       => 'a string…' ],
         $this->_itemString->toArray() );
      $this->assertSame(
         [ 'name'        => 'stringItemNullable',
           'description' => null,
           'type'        => 'string',
           'nullable'    => true,
           'value'       => '.1234' ],
         $this->_itemStringNullable->toArray() );
      $this->assertSame(
         [ 'name'        => 'arrayItem',
           'description' => null,
           'type'        => 'array',
           'nullable'    => false,
           'value'       => [ 'Foo', 'Bar', 'Baz' ] ],
         $this->_itemArray->toArray() );
      $this->assertSame(
         [ 'name'        => 'arrayItemNullable',
           'description' => null,
           'type'        => 'array',
           'nullable'    => true,
           'value'       => null ],
         $this->_itemArrayNullable->toArray() );
      $this->assertEquals(
         [ 'name'        => 'DateTimeItem',
           'description' => null,
           'type'        => '\\DateTime',
           'nullable'    => false,
           'value'       => DateTime::Parse( '2018-02-14 00:00:00' ) ],
         $this->_itemDateTime->toArray() );
      $this->assertEquals(
         [ 'name'        => 'DateTimeItemNullable',
           'description' => null,
           'type'        => '\\DateTime',
           'nullable'    => true,
           'value'       => DateTime::Parse( '2017-12-12 12:00:00' ) ],
         $this->_itemDateTimeNullable->toArray() );

   }

   public function test_getType()
   {

      $this->assertSame( 'bool', $this->_itemBool->getType() );
      $this->assertSame( 'bool', $this->_itemBoolNullable->getType() );
      $this->assertSame( 'int', $this->_itemInt->getType() );
      $this->assertSame( 'int', $this->_itemIntNullable->getType() );
      $this->assertSame( 'float', $this->_itemFloat->getType() );
      $this->assertSame( 'float', $this->_itemFloatNullable->getType() );
      $this->assertSame( 'string', $this->_itemString->getType() );
      $this->assertSame( 'string', $this->_itemStringNullable->getType() );
      $this->assertSame( 'array', $this->_itemArray->getType() );
      $this->assertSame( 'array', $this->_itemArrayNullable->getType() );
      $this->assertSame( '\\DateTime', $this->_itemDateTime->getType() );
      $this->assertSame( '\\DateTime', $this->_itemDateTimeNullable->getType() );

   }

   public function test_isNullable()
   {

      $this->assertFalse( $this->_itemBool->isNullable() );
      $this->assertTrue( $this->_itemBoolNullable->isNullable() );
      $this->assertFalse( $this->_itemInt->isNullable() );
      $this->assertTrue( $this->_itemIntNullable->isNullable() );
      $this->assertFalse( $this->_itemFloat->isNullable() );
      $this->assertTrue( $this->_itemFloatNullable->isNullable() );
      $this->assertFalse( $this->_itemString->isNullable() );
      $this->assertTrue( $this->_itemStringNullable->isNullable() );
      $this->assertFalse( $this->_itemArray->isNullable() );
      $this->assertTrue( $this->_itemArrayNullable->isNullable() );
      $this->assertFalse( $this->_itemDateTime->isNullable() );
      $this->assertTrue( $this->_itemDateTimeNullable->isNullable() );

   }

   public function test_getStringValue()
   {

      $this->assertSame( 'true', $this->_itemBool->getStringValue() );
      $this->assertSame( 'false', $this->_itemBoolNullable->getStringValue() );
      $this->assertSame( '12', $this->_itemInt->getStringValue() );
      $this->assertSame( '-1234', $this->_itemIntNullable->getStringValue() );
      $this->assertSame( '12.34', $this->_itemFloat->getStringValue() );
      $this->assertSame( '0.1234', $this->_itemFloatNullable->getStringValue() );
      $this->assertSame( 'a string…', $this->_itemString->getStringValue() );
      $this->assertSame( '.1234', $this->_itemStringNullable->getStringValue() );
      $this->assertSame( \json_encode( [ 'Foo', 'Bar', 'Baz' ] ), $this->_itemArray->getStringValue() );
      $this->assertSame( null, $this->_itemArrayNullable->getStringValue() );
      $this->assertSame( '2018-02-14 00:00:00', $this->_itemDateTime->getStringValue() );
      $this->assertSame( '2017-12-12 12:00:00', $this->_itemDateTimeNullable->getStringValue() );

   }

   public function test_getIntValue()
   {

      $this->assertSame( 1, $this->_itemBool->getIntValue() );
      $this->assertSame( 0, $this->_itemBoolNullable->getIntValue() );
      $this->assertSame( 12, $this->_itemInt->getIntValue() );
      $this->assertSame( -1234, $this->_itemIntNullable->getIntValue() );
      $this->assertSame( 12, $this->_itemFloat->getIntValue() );
      $this->assertSame( 0, $this->_itemFloatNullable->getIntValue() );
      $this->assertSame( null, $this->_itemString->getIntValue() );
      $this->assertSame( 0, $this->_itemStringNullable->getIntValue() );
      $this->assertSame( null, $this->_itemArray->getIntValue() );
      $this->assertSame( null, $this->_itemArrayNullable->getIntValue() );
      $this->assertSame( null, $this->_itemDateTime->getIntValue() );
      $this->assertSame( null, $this->_itemDateTimeNullable->getIntValue() );

   }

   public function test_getBoolValue()
   {

      $this->assertSame( true, $this->_itemBool->getBoolValue() );
      $this->assertSame( false, $this->_itemBoolNullable->getBoolValue() );
      $this->assertSame( true, $this->_itemInt->getBoolValue() );
      $this->assertSame( false, $this->_itemIntNullable->getBoolValue() );
      $this->assertSame( true, $this->_itemFloat->getBoolValue() );
      $this->assertSame( true, $this->_itemFloatNullable->getBoolValue() );
      $this->assertSame( false, $this->_itemString->getBoolValue() );
      $this->assertSame( true, $this->_itemStringNullable->getBoolValue() );
      $this->assertSame( null, $this->_itemArray->getBoolValue() );
      $this->assertSame( null, $this->_itemArrayNullable->getBoolValue() );
      $this->assertSame( null, $this->_itemDateTime->getBoolValue() );
      $this->assertSame( null, $this->_itemDateTimeNullable->getBoolValue() );

   }

   public function test_getFloatValue()
   {

      $this->assertSame( 1.0, $this->_itemBool->getFloatValue() );
      $this->assertSame( 0.0, $this->_itemBoolNullable->getFloatValue() );
      $this->assertSame( 12.0, $this->_itemInt->getFloatValue() );
      $this->assertSame( -1234.0, $this->_itemIntNullable->getFloatValue() );
      $this->assertSame( 12.34, $this->_itemFloat->getFloatValue() );
      $this->assertSame( 0.1234, $this->_itemFloatNullable->getFloatValue() );
      $this->assertSame( null, $this->_itemString->getFloatValue() );
      $this->assertSame( 0.1234, $this->_itemStringNullable->getFloatValue() );
      $this->assertSame( null, $this->_itemArray->getFloatValue() );
      $this->assertSame( null, $this->_itemArrayNullable->getFloatValue() );
      $this->assertSame( null, $this->_itemDateTime->getFloatValue() );
      $this->assertSame( null, $this->_itemDateTimeNullable->getFloatValue() );

   }

   public function test_getValue()
   {

      $this->assertSame( true, $this->_itemBool->getValue() );
      $this->assertSame( false, $this->_itemBoolNullable->getValue() );
      $this->assertSame( 12, $this->_itemInt->getValue() );
      $this->assertSame( -1234, $this->_itemIntNullable->getValue() );
      $this->assertSame( 12.34, $this->_itemFloat->getValue() );
      $this->assertSame( 0.1234, $this->_itemFloatNullable->getValue() );
      $this->assertSame( 'a string…', $this->_itemString->getValue() );
      $this->assertSame( '.1234', $this->_itemStringNullable->getValue() );
      $this->assertSame( [ 'Foo', 'Bar', 'Baz' ], $this->_itemArray->getValue() );
      $this->assertSame( null, $this->_itemArrayNullable->getValue() );
      $this->assertEquals( DateTime::Parse( '2018-02-14 00:00:00' ), $this->_itemDateTime->getValue() );
      $this->assertEquals( DateTime::Parse( '2017-12-12 12:00:00' ), $this->_itemDateTimeNullable->getValue() );

   }

   public function test_getParent()
   {

      $this->assertSame( 'default', $this->_itemBool->getParent()->getName() );

   }

   public function test_setParent()
   {

      $this->_itemBool->setParent( new ConfigSection( 'Foo' ) );

      $this->assertSame( 'Foo', $this->_itemBool->getParent()->getName() );

   }

   public function test_setValue()
   {

      $this->_itemBool->setValue( false );
      $this->assertSame( false, $this->_itemBool->getValue() );
      $this->_itemBool->setValue( 'true' );
      $this->assertSame( true, $this->_itemBool->getValue() );
      $this->_itemBool->setValue( 'false' );
      $this->assertSame( false, $this->_itemBool->getValue() );
      $this->_itemBool->setValue( 1 );
      $this->assertSame( true, $this->_itemBool->getValue() );
      $this->_itemBool->setValue( -1.5 );
      $this->assertSame( false, $this->_itemBool->getValue() );
      $this->_itemBool->setValue( 'yes' );
      $this->assertSame( true, $this->_itemBool->getValue() );
      $this->_itemBool->setValue( 'no' );
      $this->assertSame( false, $this->_itemBool->getValue() );
      $this->_itemBoolNullable->setValue( 'enabled' );
      $this->assertSame( true, $this->_itemBoolNullable->getValue() );
      $this->_itemBoolNullable->setValue( null );
      $this->assertSame( null, $this->_itemBoolNullable->getValue() );
      $this->_itemArray->setValue( new \ArrayIterator( [ 'foo', 'bar' ] ) );
      $this->assertSame( [ 'foo', 'bar' ], $this->_itemArray->getValue() );
      $this->_itemArray->setValue(
         new class implements IArrayable
         {
            public function toArray() : array { return [ 1, 3 ]; }
         }
      );
      $this->assertSame( [ 1, 3 ], $this->_itemArray->getValue() );
      $this->_itemArray->setValue(
         \json_encode( [ 'a' => 12, 'x y z' => 0 ] )
      );
      $this->assertSame( [ 'a' => 12, 'x y z' => 0 ], $this->_itemArray->getValue() );
      $this->_itemArray->setValue(
         \serialize( [ 0 ] )
      );
      $this->assertSame( [ 0 ], $this->_itemArray->getValue() );
      $this->_itemOther->setValue( Time::Create( 15, 27, 39 ) );
      $this->assertSame( '15:27:39', $this->_itemOther->getStringValue() );

   }

   public function test_setValueException1()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemBool->setValue( null );

   }

   public function test_setValueException2()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemString->setValue( \fopen( 'data://text/plain,#', 'r' ) );

   }

   public function test_setValueException3()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemBool->setValue( new \stdClass() );

   }

   public function test_setValueException4()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemArray->setValue( new \stdClass() );

   }

   public function test_setValueException5()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemArray->setValue( '$$$$' );

   }

   public function test_setValueException6()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemInt->setValue( '$$$$' );

   }

   public function test_setValueException7()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemFloat->setValue( '$$$$' );

   }

   public function test_setValueException8()
   {

      $this->expectException( ArgumentException::class );
      $this->_itemDateTime->setValue( '$$$$' );

   }

   public function test_isChanged()
   {

      $this->assertFalse( $this->_itemInt->isChanged() );
      $this->_itemInt->setValue( 0 );
      $this->assertTrue( $this->_itemInt->isChanged() );

   }

   public function test_setIsChanged()
   {

      $this->_itemInt->setValue( 12345 );
      $this->_itemInt->setIsChanged( false );
      $this->assertFalse( $this->_itemInt->isChanged() );

   }

   public function test_setIsNullable()
   {

      $this->assertFalse( $this->_itemFloat->isNullable() );
      $this->_itemFloat->setIsNullable( true );
      $this->assertTrue( $this->_itemFloat->isNullable() );

   }

   public function test_setType()
   {

      $this->assertSame( 'bool', $this->_itemString->setType( 'bool' )->getType() );

   }

   public function test_toString()
   {

      $this->assertSame( 'true', (string) $this->_itemBool );
      $this->assertSame( 'false', (string) $this->_itemBoolNullable );
      $this->assertSame( '12', (string) $this->_itemInt );
      $this->assertSame( '-1234', (string) $this->_itemIntNullable );
      $this->assertSame( '12.34', (string) $this->_itemFloat );
      $this->assertSame( '0.1234', (string) $this->_itemFloatNullable );
      $this->assertSame( 'a string…', (string) $this->_itemString );
      $this->assertSame( '.1234', $this->_itemStringNullable->__toString() );
      $this->assertSame( \json_encode( [ 'Foo', 'Bar', 'Baz' ] ), (string) $this->_itemArray );
      $this->assertSame( '', (string) $this->_itemArrayNullable );
      $this->assertSame( '2018-02-14 00:00:00', (string) $this->_itemDateTime );
      $this->assertSame( '2017-12-12 12:00:00', (string) $this->_itemDateTimeNullable );

   }

   public function test_clone()
   {

      $this->assertNotSame( $this->_itemDateTime, clone $this->_itemDateTime );
      $this->assertEquals( $this->_itemDateTime, clone $this->_itemDateTime );

   }

   public function test_getName()
   {

      $this->assertSame( 'boolItem', $this->_itemBool->getName() );
      $this->assertSame( 'boolItemNullable', $this->_itemBoolNullable->getName() );
      $this->assertSame( 'intItem', $this->_itemInt->getName() );
      $this->assertSame( 'intItemNullable', $this->_itemIntNullable->getName() );
      $this->assertSame( 'floatItem', $this->_itemFloat->getName() );
      $this->assertSame( 'floatItemNullable', $this->_itemFloatNullable->getName() );
      $this->assertSame( 'stringItem', $this->_itemString->getName() );
      $this->assertSame( 'stringItemNullable', $this->_itemStringNullable->getName() );
      $this->assertSame( 'arrayItem', $this->_itemArray->getName() );
      $this->assertSame( 'arrayItemNullable', $this->_itemArrayNullable->getName() );
      $this->assertSame( 'DateTimeItem', $this->_itemDateTime->getName() );
      $this->assertSame( 'DateTimeItemNullable', $this->_itemDateTimeNullable->getName() );

   }

   public function test_getDescription()
   {

      $this->assertSame( 'A item description…', $this->_itemBool->getDescription() );
      $this->assertSame( null, $this->_itemBoolNullable->getDescription() );

   }


}
