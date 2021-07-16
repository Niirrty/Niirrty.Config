<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-05-20
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Config;


use \Niirrty\{ArgumentException, IArrayable, Type, TypeTool};
use \Niirrty\Date\DateTime;


class ConfigItem implements IConfigItem
{


    /**
     * Implements ->getId(), ->getName() and ->getDescription()
     */
    use ConfigElementTrait;


    /** @type bool */
    protected bool $_nullable;

    /** @type bool */
    protected bool $_changed;

    /** @type string */
    protected string $_type;

    /** @type mixed */
    protected mixed $_value;


    /**
     * ConfigItem constructor.
     *
     * @param IConfigElementBase $parent      The item owning section
     * @param string             $name        The item name.
     * @param null|string        $description Optional item description
     */
    public function __construct(
        protected IConfigElementBase $parent, string $name, ?string $description = null )
    {

        $this->_name = $name;
        $this->_description = \trim( $description ?? '' );
        $this->_nullable = false;
        $this->_value = null;

        if ( '' === $this->_description )
        {
            $this->_description = null;
        }

    }


    /**
     * Returns all instance data as an associative array.
     *
     * The keys of the returned array are:
     *
     * name, description, type, nullable, value
     *
     * @return array
     */
    public function toArray(): array
    {

        return [
            'name'        => $this->_name,
            'description' => $this->_description,
            'type'        => $this->_type,
            'nullable'    => $this->_nullable,
            'value'       => $this->_value,
        ];

    }

    /**
     * Gets the type name of the config item.
     *
     * @return string
     */
    public function getType(): string
    {

        return $this->_type;

    }

    /**
     * Gets if the config value is nullable.
     *
     * @return bool
     */
    public function isNullable(): bool
    {

        return $this->_nullable;

    }

    /**
     * Gets the config value as string, or NULL.
     *
     * @return null|string
     */
    public function getStringValue(): ?string
    {

        if ( null === $this->_value )
        {
            return $this->_value;
        }

        return match ( $this->_type )
        {
            'bool', 'boolean' => $this->_value ? 'true' : 'false',
            'array'           => \json_encode( $this->_value ),
            '\\DateTime', '\\DateTimeInterface', 'DateTime', 'DateTimeInterface', 'Niirrty\\Date\\DateTime', '\\Niirrty\\Date\\DateTime'
                              => $this->_value->format( 'Y-m-d H:i:s' ),
            default           => (string) $this->_value,
        };

    }

    /**
     * Gets the config value as int, or NULL if the value is null or not convertible to a integer.
     *
     * @return int|null
     */
    public function getIntValue(): ?int
    {

        try
        {
            return TypeTool::ConvertNative( $this->_value, Type::PHP_INTEGER );
        }
        catch ( \Throwable )
        {
            return null;
        }

    }

    /**
     * Gets the config value as bool, or NULL if the value is null or not convertible to a boolean.
     *
     * @return bool|null
     */
    public function getBoolValue(): ?bool
    {

        try
        {
            return TypeTool::ConvertNative( $this->_value, Type::PHP_BOOLEAN );
        }
        catch ( \Throwable )
        {
            return null;
        }

    }

    /**
     * Gets the config value as float, or NULL if the value is null or not convertible to a float.
     *
     * @return float|null
     */
    public function getFloatValue(): ?float
    {

        try
        {
            return TypeTool::ConvertNative( $this->_value, Type::PHP_FLOAT );
        }
        catch ( \Throwable )
        {
            return null;
        }

    }

    /**
     * Gets the value with mixed type.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {

        return $this->_value;

    }

    /**
     * Gets the parent section element.
     *
     * @return IConfigElementBase
     */
    public function getParent(): IConfigElementBase
    {

        return $this->parent;

    }

    /**
     * Sets the parent section element.
     *
     * @param IConfigElementBase $parentSection
     *
     * @return ConfigItem
     */
    public function setParent( IConfigElementBase $parentSection ): ConfigItem
    {

        $this->parent = $parentSection;

        return $this;

    }

