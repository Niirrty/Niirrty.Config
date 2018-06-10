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
use Niirrty\Type;


class PHPConfigProvider extends AbstractFileConfigProvider implements IConfigProvider
{


   /**
    * PhpConfigProvider constructor.
    *
    * @param string $name Sets the name of the provider.
    */
   public function __construct( string $name = 'PHP' )
   {

      parent::_construct( empty( $name ) ? 'PHP' : $name, [ 'php' ] );

   }


   /**
    * Reads all available configuration items from the source.
    *
    * @param string[]|null $sectionNames
    * @return \Niirrty\Config\IConfiguration
    * @throws \Niirrty\Config\Exceptions\ConfigParseException
    * @throws \Niirrty\Config\Exceptions\ConfigProviderException
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

      $data = null;

      try
      {
         /** @noinspection PhpIncludeInspection */
         $data = include $this->_options[ 'file' ];
      }
      catch ( \Throwable $ex )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Unable to load config data from file "' . $this->_options[ 'file' ] . '"!',
            $ex
         );
      }

      if ( ! \is_array( $data ) )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Unable to load config data from file "' . $this->_options[ 'file' ] . '"!'
         );
      }

      foreach ( $data as $sectionKey => $sectionData )
      {
         if ( \is_int( $sectionKey ) )
         {
            if ( ! isset( $sectionData[ 'name' ] ) )
            {
               throw new ConfigParseException(
                  $this->_name,
                  'Invalid config section, a section must have a name.'
               );
            }
            $sectionName = $sectionData[ 'name' ];
         }
         else { $sectionName = $sectionKey; }
         if ( null !== $sectionNames && ! \in_array( $sectionName, $sectionNames, true ) )
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
                  if ( ! isset( $itemData[ 'name' ] ) )
                  {
                     throw new ConfigParseException(
                        $this->_name,
                        'Invalid config item in section "' . $sectionName . '", missing a item name.'
                     );
                  }
                  $itemName = $itemData[ 'name' ];
               }
               else { $itemName = $itemKey; }
               $nullable = $itemData[ 'nullable' ] ?? false;
               if ( ! \is_bool( $nullable ) )
               {
                  $nullable = \boolval( $nullable );
               }
               $description = $itemData[ 'description' ] ?? null;
               $type = $itemData[ 'type' ] ?? 'string';
               $item = new ConfigItem( $section, $itemName, $description );
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
    * @return \Niirrty\Config\Provider\PHPConfigProvider
    * @throws \Niirrty\Config\Exceptions\ConfigProviderException
    * @throws \Niirrty\IO\FileAccessException
    */
   public function write( IConfiguration $config )
   {

      if ( $this->_fileExists && ! \is_writable( $this->_options[ 'file' ] ) )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Can not write to config file "' . $this->_options[ 'file' ] . '" if the file is not writable!'
         );
      }

      $fp = IOHelper::fopen( $this->_options[ 'file' ], 'wb' );

      \fwrite( $fp, '<?php' . "\nreturn [" );

      $i = 0;

      foreach ( $config as $section )
      {

         \fwrite( $fp, (0 === $i ? '' : ',') . "\n   " . $this->valueToPHP( $section->getName() ) . ' => [' );

         if ( null !== $section->getDescription() )
         {
            \fwrite( $fp, "\n      'description' => " . $this->valueToPHP( $section->getDescription() ) . ',' );
         }

         \fwrite( $fp, "\n      'items' => [" );

         $j = 0;

         foreach ( $section as $itm )
         {

            /** @noinspection PhpUndefinedMethodInspection */
            \fwrite( $fp, ( 0 === $j ? '' : ',') . "\n         " . $this->valueToPHP( $itm->getName() ) . ' => [' );

            /** @noinspection PhpUndefinedMethodInspection */
            if ( null !== $itm->getDescription() )
            {
               /** @noinspection PhpUndefinedMethodInspection */
               \fwrite( $fp, "\n            'description' => " . $this->valueToPHP( $itm->getDescription() ) . ',' );
            }

            /** @noinspection PhpUndefinedMethodInspection */
            \fwrite( $fp, "\n            'nullable' => " . $this->valueToPHP( $itm->isNullable() ) . ',' );
            /** @noinspection PhpUndefinedMethodInspection */
            \fwrite( $fp, "\n            'type' => " . $this->valueToPHP( $itm->getType() ) . ',' );
            /** @noinspection PhpUndefinedMethodInspection */
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
    * @throws \Niirrty\Config\Exceptions\ConfigProviderOptionException If a wrong option value is defined.
    */
   protected function validateOption( string $name, $value )
   {

      // Do nothing
      return;

   }


   private function valueToPHP( $value )
   {

      return ( new Type( $value ) )->getPhpCode();

   }


   /**
    * Init a new PHP config provider.
    *
    * @param string $file       The path of the PHP config file.
    * @param array  $extensions Allowed PHP file name extensions.
    * @param string $name       The name of the PHP provider.
    * @return \Niirrty\Config\Provider\PHPConfigProvider
    */
   public static function Init( string $file, array $extensions = [ 'php' ], string $name = 'PHP' )
      : PHPConfigProvider
   {

      $provider = new PHPConfigProvider( $name );

      $provider->setExtensions( $extensions );
      $provider->setFile( $file );

      return $provider;

   }


}

