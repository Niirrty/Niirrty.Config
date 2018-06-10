<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-26
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Config\Exceptions;



class ConfigProviderException extends ConfigException
{


   /** @type string The configuration provider name */
   protected $_providerName;


   /**
    * Init a new ConfigurationException instance.
    */
   public function __construct( string $providerName, ?string $message = null, ?\Throwable $previous = null )
   {

      parent::__construct(
         '"' . $providerName . '"Config provider error.' . self::appendMessage( $message ),
         0,
         $previous
      );

      $this->_providerName = $providerName;

   }

   /**
    * Gets the provider name.
    *
    * @return string
    */
   public function getProviderName() : string
   {

      return $this->_providerName;

   }


}

