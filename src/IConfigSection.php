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


use \Niirrty\IArrayable;


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
     * @return IConfigSection
     */
    public function setItem( IConfigItem $item ): IConfigSection;

    /**
     * Sets the value of a already defined config item.
     *
     * @param string $name  The name (string) of the config item
     * @param mixed  $value The config value.
     *
     * @return IConfigSection
     */
    public function setValue( string $name, mixed $value ): IConfigSection;

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
    public function getValue( string $name ): mixed;

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
     * @return IConfigSection
     */
    public function setIsChanged( bool $isChanged ): IConfigSection;

    /**
     * Gets if a config item with defined name already exists.
     *
     * @param string $name The name (string) of the config item
     *
     * @return bool
     */
    public function hasItem( string $name ): bool;


}

