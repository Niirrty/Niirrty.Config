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


class ConfigProviderOptionException extends ConfigProviderException
{


   /** @type string The name of the option */
   protected $_optionName;


   /**
    * ConfigProviderOptionException constructor.
    *
    * @param string          $providerName
    * @param string          $optionName
    * @param null|string     $message
    * @param null|\Throwable $previous
    */
   public function __construct(
      string $providerName, string $optionName, ?string $message = null, ?\Throwable $previous = null )
   {

      parent::__construct(
         $providerName,
         'Set a new value for option "' . $optionName . '" fails.' . self::appendMessage( $message ),
         $previous
      );

      $this->_optionName = $optionName;

   }


}

