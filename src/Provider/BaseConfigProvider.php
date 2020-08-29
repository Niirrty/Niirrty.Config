<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Ni Irrty
 * @license        MIT
 * @since          2018-05-26
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Config\Provider;


use Niirrty\Config\Exceptions\ConfigProviderOptionException;


/**
 * Abstract base implementation of {@see \Niirrty\Config\Provider\IConfigProvider}
 *
 * @package Niirrty\Config\Provider
 */
abstract class BaseConfigProvider
{


    /** @type string The provider name */
    protected $_name;

    /** @type array The provider depending options */
    protected $_options = [];

    /** @type bool Holds the state if the provider is valid */
    protected $_valid;


    /**
     * Gets the provider name.
     *
     * @return string
     */
    public final function getName(): string
    {

        return $this->_name;

    }

    /**
     * Gets if the provider is valid and ready for use.
     *
     * @return bool
     */
    public function isValid(): bool
    {

        return $this->_valid;

    }

    /**
     * Gets all defined provider options as associative array.
     *
     * @return array
     */
    public function getOptions(): array
    {

        return $this->_options;

    }

    /**
     * Gets the names of all current defined options.
     *
     * @return array
     */
    public function getOptionNames(): array
    {

        return \array_keys( $this->_options );

    }

    /**
     * Gets the value of the defined option.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption( string $name )
    {

        return $this->_options[ $name ] ?? null;

    }

    /**
     * Sets a specific option
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     * @throws ConfigProviderOptionException
     */
    public function setOption( string $name, $value )
    {

        $methodName = 'set' . \ucfirst( $name );
        if ( \method_exists( $this, $methodName ) )
        {
            $this->{$methodName}( $value );

            return $this;
        }

        $this->validateOption( $name, $value );

        $this->_options[ $name ] = $value;

        return $this;

    }

    /**
     * Gets if a options with defined name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOption( string $name ): bool
    {

        return \array_key_exists( $name, $this->_options );

    }

    /**
     * Validates the defined option and throws a exception on error.
     *
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     *
     * @throws ConfigProviderOptionException If a wrong option is defined.
     */
    protected abstract function validateOption( string $name, $value );


}