    /**
     * Sets a new value.
     *
     * @param mixed $value
     *
     * @return ConfigItem
     * @throws ArgumentException|\Throwable if the config value is invalid
     */
    public function setValue( mixed $value ) : ConfigItem
    {

        if ( $this->_value === $value )
        {
            return $this;
        }

        $this->_changed = true;

        if ( null === $value )
        {

            if ( !$this->_nullable )
            {
                throw new ArgumentException(
                    'value',
                    $value,
                    "Null is not a supported value for config item '{$this->_name}'!"
                );
            }

            $this->_value = $value;

            return $this;

        }

        switch ( $this->_type )
        {

            case Type::PHP_STRING:
                if ( !TypeTool::IsStringConvertible( $value, $strOut ) )
                {
                    throw new ArgumentException(
                        'value',
                        $value,
                        "The new value for config item '{$this->_name}' is not convertible to a string!"
                    );
                }
                $this->_value = $strOut;

                return $this;

            case Type::PHP_BOOLEAN:
            case 'boolean':
                if ( !TypeTool::IsBoolConvertible( $value, $boolOut ) )
                {
                    throw new ArgumentException(
                        'value',
                        $value,
                        "The new value for config item '{$this->_name}' is not convertible to a boolean!"
                    );
                }
                $this->_value = $boolOut;

                return $this;

            case Type::PHP_ARRAY:
                if ( \is_array( $value ) )
                {
                    $this->_value = $value;

                    return $this;
                }
                if ( \is_iterable( $value ) )
                {
                    $this->_value = \iterator_to_array( $value );

                    return $this;
                }
                if ( $value instanceof IArrayable )
                {
                    $this->_value = $value->toArray();

                    return $this;
                }
                if ( \is_string( $value ) )
                {
                    $val = @\json_decode( $value, true );
                    if ( \is_array( $val ) )
                    {
                        $this->_value = $val;

                        return $this;
                    }
                    $val = @\unserialize( $value );
                    if ( \is_array( $val ) )
                    {
                        $this->_value = $val;

                        return $this;
                    }
                }
                throw new ArgumentException(
                    'value',
                    $value,
                    "The new value for config item '{$this->_name}' is not convertible to a array!"
                );

            case Type::PHP_INTEGER:
            case 'integer':
                if ( !TypeTool::IsInteger( $value ) )
                {
                    throw new ArgumentException(
                        'value',
                        $value,
                        "The new value for config item '{$this->_name}' is not convertible to a integer!"
                    );
                }
                $this->_value = (int) $value;

                return $this;

            case Type::PHP_FLOAT:
            case 'double':
                if ( !TypeTool::IsDecimal( $value, true ) )
                {
                    throw new ArgumentException(
                        'value',
                        $value,
                        "The new value for config item '{$this->_name}' is not convertible to a integer!"
                    );
                }
                $this->_value = (float) \str_replace( ',', '.', (string) $value );

                return $this;

            case 'DateTime':
            case 'DateTimeInterface':
            case '\\DateTimeInterface':
            case 'Niirrty\\DateTime':
            case '\\Niirrty\\DateTime':
            case '\\DateTime':
                if ( false === ( $dt = DateTime::Parse( $value ) ) )
                {
                    throw new ArgumentException(
                        'value',
                        $value,
                        "The new value for config item '{$this->_name}' is not convertible to a date time!"
                    );
                }
                $this->_value = $dt;

                return $this;

            default:
                $this->_value = $value;

                return $this;

        }

    }

    /**
     * Gets if the value is marked as changed.
     *
     * @return bool
     */
    public function isChanged(): bool
    {

        return $this->_changed;

    }

    /**
     * Sets the state if the value is marked as changed.
     *
     * @param bool $changed
     *
     * @return ConfigItem
     */
    public function setIsChanged( bool $changed ): ConfigItem
    {

        $this->_changed = $changed;

        return $this;

    }

    /**
     * Sets if the item is nullable.
     *
     * @param bool $nullable
     *
     * @return ConfigItem
     */
    public function setIsNullable( bool $nullable ) : ConfigItem
    {

        $this->_nullable = $nullable;
        $this->_changed = true;

        return $this;

    }

    /**
     * Sets the accepted item value type.
     *
     * @param string $typeName
     *
     * @return IConfigItem
     */
    public function setType( string $typeName ) : ConfigItem
    {

        $this->_type = $typeName;
        $this->_changed = true;

        return $this;

    }

    /**
     * Gets the string representation of the instance data for implementing class.
     *
     * @return string
     */
    public function __toString()
    {

        return $this->getStringValue() ?? '';

    }

    public function __clone()
    {

        $this->_value = clone $this->_value;

    }


    /**
     * Creates a new config item for defined section and register it inside the section.
     *
     * @param IConfigSection                     $parentSection
     * @param string                             $name
     * @param string                             $type
     * @param                                    $value
     * @param bool                               $nullable
     *
     * @return IConfigItem
     * @throws ArgumentException
     * @throws \Throwable
     */
    public static function Create(
        IConfigSection $parentSection, string $name, string $type, $value, bool $nullable = false ): ConfigItem
    {

        $item = ( new ConfigItem( $parentSection, $name ) )
            ->setType( $type )
            ->setIsNullable( $nullable )
            ->setValue( $value )
            ->setIsChanged( false );

        $parentSection->setItem( $item );

        return $item;

    }


}

