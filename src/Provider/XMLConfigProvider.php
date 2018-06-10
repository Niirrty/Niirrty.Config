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
use Niirrty\TypeTool;
use Niirrty\XmlAttributeHelper;


class XMLConfigProvider extends AbstractFileConfigProvider implements IConfigProvider
{


   /**
    * PhpConfigProvider constructor.
    *
    * @param string $name Sets the name of the provider.
    */
   public function __construct( string $name = 'XML' )
   {

      parent::_construct( empty( $name ) ? 'XML' : $name, [ 'xml' ] );

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

      $xmlDoc = null;

      try
      {
         $xmlDoc = \simplexml_load_file( $this->_options[ 'file' ] );
      }
      catch ( \Throwable $ex )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Unable to load config data from file "' . $this->_options[ 'file' ] . '"!',
            $ex
         );
      }

      if ( ! ( $xmlDoc instanceof \SimpleXMLElement ) )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Unable to load config data from file "'
               . $this->_options[ 'file' ]
               . '" if the file is not valid XML or not readable!'
         );
      }

      if ( ! isset( $xmlDoc->Section ) )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Unable to load config data from file "'
            . $this->_options[ 'file' ]
            . '" if the XML not defines the required Section element(s)!'
         );
      }

      foreach ( $xmlDoc->Section as $sectionElement )
      {
         $sectionName = XmlAttributeHelper::GetAttributeValue( $sectionElement, 'name' );
         if ( empty( $sectionName ) )
         {
            throw new ConfigParseException(
               $this->_name,
               'Invalid config section, a section must have a name.'
            );
         }
         if ( null !== $sectionNames && ! \in_array( $sectionName, $sectionNames, true ) )
         {
            continue;
         }
         $description =  XmlAttributeHelper::GetAttributeValue( $sectionElement, 'description' );
         if ( empty( $description ) && isset( $sectionElement->Description ) )
         {
            $description = (string) $sectionElement->Description;
         }
         $section     = new ConfigSection( $sectionName, $description );
         if ( isset( $sectionElement->Item ) )
         {
            foreach ( $sectionElement->Item as $itemElement )
            {
               $itemName = XmlAttributeHelper::GetAttributeValue( $itemElement, 'name' );
               if ( empty( $itemName ) )
               {
                  throw new ConfigParseException(
                     $this->_name,
                     'Invalid config item in section "' . $sectionName . '", missing a item name.'
                  );
               }
               $nullableStr = XmlAttributeHelper::GetAttributeValue( $itemElement, 'nullable' );
               if ( ! TypeTool::IsBoolConvertible( $nullableStr, $nullable ) )
               {
                  $nullable = false;
               }
               $description =  XmlAttributeHelper::GetAttributeValue( $itemElement, 'description' );
               if ( empty( $description ) && isset( $itemElement->Description ) )
               {
                  $description = (string) $itemElement->Description;
               }
               $type        = XmlAttributeHelper::GetAttributeValue( $itemElement, 'type' ) ?? 'string';
               $item        = new ConfigItem( $section, $itemName, $description );
               $item->setIsNullable( $nullable );
               $item->setType( $type );
               $valueStr    = XmlAttributeHelper::GetAttributeValue( $itemElement, 'value' );
               if ( null === $valueStr )
               {
                  if ( ! isset( $itemElement->Value ) )
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
                     try { $item->setValue( (string) $itemElement->Value ); }
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
               }
               else
               {
                  try { $item->setValue( $valueStr ); }
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
    * @return \Niirrty\Config\Provider\XMLConfigProvider
    * @throws \Niirrty\Config\Exceptions\ConfigProviderException
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

      $xmlWriter = new \XMLWriter();

      try { $xmlWriter->openURI( $this->_options[ 'file' ] ); }
      catch ( \Throwable $ex )
      {
         throw new ConfigProviderException(
            $this->_name,
            'Can not write to config file "' . $this->_options[ 'file' ] . '"!',
            $ex
         );
      }

      $xmlWriter->setIndent( true );
      $xmlWriter->setIndentString( '  ' );

      $xmlWriter->startDocument( '1.0', 'utf-8' );
      $xmlWriter->startElement( 'Config' );

      foreach ( $config as $section )
      {
         $xmlWriter->startElement( 'Section' );
         $xmlWriter->writeAttribute( 'name', $section->getName() );
         if ( null !== $section->getDescription() )
         {
            $xmlWriter->writeElement( 'Description', $section->getDescription() );
         }
         foreach ( $section as $item )
         {
            $xmlWriter->startElement( 'Item' );
            /** @noinspection PhpUndefinedMethodInspection */
            $xmlWriter->writeAttribute( 'name', $item->getName() );
            /** @noinspection PhpUndefinedMethodInspection */
            $xmlWriter->writeAttribute( 'type', $item->getType() );
            /** @noinspection PhpUndefinedMethodInspection */
            $xmlWriter->writeAttribute( 'nullable', $item->isNullable() ? 'true' : 'false' );
            /** @noinspection PhpUndefinedMethodInspection */
            switch ( $item->getType() )
            {
               case 'bool':
               case 'boolean':
               case 'int':
               case 'integer':
               case 'float':
               case 'double':
               case 'DateTime':
               case '\\DateTime':
               case 'Niirrty\\Date\\DateTime':
               case '\\Niirrty\\Date\\DateTime':
                  /** @noinspection PhpUndefinedMethodInspection */
                  $xmlWriter->writeAttribute( 'value', $item->getStringValue() );
                  break;

               default:
                  /** @noinspection PhpUndefinedMethodInspection */
                  $xmlWriter->writeElement( 'Value', $item->getStringValue() );
                  break;

            }
            /** @noinspection PhpUndefinedMethodInspection */
            if ( null !== $item->getDescription() )
            {
               /** @noinspection PhpUndefinedMethodInspection */
               $xmlWriter->writeElement( 'Description', $item->getDescription() );
            }
            $xmlWriter->endElement(); // </item>
         }
         $xmlWriter->endElement(); // </section>
      }

      $xmlWriter->endDocument();
      $xmlWriter->flush();
      $xmlWriter = null;

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
    * Init a new XML config provider.
    *
    * @param string $file       The path of the XML config file.
    * @param array  $extensions Allowed XML file name extensions.
    * @param string $name       The name of the XML provider.
    * @return \Niirrty\Config\Provider\XMLConfigProvider
    */
   public static function Init( string $file, array $extensions = [ 'xml' ], string $name = 'XML' )
      : XMLConfigProvider
   {

      $provider = new XMLConfigProvider( $name );

      $provider->setExtensions( $extensions );
      $provider->setFile( $file );

      return $provider;

   }


}

