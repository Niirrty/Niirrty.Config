<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-05-26
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Config\Provider;


use Niirrty\Config\Exceptions\ConfigProviderOptionException;


/**
 * Abstract base implementation of {@see IConfigProvider}
 *
 * @package Niirrty\Config\Provider
 */
abstract class BaseConfigProvider implements IConfigProvider
{


    #region // - - -   P R O T E C T E D   F I E L D S   - - - - - - - - - - - - - - - - - - - - - - -

    /** @type bool Holds the state if the provider is valid */
    protected bool $_valid;

    #endregion


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * BaseConfigProvider constructor.
     *
     * @param string $name    The provider name
     * @param array  $options The provider depending options
     */
    protected function __construct( protected string $name = '', protected array $options = [] )
    {
        $this->_valid = false;
    }

    #endregion


    /**
     * Gets the provider name.
     *
     * @return string
     */
    public final function getName(): string
    {

        return $this->name;

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

        return $this->options;

    }

    /**
     * Gets the names of all current defined options.
     *
     * @return array
     */
    public function getOptionNames(): array
    {

        return \array_keys( $this->options );

    }

    /**
     * Gets the value of the defined option.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption( string $name ): mixed
    {

        return $this->options[ $name ] ?? null;

    }

    /**
     * Sets a specific option
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     * @throws ConfigProviderOptionException
     */
    public function setOption( string $name, mixed $value ): self
    {

        $methodName = 'set' . \ucfirst( $name );
        if ( \method_exists( $this, $methodName ) )
        {
            $this->{$methodName}( $value );

            return $this;
        }

        $this->validateOption( $name, $value );

        $this->options[ $name ] = $value;

        return $this;

    }

    /**
     * Gets if option with defined name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOption( string $name ): bool
    {

        return \array_key_exists( $name, $this->options );

    }

    /**
     * Validates the defined option and throws a exception on error.
     *
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     *
     * @throws ConfigProviderOptionException If a wrong option is defined.
     */
    protected abstract function validateOption( string $name, mixed $value ) : void;


}

