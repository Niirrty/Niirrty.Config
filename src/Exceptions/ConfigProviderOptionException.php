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


class ConfigProviderOptionException extends ConfigProviderException
{


    /**
     * ConfigProviderOptionException constructor.
     *
     * @param string          $providerName
     * @param string          $optionName   The name of the option
     * @param null|string     $message
     * @param null|\Throwable $previous
     */
    public function __construct(
        string $providerName, protected string $optionName, ?string $message = null, ?\Throwable $previous = null )
    {

        parent::__construct(
            $providerName,
            'Set a new value for option "' . $optionName . '" fails.' . self::appendMessage( $message ),
            $previous
        );

    }

    public function getOptionName() : string
    {

        return $this->optionName;

    }


}

