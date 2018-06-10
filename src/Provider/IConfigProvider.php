<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-19
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Config\Provider;


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
   public function getName() : string;

   /**
    * Gets if the provider is valid and ready for use.
    *
    * @return bool
    */
   public function isValid() : bool;

   /**
    * Gets all defined provider options as associative array.
    *
    * @return array
    */
   public function getOptions() : array;

   /**
    * Gets the value of the defined option.
    *
    * @return mixed
    */
   public function getOption( string $name );

   /**
    * Gets the names of all current defined options.
    *
    * @return array
    */
   public function getOptionNames() : array;

   /**
    * Sets a specific option
    *
    * @param string $name
    * @param mixed  $value
    * @return mixed
    * @throws \Niirrty\Config\Exceptions\ConfigProviderOptionException
    */
   public function setOption( string $name, $value );

   /**
    * Gets if a options with defined name exists.
    *
    * @param string $name
    * @return bool
    */
   public function hasOption( string $name ) : bool;

   /**
    * Reads all available configuration items from the source.
    *
    * @param string[]|null $sectionNames
    * @return \Niirrty\Config\IConfiguration
    * @throws \Niirrty\Config\Exceptions\ConfigParseException
    * @throws \Niirrty\Config\Exceptions\ConfigProviderException
    */
   public function read( ?array $sectionNames = null ) : IConfiguration;

   /**
    * Writes the config to the source.
    *
    * @param \Niirrty\Config\IConfiguration $config
    * @return mixed
    * @throws \Niirrty\Config\Exceptions\ConfigProviderException
    */
   public function write( IConfiguration $config );


}

