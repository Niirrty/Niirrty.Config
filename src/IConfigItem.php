<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-19
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Config;


use Niirrty\IArrayable;
use Niirrty\IStringable;


/**
 * Each configuration item must implement this interface.
 *
 * @package Niirrty\Config
 */
interface IConfigItem extends IConfigElementBase, IStringable, IArrayable
{


   /**
    * Gets the type name of the config item.
    *
    * @return string
    */
   public function getType() : string;

   /**
    * Gets if the config value is nullable.
    *
    * @return bool
    */
   public function isNullable() : bool;

   /**
    * Gets the config value as string, or NULL.
    *
    * @return null|string
    */
   public function getStringValue() : ?string;

   #public function getSection() : ?string;

   /**
    * Gets the config value as int, or NULL if the value is null or not convertible to a integer.
    *
    * @return int|null
    */
   public function getIntValue() : ?int;

   /**
    * Gets the config value as bool, or NULL if the value is null or not convertible to a boolean.
    *
    * @return bool|null
    */
   public function getBoolValue() : ?bool;

   /**
    * Gets the config value as float, or NULL if the value is null or not convertible to a float.
    *
    * @return float|null
    */
   public function getFloatValue() : ?float;

   /**
    * Gets the value with mixed type.
    *
    * @return mixed
    */
   public function getValue();

   /**
    * Gets the parent section element.
    *
    * @return \Niirrty\Config\IConfigElementBase
    */
   public function getParent() : IConfigElementBase;

   /**
    * Sets the parent section element.
    *
    * @param \Niirrty\Config\IConfigElementBase $parentSection
    * @return mixed
    */
   public function setParent( IConfigElementBase $parentSection );

   /**
    * Sets a new value.
    *
    * @param mixed $value
    * @return \Niirrty\Config\IConfigItem
    * @throws \Niirrty\ArgumentException if the config value is invalid
    */
   public function setValue( $value );

   /**
    * Sets if the item is nullable.
    *
    * @param bool $nullable
    * @return \Niirrty\Config\IConfigItem
    */
   public function setIsNullable( bool $nullable );

   /**
    * Sets the accepted item value type.
    *
    * @param string $typeName
    * @return \Niirrty\Config\IConfigItem
    */
   public function setType( string $typeName );

   /**
    * Gets if the value is marked as changed.
    *
    * @return bool
    */
   public function isChanged() : bool;

   /**
    * Sets the state if the value is marked as changed.
    *
    * @param bool $changed
    * @return \Niirrty\Config\IConfigItem
    */
   public function setIsChanged( bool $changed );

}

