<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-23
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Config;


use Niirrty\ArgumentException;
use Niirrty\Config\Provider\IConfigProvider;
use Traversable;


/**
 * Represents the configuration.
 *
 * The data are subdivided into sections and their associated items.
 *
 * You can simple access a section with all items by $configurationInstance[ 'sectionName' ]
 * but you can also a items directly by $configurationInstance[ 'sectionName::itemName' ] or also by
 * $configurationInstance[ 'sectionName' ][ 'itemName' ]
 *
 * @package Niirrty\Config
 */
class Configuration implements IConfiguration
{


   /** @type \Niirrty\Config\Provider\IConfigProvider */
   private $_provider;

   /** @type \Niirrty\Config\IConfigSection[] */
   protected $_sections;

   protected $_changed = false;


   /**
    * Configuration constructor.
    *
    * @param \Niirrty\Config\Provider\IConfigProvider $provider
    * @param \Niirrty\Config\IConfigSection[]         $input
    * @throws \Niirrty\ArgumentException
    */
   public function __construct( IConfigProvider $provider, array $input = [] )
   {

      if ( \count( $input ) > 0 )
      {
         $checkedInput = [];
         foreach ( $input as $section )
         {
            if ( ! ( $section instanceof IConfigSection ) )
            {
               throw new ArgumentException(
                  'input',
                  $input,
                  'Invalid initial array item. Instance of "' . IConfigSection::class . '" required!'
               );
            }
            $checkedInput[ $section->getName() ] = $section;
         }
         $input = $checkedInput;
      }

      $this->_sections = $input;
      $this->_provider = $provider;

   }


   /**
    * Returns all instance data as an associative array.
    *
    * @return array
    */
   public function toArray() : array
   {

      $out = [];

      foreach ( $this as $section )
      {
         $out[] = $section->toArray();
      }

      return $out;

   }

   /**
    * Gets the associated provider.
    *
    * @return \Niirrty\Config\Provider\IConfigProvider
    */
   public function getProvider() : IConfigProvider
   {

      return $this->_provider;

   }

   /**
    * Sets the configuration item.
    *
    * @param \Niirrty\Config\IConfigItem $item
    * @return \Niirrty\Config\Configuration
    * @throws \Niirrty\ArgumentException If the item not define a parent section
    */
   public function setItem( IConfigItem $item )
   {

      if ( null === $item->getParent() || '' === $item->getParent()->getName() )
      {
         throw new ArgumentException(
            'item',
            $item,
            'Can not set the config item if the item not define a parent section!'
         );
      }

      if ( ! isset( $this->_sections[ $item->getParent()->getName() ] ) )
      {
         $this->_sections[ $item->getParent()->getName() ] = new ConfigSection(
            $item->getParent()->getName(),
            $item->getParent()->getDescription()
         );
      }

      $this->_sections[ $item->getParent()->getName() ]->setItem( $item );
      $this->_changed = true;

      return $this;

   }

   /**
    * Sets the configuration value.
    *
    * @param string $sectionName The section name or ID.
    * @param string $itemName    The item name or ID.
    * @param mixed  $value
    * @return \Niirrty\Config\Configuration
    * @throws \Niirrty\ArgumentException
    */
   public function setValue( string $sectionName, string $itemName, $value )
   {

      if ( ! $this->hasSection( $sectionName ) )
      {
         throw new ArgumentException(
            'sectionName',
            $sectionName,
            'Can not set value for item "' . $itemName . '" if the owning section not exists!'
         );
      }

      if ( ! $this->hasItem( $sectionName, $itemName ) )
      {
         throw new ArgumentException(
            'itemName',
            $itemName,
            'Can not set value for section "' . $sectionName . '" if the item not exists!'
         );
      }

      $this->getItem( $sectionName, $itemName )->setValue( $value );
      $this->_changed = true;

      return $this;

   }

   /**
    * Sets the config item by key and value, and optionally $nullable and $type.
    *
    * @param IConfigSection $section  The section.
    * @return \Niirrty\Config\Configuration
    */
   public function setSection( IConfigSection $section )
   {

      $this->_sections[ $section->getName() ] = $section;

      return $this;

   }

   /**
    * Gets the config item for defined defined section and item name, or null if not found.
    *
    * @param string $sectionName
    * @return \Niirrty\Config\IConfigSection|null
    */
   public function getSection( string $sectionName ) : ?IConfigSection
   {

      return ! isset( $this->_sections[ $sectionName ] )
         ? null
         : $this->_sections[ $sectionName ];

   }

   /**
    * Gets the config item for defined defined section and item name, or null if not found.
    *
    * @param string $sectionName
    * @param string $itemName
    * @return \Niirrty\Config\IConfigItem|null
    */
   public function getItem( string $sectionName, string $itemName ) : ?IConfigItem
   {

      if ( ! isset( $this->_sections[ $sectionName ] ) )
      {
         return null;
      }

      return ! isset( $this->_sections[ $sectionName ][ $itemName ] )
         ? null
         : $this->_sections[ $sectionName ][ $itemName ];

   }

   /**
    * Gets the value of the config item with defined section and item name.
    *
    * @param string $sectionName
    * @param string $itemName
    * @return mixed
    */
   public function getValue( string $sectionName, string $itemName )
   {

      return ( null === ( $item = $this->getItem( $sectionName, $itemName ) ) )
         ? null
         : $item->getValue();

   }

