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


class ConfigParseException extends ConfigProviderException
{


   /**
    * Init a new ConfigParseException instance.
    */
   public function __construct( $providerName, string $message, ?\Throwable $previous = null )
   {

      parent::__construct(
         $providerName,
         'Configuration parse error.' . self::appendMessage( $message ),
         $previous
      );

   }


}

