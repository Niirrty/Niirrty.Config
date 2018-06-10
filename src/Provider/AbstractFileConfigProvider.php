<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-06-04
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Config\Provider;


use Niirrty\Config\Exceptions\ConfigProviderOptionException;
use Niirrty\IO\File;
use Niirrty\IO\Vfs\IVfsManager;


abstract class AbstractFileConfigProvider extends BaseConfigProvider
{


   /** @type bool */
   protected $_fileExists = false;

   /** @type \Niirrty\IO\Vfs\IVfsManager|null */
   protected $_vfsManager = null;


   protected function _construct( string $name, array $extensions )
   {

      $this->_options[ 'file' ]       = null;
      $this->_options[ 'extensions' ] = $extensions;
      $this->_name                    = $name;
      $this->_valid                   = false;

   }

   /**
    * Sets the path of the config PHP file.
    *
    * @param string                           $filePath
    * @param \Niirrty\IO\Vfs\IVfsManager|null $vfsManager Optional virtual file system manager to resolve the file path.
    * @return \Niirrty\Config\Provider\AbstractFileConfigProvider
    * @throws \Niirrty\Config\Exceptions\ConfigProviderOptionException
    */
   public function setFile( string $filePath, ?IVfsManager $vfsManager = null ) : AbstractFileConfigProvider
   {

      if ( null !== $vfsManager )
      {
         $this->_vfsManager = $vfsManager;
         $filePath = $vfsManager->parsePath( $filePath );
      }
      else if ( null !== $this->_vfsManager )
      {
         $filePath = $this->_vfsManager->parsePath( $filePath );
      }

      if ( ! @\is_file( $filePath ) || ! @\file_exists( $filePath ) )
      {

         if ( \is_dir( $filePath ) )
         {
            throw new ConfigProviderOptionException(
               $this->_name,
               'file',
               'Can not set the config file if it points to a directory and not a file!'
            );
         }

         $dir = \dirname( $filePath );

         if ( ! \is_dir( $dir ) )
         {
            throw new ConfigProviderOptionException(
               $this->_name,
               'file',
               'Can not set the config file if it points to a not existing file inside a not existing directory!'
            );
         }

         if ( ! \is_writable( $dir ) )
         {
            throw new ConfigProviderOptionException(
               $this->_name,
               'file',
               'Can not set the config file if it points to a not writable directory!'
            );
         }

         $this->_options[ 'file' ] = $filePath;
         $this->_valid = true;
         $this->_fileExists = false;

         return $this;

      }

      if ( ! \is_readable( $filePath ) )
      {
         throw new ConfigProviderOptionException(
            $this->_name,
            'file',
            'Can not set this provider option if the value not points to a unreadable file!'
         );
      }

      $extension = File::GetExtensionName( $filePath );
      if ( false === $extension || ! \in_array( \strtolower( $extension ), $this->_options[ 'extensions' ] ) )
      {
         throw new ConfigProviderOptionException(
            $this->_name,
            'file',
            'Can not set this provider option because the file name extension "'
            . $extension
            . '" is not allowed!'
         );
      }

      $this->_options[ 'file' ] = $filePath;
      $this->_valid = true;
      $this->_fileExists = true;

      return $this;

   }

   /**
    * Sets the names of the accepted PHP config file extensions.
    *
    * @param array $extensions
    * @return \Niirrty\Config\Provider\PHPConfigProvider
    * @throws \Niirrty\Config\Exceptions\ConfigProviderOptionException
    */
   public function setExtensions( array $extensions ) : AbstractFileConfigProvider
   {

      if ( 1 > \count( $extensions ) )
      {
         throw new ConfigProviderOptionException(
            $this->_name,
            'extensions',
            'Can not set this provider option if the value is a empty array!'
         );
      }
      foreach ( $extensions as $extension )
      {
         if ( ! \is_string( $extension ) || ! \preg_match( '~^[a-zA-Z0-9]{1,5}$~', $extension ) )
         {
            throw new ConfigProviderOptionException(
               $this->_name,
               'extensions',
               'Can not set this provider option if not contains string values (1-5 alpha numeric chars)!'
            );
         }
      }
      if ( null !== $this->_options[ 'file' ] )
      {
         $extension = File::GetExtensionName( $this->_options[ 'file' ] );
         if ( false === $extension || ! \in_array( \strtolower( $extension ), $extensions ) )
         {
            throw new ConfigProviderOptionException(
               $this->_name,
               'extensions',
               'Can not set the option because the defined file not use one of the allowed extensions!'
            );
         }
      }
      $this->_options[ 'extensions' ] = $extensions;

      return $this;

   }

   /**
    * Gets the path of the config PHP file or null if none is defined
    *
    * @return string|null
    */
   public function getFile() : ?string
   {

      return $this->_options[ 'file' ];

   }

   /**
    * Gets the path of the config PHP file or null if none is defined
    *
    * @return string|null
    */
   public function getExtensions() : array
   {

      return $this->_options[ 'extensions' ];

   }

}