   /**
    * Gets if some of the items/sections are changed.
    *
    * @return bool
    */
   public function isChanged() : bool
   {

      if ( $this->_changed )
      {
         return true;
      }

      foreach ( $this->_sections as $section )
      {
         if ( $section->isChanged() )
         {
            return true;
         }
      }

      return false;

   }

   /**
    * Sets if some of the items is changed.
    *
    * @param bool $isChanged
    * @return mixed
    */
   public function setIsChanged( bool $isChanged )
   {

      $this->_changed = $isChanged;

      if ( ! $isChanged )
      {

         foreach ( $this->_sections as $section )
         {
            $section->setIsChanged( $isChanged );
         }

      }

      return $this;

   }

   /**
    * Gets if a config item with defined section and item name already exists.
    *
    * @param string $sectionName
    * @param string $itemName
    * @return bool
    */
   public function hasItem( string $sectionName, string $itemName ) : bool
   {

      return isset( $this->_sections[ $sectionName ] ) && isset( $this->_sections[ $sectionName ][ $itemName ] );

   }

   /**
    * Gets if a config section with defined section name already exists.
    *
    * @param string $sectionName
    * @return bool
    */
   public function hasSection( string $sectionName ) : bool
   {

      return isset( $this->_sections[ $sectionName ] );

   }


   /**
    * Retrieve an external iterator
    *
    * @return Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
    */
   public function getIterator()
   {

      return new \ArrayIterator( $this->_sections );

   }

   /**
    * Whether a offset exists
    *
    * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
    * @param mixed $offset An offset to check for.
    * @return bool true on success or false on failure.
    *              The return value will be casted to boolean if non-boolean was returned.
    */
   public function offsetExists( $offset )
   {

      list( $sectionName, $itemName ) = $this->expandOffset( $offset );

      if ( null !== $itemName )
      {
         return isset( $this->_sections[ $sectionName ] ) &&
                isset( $this->_sections[ $sectionName ][ $itemName ] );
      }

      return isset( $this->_sections[ $sectionName ] );

   }

   /**
    * Offset to retrieve
    *
    * @link  http://php.net/manual/en/arrayaccess.offsetget.php
    * @param mixed $offset The offset to retrieve.
    * @return \Niirrty\Config\IConfigSection|null
    */
   public function offsetGet( $offset )
   {

      list( $sectionName, $itemName ) = $this->expandOffset( $offset );

      if ( null !== $itemName )
      {
         return isset( $this->_sections[ $sectionName ] ) &&
                isset( $this->_sections[ $sectionName ][ $itemName ] )
            ? $this->_sections[ $sectionName ][ $itemName ]
            : null;
      }

      return isset( $this->_sections[ $sectionName ] )
         ? $this->_sections[ $sectionName ]
         : null;

   }

   /**
    * Offset to set
    *
    * @link  http://php.net/manual/en/arrayaccess.offsetset.php
    * @param string|null $offset The offset to assign the value to.
    * @param \Niirrty\Config\IConfigSection $value
    * @throws \Niirrty\ArgumentException
    */
   public function offsetSet( $offset, $value )
   {

      if ( ! ( $value instanceof IConfigSection ) && ! ( $value instanceof IConfigItem ) )
      {
         // A none IConfigItem value should be set, we need a usable offset.
         if ( null === $offset )
         {
            throw new ArgumentException(
               'offset',
               $offset,
               'Can not set a config item/section if no item/section name is defined!'
            );
         }
         list( $sectionName, $itemName ) = $this->expandOffset( $offset );
         if ( null === $itemName )
         {
            throw new ArgumentException(
               'offset',
               $offset,
               'Can not set a config item/section if no item name is defined! (Please use format "SectionName::ItemName")'
            );
         }

         $this->setValue( $sectionName, $itemName, $value );

         return;

      }

      if ( $value instanceof IConfigSection )
      {
         if ( null !== $offset && $offset !== $value->getName() )
         {
            throw new ArgumentException(
               'offset',
               $offset,
               'Can not assign a config section "'
                  . $value->getName()
                  . '" with a different name "'
                  . $offset
                  . '" to a configuration!'
            );
         }
         $this->_sections[ $value->getName() ] = $value;
         return;
      }

      // Its a config item…

      $sectionName = $value->getParent()->getName();
      $identifier  = $sectionName . '::' . $value->getName();

      if ( null !== $offset && $offset !== $identifier )
      {
         throw new ArgumentException(
            'offset',
            $offset,
            'Can not assign a config item "'
               . $identifier
               . '" with a different name "'
               . $offset
               . '" to section "' . $sectionName . '"!'
         );
      }

      if ( ! isset( $this->_sections[ $sectionName ] ) )
      {
         $this->_sections[ $sectionName ] = new ConfigSection( $sectionName, $value->getParent()->getDescription() );
      }

      $this->_sections[ $sectionName ][] = $value;

   }

   /**
    * Offset to unset
    *
    * @param mixed $offset The offset to unset.
    */
   public function offsetUnset( $offset )
   {

      list( $sectionName, $itemName ) = $this->expandOffset( $offset );

      if ( null !== $itemName )
      {
         unset( $this->_sections[ $sectionName ][ $itemName ] );
      }
      else
      {
         unset( $this->_sections[ $sectionName ] );
      }

   }

   /**
    * Count elements of an object
    *
    * @return int The custom count as an integer. The return value is cast to an integer.
    */
   public function count()
   {

      return \count( $this->_sections );

   }


   protected function expandOffset( string $offset )
   {

      $parts = \explode( '::', $offset, 2 );

      if ( ! isset( $parts[ 1 ] ) )
      {
         $parts[ 1 ] = null;
      }

      return $parts;

   }


}
