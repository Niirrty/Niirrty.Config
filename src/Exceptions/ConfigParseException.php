<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-05-26
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Config\Exceptions;


class ConfigParseException extends ConfigProviderException
{


    /**
     * Init a new ConfigParseException instance.
     *
     * @param string          $providerName
     * @param string          $message
     * @param \Throwable|null $previous
     */
    public function __construct( string $providerName, string $message, ?\Throwable $previous = null )
    {

        parent::__construct(
            $providerName,
            'Configuration parse error.' . self::appendMessage( $message ),
            $previous
        );

    }


}

