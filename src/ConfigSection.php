<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-05-20
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Config;


use \Niirrty\ArgumentException;
use \Traversable;


/**
 * A configuration section with all associated items. Usable like a array/iterable.
 *
 * @package Niirrty\Config
 */
class ConfigSection implements IConfigSection
{


    // Defines ->getId(), ->getName() and ->getDescription() and the associated protected fields.
    use ConfigElementTrait;


    protected bool $_changed = false;

    /** @type IConfigItem[] */
    protected array $_items;


    /**
     * ConfigSection constructor.
     *
     * @param string      $name        The unique section name.
     * @param null|string $description Optional section description
     */
    public function __construct( string $name, ?string $description = null )
    {

        $this->_items = [];
        $this->_name = $name;
        $this->_description = $description;

    }


    /**
     * Returns all instance data as an associative array.
     *
     * Returned array contains the keys 'name' (string), description (?string), items (array)
     *
     * @return array
     */
    public function toArray(): array
    {

        $out = [
            'name'        => $this->_name,
            'description' => $this->_description,
            'items'       => [],
        ];

        foreach ( $this->_items as $item )
        {
            $out[ 'items' ][] = $item->toArray();
        }

        return $out;

    }

    /**
     * Sets the configuration item.
     *
     * @param IConfigItem $item
     *
     * @return ConfigSection
     */
    public function setItem( IConfigItem $item ) : ConfigSection
    {

        $item->setParent( $this );

        $this[ $item->getName() ] = $item;
        $this->_changed = true;

        return $this;

    }

    /**
     * Sets the value of a already defined config item.
     *
     * @param string $name  The name (string) of the config item
     * @param mixed  $value The config value.
     *
     * @return ConfigSection
     * @throws ArgumentException If $nameOrId is unknown
     */
    public function setValue( string $name, mixed $value ) : ConfigSection
    {

        if ( !isset( $this[ $name ] ) )
        {
            throw new ArgumentException(
                'name',
                $name,
                "There is no config item with defined value within section '{$this->_name}'!"
            );
        }

        $this[ $name ]->setValue( $value );

        return $this;

    }

    /**
     * Gets the config item for defined key, or null if the key is not defined.
     *
     * @param string $name The name (string) of the config item
     *
     * @return ConfigItem|null
     */
    public function getItem( string $name ): ?ConfigItem
    {

        return isset( $this[ $name ] ) ? $this[ $name ] : null;

    }

    /**
     * Gets the value of the config item with defined key.
     *
     * @param string $name The name (string) of the config item
     *
     * @return mixed
     */
    public function getValue( string $name ) : mixed
    {

        return isset( $this[ $name ] ) ? $this[ $name ]->getValue() : null;

    }

    /**
     * Gets if some of the items is changed.
     *
     * @return bool
     */
    public function isChanged(): bool
    {

        if ( $this->_changed )
        {
            return true;
        }

        foreach ( $this as $item )
        {
            if ( $item->isChanged() )
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
     *
     * @return ConfigSection
     */
    public function setIsChanged( bool $isChanged ) : ConfigSection
    {

        $this->_changed = $isChanged;

        if ( !$isChanged )
        {

            foreach ( $this as $item )
            {
                $item->setIsChanged( $isChanged );
            }

        }

        return $this;

    }

    /**
     * Gets if a config item with defined name already exists.
     *
     * @param string $name The name (string) of the config item
     *
     * @return bool
     */
    public function hasItem( string $name ): bool
    {

        return $this->offsetExists( $name );

    }


    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|\ArrayIterator An instance of an object implementing Iterator or Traversable
     * @since 5.0.0
     */
    public function getIterator(): Traversable|\ArrayIterator
    {

        return new \ArrayIterator( $this->_items );

    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return bool true on success or false on failure.
     *              The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists( mixed $offset ): bool
    {

        return isset( $this->_items[ $offset ] );

    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return IConfigItem|null
     */
    public function offsetGet( mixed $offset ): mixed 
    {

        return $this->offsetExists( $offset ) ? $this->_items[ $offset ] : null;

    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param string|null $offset The offset to assign the value to.
     * @param IConfigItem $value
     *
     * @throws ArgumentException
     */
    public function offsetSet( mixed $offset, mixed $value ) : void
    {

        if ( !( $value instanceof IConfigItem ) )
        {
            // A none IConfigItem value should be set, we need a usable offset.
            if ( null === $offset )
            {
                throw new ArgumentException(
                    'offset',
                    $offset,
                    'Can not set a config value if no item is defined!'
                );
            }
            $this->setValue( $offset, $value );

            return;
        }

        if ( null !== $offset && $offset !== $value->getName() )
        {
            throw new ArgumentException(
                'offset',
                $offset,
                'Can not assign a config item "'
                . $value->getName()
                . '" with a different name "'
                . $offset
                . '" to a section!'
            );
        }

        $this->_items[ $value->getName() ] = $value;

    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset( mixed $offset ) : void
    {

        unset( $this->_items[ $offset ] );

    }

    /**
     * Count elements of an object
     *
     * @return int The custom count as an integer. The return value is cast to an integer.
     */
    public function count(): int
    {

        return \count( $this->_items );

    }


}

