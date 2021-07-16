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


use \Niirrty\{ArgumentException, NiirrtyException, Type};
use \Niirrty\Config\{ConfigItem, ConfigSection, Configuration, IConfiguration};
use \Niirrty\Config\Exceptions\{ConfigParseException, ConfigProviderException, ConfigProviderOptionException};
use \Niirrty\IO\FileAccessException;
use \Niirrty\IO\Vfs\VfsManager;


class PHPConfigProvider extends AbstractFileConfigProvider implements IConfigProvider
{


    /**
     * PhpConfigProvider constructor.
     *
     * @param string $name Sets the name of the provider.
     */
    public function __construct( string $name = 'PHP' )
    {

        parent::__construct( empty( $name ) ? 'PHP' : $name, [ 'php' ] );

    }


    /**
     * Reads all available configuration items from the source.
     *
     * @param string[]|null $sectionNames
     *
     * @return IConfiguration
     * @throws ConfigParseException
     * @throws ConfigProviderException|ArgumentException
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

        try
        {
            /** @noinspection PhpIncludeInspection */
            $data = include $this->options[ 'file' ];
        }
        catch ( \Throwable $ex )
        {
            throw new ConfigProviderException(
                $this->name,
                'Unable to load config data from file "' . $this->options[ 'file' ] . '"!',
                $ex
            );
        }

        if ( !\is_array( $data ) )
        {
            throw new ConfigProviderException(
                $this->name,
                'Unable to load config data from file "' . $this->options[ 'file' ] . '"!'
            );
        }

        foreach ( $data as $sectionKey => $sectionData )
        {
            if ( \is_int( $sectionKey ) )
            {
                if ( !isset( $sectionData[ 'name' ] ) )
                {
                    throw new ConfigParseException(
                        $this->name,
                        'Invalid config section, a section must have a name.'
                    );
                }
                $sectionName = $sectionData[ 'name' ];
            }
            else
            {
                $sectionName = $sectionKey;
            }
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
                foreach ( $sectionData[ 'items' ] as $itemKey => $itemData )
                {
                    if ( \is_int( $itemKey ) )
                    {
                        if ( !isset( $itemData[ 'name' ] ) )
                        {
                            throw new ConfigParseException(
                                $this->name,
                                'Invalid config item in section "' . $sectionName . '", missing a item name.'
                            );
                        }
                        $itemName = $itemData[ 'name' ];
                    }
                    else
                    {
                        $itemName = $itemKey;
                    }
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
     * @return PHPConfigProvider
     * @throws ConfigProviderException
     * @throws FileAccessException
     */
    public function write( IConfiguration $config ) : PHPConfigProvider
    {

        if ( $this->_fileExists && !\is_writable( $this->options[ 'file' ] ) )
        {
            throw new ConfigProviderException(
                $this->name,
                'Can not write to config file "' . $this->options[ 'file' ] . '" if the file is not writable!'
            );
        }

        $fp = IOHelper::fopen( $this->options[ 'file' ], 'wb' );

        \fwrite( $fp, '<?php' . "\nreturn [" );

        $i = 0;

        foreach ( $config as $section )
        {

            \fwrite( $fp, ( 0 === $i ? '' : ',' ) . "\n   " . $this->valueToPHP( $section->getName() ) . ' => [' );

            if ( null !== $section->getDescription() )
            {
                \fwrite( $fp, "\n      'description' => " . $this->valueToPHP( $section->getDescription() ) . ',' );
            }

            \fwrite( $fp, "\n      'items' => [" );

            $j = 0;

            foreach ( $section as $itm )
            {

                \fwrite( $fp,
                         ( 0 === $j ? '' : ',' ) . "\n         " . $this->valueToPHP( $itm->getName() ) . ' => [' );

                if ( null !== $itm->getDescription() )
                {
                    \fwrite( $fp,
                             "\n            'description' => " . $this->valueToPHP( $itm->getDescription() ) . ',' );
                }

                \fwrite( $fp, "\n            'nullable' => " . $this->valueToPHP( $itm->isNullable() ) . ',' );
                \fwrite( $fp, "\n            'type' => " . $this->valueToPHP( $itm->getType() ) . ',' );
                \fwrite( $fp, "\n            'value' => " . $this->valueToPHP( $itm->getValue() ) . ',' );

                \fwrite( $fp, "\n         ]" );

                $j++;

            }

            \fwrite( $fp, "\n      ]" );

            \fwrite( $fp, "\n   ]" );

            $i++;

        }

        \fwrite( $fp, "\n];\n" );

        $config->setIsChanged( false );

        return $this;

    }


    /**
     * Validates the defined option and throws a exception on error.
     *
     * @param string $name  The option name.
     * @param mixed  $value The option value.
     */
    protected function validateOption( string $name, mixed $value ) { }


    private function valueToPHP( $value ): string
    {

        try
        {
            return ( new Type( $value ) )->getPhpCode();
        }
        catch ( NiirrtyException )
        {
            return '';
        }

    }


    /**
     * Init a new PHP config provider.
     *
     * @param string          $file       The path of the PHP config file.
     * @param array           $extensions Allowed PHP file name extensions.
     * @param string          $name       The name of the PHP provider.
     * @param VfsManager|null $vfsManager Optional VFS Manager to handle VFS paths
     *
     * @return PHPConfigProvider
     * @throws ConfigProviderOptionException
     */
    public static function Init(
        string $file, array $extensions = [ 'php' ], string $name = 'PHP',
        VfsManager $vfsManager = null ): PHPConfigProvider
    {

        $provider = new PHPConfigProvider( $name );

        $provider->setExtensions( $extensions );
        $provider->setFile( $file, $vfsManager );

        return $provider;

    }


}

