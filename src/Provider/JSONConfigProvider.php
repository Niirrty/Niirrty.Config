<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-05-25
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Config\Provider;


use Niirrty\ArgumentException;
use Niirrty\Config\
{ConfigItem, ConfigSection, Configuration, IConfiguration};
use Niirrty\Config\Exceptions\
{ConfigParseException, ConfigProviderException, ConfigProviderOptionException};
use Niirrty\IO\FileAccessException;
use Niirrty\IO\Vfs\VfsManager;


/**
 * A JSON file configuration provider for accessing JSON config data.
 *
 * The data must be defined by a known format.
 *
 * Each configuration can contain 0 or more configuration sections.
 *
 * Each config section can contain 0 or more configuration items (a named config value with some metadata)
 *
 * A item can not be defined without a owning section
 *
 * <code>
 * [
 *
 *    {
 *       "name": "default",
 *       "description": "A optional section description…",
 *       "items": [
 *          {
 *             "name": "foo",
 *             "description": "A optional item description…",
 *             "nullable": false,
 *             "type": "bool",
 *             "value": false
 *          },
 *          {
 *             "name": "bar",
 *             "nullable": true,
 *             "type": "int",
 *             "value": 1234
 *          },
 *          {
 *             "name": "baz",
 *             "nullable": true,
 *             "type": "string"
 *          }
 *       ]
 *    }
 *
 * ]
 * </code>
 *
 * @package Niirrty\Config\Provider
 */
class JSONConfigProvider extends AbstractFileConfigProvider implements IConfigProvider
{


    /**
     * PhpConfigProvider constructor.
     *
     * @param string $name Sets the name of the provider.
     */
    public function __construct( string $name = 'JSON' )
    {

        parent::__construct( empty( $name ) ? 'JSON' : $name, [ 'json' ] );

    }


    /**
     * Reads all available configuration items from the source.
     *
     * @param string[]|null $sectionNames
     *
     * @return IConfiguration
     * @throws ConfigParseException|ConfigProviderException|FileAccessException|ArgumentException|\Throwable
     */
    public function read( ?array $sectionNames = null ): IConfiguration
    {

        $config = new Configuration( $this );

        if ( !$this->_fileExists )
        {
            return $config;
        }

        // Ensure $sectionNames is null if it is a empty array
        if ( null !== $sectionNames && 1 > \count( $sectionNames ) )
        {
            $sectionNames = null;
        }

        $dataString = IOHelper::fileGetContents( $this->options[ 'file' ] );

        if ( empty( $dataString ) )
        {
            throw new ConfigProviderException(
                $this->name,
                'Unable to load config data from file "'
                . $this->options[ 'file' ]
                . '" if the file is empty or not readable!'
            );
        }

        $data = @\json_decode( $dataString, true );

        if ( !\is_array( $data ) )
        {
            throw new ConfigProviderException(
                $this->name,
                'Unable to load config data from file "' . $this->options[ 'file' ] . '" if the data are invalid JSON!'
            );
        }

        foreach ( $data as $sectionData )
        {
            if ( !isset( $sectionData[ 'name' ] ) )
            {
                throw new ConfigParseException(
                    $this->name,
                    'Invalid config section, a section must have a name.'
                );
            }
            $sectionName = $sectionData[ 'name' ];
            if ( null !== $sectionNames && !\in_array( $sectionName, $sectionNames, true ) )
            {
                continue;
            }
            $description = $sectionData[ 'description' ] ?? null;
            $section = new ConfigSection( $sectionName, $description );
            if ( isset( $sectionData[ 'items' ] ) &&
                 \is_array( $sectionData[ 'items' ] ) &&
                 0 < \count( $sectionData[ 'items' ] ) )
            {
                foreach ( $sectionData[ 'items' ] as $itemData )
                {
                    if ( !isset( $itemData[ 'name' ] ) )
                    {
                        throw new ConfigParseException(
                            $this->name,
                            'Invalid config item in section "' . $sectionName . '", missing a item name.'
                        );
                    }
                    $itemName = $itemData[ 'name' ];
                    $nullable = $itemData[ 'nullable' ] ?? false;
                    if ( !\is_bool( $nullable ) )
                    {
                        $nullable = \boolval( $nullable );
                    }
                    $description = $itemData[ 'description' ] ?? null;
                    $type = $itemData[ 'type' ] ?? 'string';
                    $item = new ConfigItem( $section, $itemName, $description );
                    $item->setIsNullable( $nullable );
                    $item->setType( $type );
                    if ( !isset( $itemData[ 'value' ] ) )
                    {
                        if ( !$nullable )
                        {
                            throw new ConfigParseException(
                                $this->name,
                                'Invalid config item "'
                                . $itemName
                                . '" in section "'
                                . $sectionName
                                . '", null is not a allowed value.'
                            );
                        }
                        $item->setValue( null );
                    }
                    else
                    {
                        try
                        {
                            $item->setValue( $itemData[ 'value' ] );
                        }
                        catch ( \Throwable $ex )
                        {
                            throw new ConfigParseException(
                                $this->name,
                                'Invalid config item "'
                                . $itemName
                                . '" value in section "'
                                . $sectionName
                                . '".',
                                $ex
                            );
                        }
                    }
                    $section->setItem( $item );
                }
            }
            $config->setSection( $section );
        }

        $config->setIsChanged( false );

        return $config;

    }

    /**
     * Writes the config to the source.
     *
     * @param IConfiguration $config
     *
     * @return JSONConfigProvider
     * @throws ConfigProviderException
     * @throws FileAccessException
     */
    public function write( IConfiguration $config ) : self
    {

        // If the file is not writable trigger a exception
        if ( $this->_fileExists && ! \is_writable( $this->options[ 'file' ] ) )
        {
            throw new ConfigProviderException(
                $this->name,
                'Can not write to config file "' . $this->options[ 'file' ] . '" if the file is not writable!'
            );
        }

        // Write JSON data to JSON config file and handle errors
        IOHelper::fileSetContents(
            $this->options[ 'file' ],
            \json_encode( $config->toArray(), \JSON_PRETTY_PRINT )
        );

        // Remove all changed flags from config
        $config->setIsChanged( false );

        return $this;

    }

    /**
     * Validates the defined option and throws a exception on error.
     *
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     */
    protected function validateOption( string $name, mixed $value ) : void {}


    /**
     * Init a new JSON config provider.
     *
     * @param string          $file       The path of the JSON config file.
     * @param array           $extensions Allowed JSON file name extensions.
     * @param string          $name       The name of the JSO provider.
     * @param VfsManager|null $vfsManager Optional VFS Manager to handle VFS paths
     *
     * @return JSONConfigProvider
     * @throws ConfigProviderOptionException
     */
    public static function Init(
        string $file, array $extensions = [ 'json' ], string $name = 'JSON',
        VfsManager $vfsManager = null ): JSONConfigProvider
    {

        $provider = new JSONConfigProvider( $name );

        $provider->setExtensions( $extensions );
        $provider->setFile( $file, $vfsManager );

        return $provider;

    }


}

