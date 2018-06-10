<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-25
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Config\Provider;


use Niirrty\Config\ConfigItem;
use Niirrty\Config\ConfigSection;
use Niirrty\Config\Configuration;
use Niirrty\Config\Exceptions\ConfigParseException;
use Niirrty\Config\Exceptions\ConfigProviderException;
use Niirrty\Config\IConfiguration;


/**
 * A JSON file configuration provider for accessing JSON config data.
 *
 * The data must be defined by a known format.
 *
 * Each configuration can contain 0 or more configuration sections.
 *
 * Each config section can contain 0 or more configuration items (a named config value with some meta data)
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

      parent::_construct( empty( $name ) ? 'JSON' : $name, [ 'json' ] );

   }


   /**
    * Reads all available configuration items from the source.
    *
    * @param string[]|null $sectionNames
    * @return \Niirrty\Config\IConfiguration
    * @throws \Niirrty\Config\Exceptions\ConfigParseException
    * @throws \Niirrty\Config\Exceptions\ConfigProviderException
    * @throws \Niirrty\IO\FileAccessException
    */
   public function read( ?array $sectionNames = null ) : IConfiguration
   {

      $config = new Configuration( $this );

      if ( ! $this->_fileExists )
      {
         return $config;
      }

      // Ensure $sectionNames is null if it is a empty array
      if ( null !== $sectionNames && 1 > \count( $sectionNames ) )
      {
         $sectionNames = null;
      }

      $dataString = null;

      $dataString = IOHelper::fileGetContents( $this->_options[ 'file' ] );

      if ( empty( $dataString ) )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Unable to load config data from file "'
               . $this->_options[ 'file' ]
               . '" if the file is empty or not readable!'
         );
      }

      $data = null;

      $data = @\json_decode( $dataString, true );

      if ( ! \is_array( $data ) )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Unable to load config data from file "' . $this->_options[ 'file' ] . '" if the data are invalid JSON!'
         );
      }

      foreach ( $data as $sectionData )
      {
         if ( ! isset( $sectionData[ 'name' ] ) )
         {
            throw new ConfigParseException(
               $this->_name,
               'Invalid config section, a section must have a name.'
            );
         }
         $sectionName = $sectionData[ 'name' ];
         if ( null !== $sectionNames && ! \in_array( $sectionName, $sectionNames, true ) )
         {
            continue;
         }
         $description = $sectionData[ 'description' ] ?? null;
         $section     = new ConfigSection( $sectionName, $description );
         if ( isset( $sectionData[ 'items' ] ) &&
              \is_array( $sectionData[ 'items' ] ) &&
              0 < \count( $sectionData[ 'items' ] ) )
         {
            foreach ( $sectionData[ 'items' ] as $itemData )
            {
               if ( ! isset( $itemData[ 'name' ] ) )
               {
                  throw new ConfigParseException(
                     $this->_name,
                     'Invalid config item in section "' . $sectionName . '", missing a item name.'
                  );
               }
               $itemName = $itemData[ 'name' ];
               $nullable    = $itemData[ 'nullable' ] ?? false;
               if ( ! \is_bool( $nullable ) ) { $nullable = \boolval( $nullable ); }
               $description = $itemData[ 'description' ] ?? null;
               $type        = $itemData[ 'type' ] ?? 'string';
               $item        = new ConfigItem( $section, $itemName, $description );
               $item->setIsNullable( $nullable );
               $item->setType( $type );
               if ( ! isset( $itemData[ 'value' ] ) )
               {
                  if ( ! $nullable )
                  {
                     throw new ConfigParseException(
                        $this->_name,
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
                  try { $item->setValue( $itemData[ 'value' ] ); }
                  catch ( \Throwable $ex )
                  {
                     throw new ConfigParseException(
                        $this->_name,
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
    * @param \Niirrty\Config\IConfiguration $config
    * @return \Niirrty\Config\Provider\JSONConfigProvider
    * @throws \Niirrty\Config\Exceptions\ConfigProviderException
    * @throws \Niirrty\IO\FileAccessException
    */
   public function write( IConfiguration $config )
   {

      // If the file is not writable trigger a exception
      if ( $this->_fileExists && ! \is_writable( $this->_options[ 'file' ] ) )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Can not write to config file "' . $this->_options[ 'file' ] . '" if the file is not writable!'
         );
      }

      // Write JSON data to JSON config file and handle errors
      IOHelper::fileSetContents(
         $this->_options[ 'file' ],
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
    * @throws \Niirrty\Config\Exceptions\ConfigProviderOptionException If a wrong option value is defined.
    */
   protected function validateOption( string $name, $value )
   {

      return;

   }


   /**
    * Init a new JSON config provider.
    *
    * @param string $file       The path of the JSON config file.
    * @param array  $extensions Allowed JSON file name extensions.
    * @param string $name       The name of the JSO provider.
    * @return \Niirrty\Config\Provider\JSONConfigProvider
    */
   public static function Init( string $file, array $extensions = [ 'json' ], string $name = 'JSON' )
      : JSONConfigProvider
   {

      $provider = new JSONConfigProvider( $name );

      $provider->setExtensions( $extensions );
      $provider->setFile( $file );

      return $provider;

   }


}

