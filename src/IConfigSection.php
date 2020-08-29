<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Ni Irrty
 * @license        MIT
 * @since          2018-05-20
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Config;


use Niirrty\IArrayable;


/**
 * Specifies a configuration section with all associated config value items
 *
 * @package Niirrty\Config
 */
interface IConfigSection
    extends IConfigElementBase, IArrayable, \ArrayAccess, \IteratorAggregate, \Countable
{


    /**
     * Sets the configuration item.
     *
     * @param IConfigItem $item
     *
     * @return mixed
     */
    public function setItem( IConfigItem $item );

    /**
     * Sets the value of a already defined config item.
     *
     * @param string $name  The name (string) of the config item
     * @param mixed  $value The config value.
     *
     * @return mixed
     */
    public function setValue( string $name, $value );

    /**
     * Gets the config item for defined key, or null if the key is not defined.
     *
     * @param string $name The name (string) of the config item
     *
     * @return IConfigItem|null
     */
    public function getItem( string $name ): ?IConfigItem;

    /**
     * Gets the value of the config item with defined key.
     *
     * @param string $name The name (string) of the config item
     *
     * @return mixed
     */
    public function getValue( string $name );

    /**
     * Gets if some of the items is changed.
     *
     * @return bool
     */
    public function isChanged(): bool;

    /**
     * Sets if some of the items is changed.
     *
     * @param bool $isChanged
     *
     * @return mixed
     */
    public function setIsChanged( bool $isChanged );

    /**
     * Gets if a config item with defined name already exists.
     *
     * @param string $name The name (string) of the config item
     *
     * @return bool
     */
    public function hasItem( string $name ): bool;


}

