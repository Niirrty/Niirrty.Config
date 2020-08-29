<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Ni Irrty
 * @license        MIT
 * @since          2018-05-19
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Config\Provider;


use Niirrty\Config\Exceptions\ConfigParseException;
use Niirrty\Config\Exceptions\ConfigProviderException;
use Niirrty\Config\Exceptions\ConfigProviderOptionException;
use Niirrty\Config\IConfiguration;


/**
 * Each configuration provider must implement this interface
 *
 * A configuration provider is used to serve access of configurations from different configuration providers
 *
 * @package Niirrty\Config\Provider
 */
interface IConfigProvider
{


    /**
     * Gets the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets if the provider is valid and ready for use.
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Gets all defined provider options as associative array.
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Gets the value of the defined option.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption( string $name );

    /**
     * Gets the names of all current defined options.
     *
     * @return array
     */
    public function getOptionNames(): array;

    /**
     * Sets a specific option
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     * @throws ConfigProviderOptionException
     */
    public function setOption( string $name, $value );

    /**
     * Gets if a options with defined name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOption( string $name ): bool;

    /**
     * Reads all available configuration items from the source.
     *
     * @param string[]|null $sectionNames
     *
     * @return IConfiguration
     * @throws ConfigParseException
     * @throws ConfigProviderException
     */
    public function read( ?array $sectionNames = null ): IConfiguration;

    /**
     * Writes the config to the source.
     *
     * @param IConfiguration $config
     *
     * @return mixed
     * @throws ConfigProviderException
     */
    public function write( IConfiguration $config );


}

