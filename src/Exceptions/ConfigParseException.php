<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Ni Irrty
 * @license        MIT
 * @since          2018-05-26
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Config\Exceptions;


class ConfigParseException extends ConfigProviderException
{


    /**
     * Init a new ConfigParseException instance.
     *
     * @param mixed           $providerName
     * @param string          $message
     * @param \Throwable|null $previous
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

