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


class ConfigProviderException extends ConfigException
{


    /**
     * Init a new ConfigurationException instance.
     *
     * @param string          $providerName The configuration provider name
     * @param string|null     $message
     * @param \Throwable|null $previous
     */
    public function __construct( protected string $providerName, ?string $message = null, ?\Throwable $previous = null )
    {

        parent::__construct(
            '"' . $providerName . '" config provider error.' . self::appendMessage( $message ),
            0,
            $previous
        );

    }

    /**
     * Gets the provider name.
     *
     * @return string
     */
    public function getProviderName(): string
    {

        return $this->providerName;

    }


}

