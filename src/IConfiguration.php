<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-05-19
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Config;


use \Niirrty\ArgumentException;
use \Niirrty\Config\Provider\IConfigProvider;
use \Niirrty\IToArray;


interface IConfiguration extends IToArray, \ArrayAccess, \IteratorAggregate, \Countable
{


    /**
     * Gets the associated provider.
     *
     * @return IConfigProvider
     */
    public function getProvider(): IConfigProvider;

    /**
     * Sets the configuration item.
     *
     * @param IConfigItem $item
     *
     * @return IConfiguration
     * @throws ArgumentException If the item not define a parent section
     */
    public function setItem( IConfigItem $item ): self;

    /**
     * Sets the config section.
     *
     * @param IConfigSection $section The section.
     *
     * @return IConfiguration
     */
    public function setSection( IConfigSection $section ): self;

    /**
     * Sets the configuration value.
     *
     * @param string $sectionName The section name or ID.
     * @param string $itemName    The item name or ID.
     * @param mixed  $value
     *
     * @return IConfiguration
     * @throws ArgumentException
     */
    public function setValue( string $sectionName, string $itemName, mixed $value ): self;

    /**
     * Gets the config item for defined defined section and item name, or null if not found.
     *
     * @param string $sectionName
     * @param string $itemName
     *
     * @return IConfigItem|null
     */
    public function getItem( string $sectionName, string $itemName ): ?IConfigItem;

    /**
     * Gets the config section for defined section name, or null if not found.
     *
     * @param string $sectionName
     *
     * @return IConfigSection|null
     */
    public function getSection( string $sectionName ): ?IConfigSection;

    /**
     * Gets the value of the config item with defined section and item name.
     *
     * @param string $sectionName
     * @param string $itemName
     *
     * @return mixed
     */
    public function getValue( string $sectionName, string $itemName ): mixed;

    /**
     * Gets if some of the items/sections are changed.
     *
     * @return bool
     */
    public function isChanged(): bool;

    /**
     * Sets if some of the items is changed.
     *
     * @param bool $isChanged
     *
     * @return IConfiguration
     */
    public function setIsChanged( bool $isChanged ): self;

    /**
     * Gets if a config item with defined section and item name already exists.
     *
     * @param string $sectionName
     * @param string $itemName
     *
     * @return bool
     */
    public function hasItem( string $sectionName, string $itemName ): bool;

    /**
     * Gets if a config section with defined section name already exists.
     *
     * @param string $sectionName
     *
     * @return bool
     */
    public function hasSection( string $sectionName ): bool;


}

