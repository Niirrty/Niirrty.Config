<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Ni Irrty
 * @license        MIT
 * @since          2018-05-20
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Config;


use Niirrty\ArgumentException;
use Niirrty\Date\DateTime;
use Niirrty\IArrayable;
use Niirrty\Type;
use Niirrty\TypeTool;


class ConfigItem implements IConfigItem
{


    /**
     * Implements ->getId(), ->getName() and ->getDescription()
     */
    use ConfigElementTrait;


    /** @type bool */
    protected $_nullable;

    /** @type bool */
    protected $_changed;

    /** @type string */
    protected $_type;

    /** @type mixed */
    protected $_value;

    /** @type IConfigElementBase */
    protected $_parent;


    /**
     * ConfigItem constructor.
     *
     * @param IConfigElementBase $parent      The item owning section
     * @param string             $name        The item name.
     * @param null|string        $description Optional item description
     */
    public function __construct(
        IConfigElementBase $parent, string $name, ?string $description = null )
    {

        $this->_name = $name;
        $this->_description = \trim( $description ?? '' );
        $this->_nullable = false;
        $this->_parent = $parent;

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

        switch ( $this->_type )
        {

            case 'bool':
            case 'boolean':
                return $this->_value ? 'true' : 'false';

            case 'array':
                return \json_encode( $this->_value );

            case '\\DateTime':
            case '\\DateTimeInterface':
            case 'DateTime':
            case 'DateTimeInterface':
            case 'Niirrty\\Date\\DateTime':
            case '\\Niirrty\\Date\\DateTime':
                return $this->_value->format( 'Y-m-d H:i:s' );

            default:
                return (string) $this->_value;

        }

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
        catch ( \Throwable $ex )
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
        catch ( \Throwable $ex )
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
        catch ( \Throwable $ex )
        {
            return null;
        }

    }

    /**
     * Gets the value with mixed type.
     *
     * @return mixed
     */
    public function getValue()
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

        return $this->_parent;

    }

    /**
     * Sets the parent section element.
     *
     * @param IConfigElementBase $parentSection
     *
     * @return $this
     */
    public function setParent( IConfigElementBase $parentSection )
    {

        $this->_parent = $parentSection;

        return $this;

    }

    /**
     * Sets a new value.
     *
     * @param $value
     *
     * @return IConfigItem
     * @throws ArgumentException if the config value is invalid
     */
    public function setValue( $value )
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
                $this->_value = (float) \str_replace( ',', '.', $value );

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
     * @return IConfigItem
     */
    public function setIsChanged( bool $changed )
    {

        $this->_changed = $changed;

        return $this;

    }

    /**
     * Sets if the item is nullable.
     *
     * @param bool $nullable
     *
     * @return IConfigItem
     */
    public function setIsNullable( bool $nullable )
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
    public function setType( string $typeName )
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
     */
    public static function Create(
        IConfigSection $parentSection, string $name, string $type, $value, bool $nullable = false ): IConfigItem
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

