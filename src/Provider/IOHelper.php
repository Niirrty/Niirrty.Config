<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Ni Irrty
 * @license        MIT
 * @since          2018-06-05
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Config\Provider;


use Niirrty\IO\FileAccessException;


/**
 * A IO helper class.
 *
 * @package Niirrty\Config\Provider
 */
class IOHelper
{


    /**
     * Opens a file with defined mode.
     *
     * Known modes and usage is described by {@link http://php.net/manual/de/function.fopen.php}
     *
     * @param string $file
     * @param string $mode
     *
     * @return resource
     * @throws FileAccessException
     */
    public static function fOpen( string $file, string $mode )
    {

        try
        {
            $fp = \fopen( $file, $mode );
        }
        catch ( \Throwable )
        {

            $accessMode = FileAccessException::ACCESS_READ;
            $modeChar2 = \strlen( $mode ) > 1 ? $mode[ 1 ] : '';
            if ( '+' === $modeChar2 )
            {
                $accessMode = FileAccessException::ACCESS_READWRITE;
            }
            else
            {
                switch ( $mode[ 0 ] )
                {
                    case 'w':
                    case 'a':
                    case 'x':
                    case 'c':
                        $accessMode = FileAccessException::ACCESS_WRITE;
                        break;
                    default:
                        break;
                }
            }

            throw new FileAccessException(
                $file,
                $accessMode
            );

        }

        return $fp;

    }

    /**
     * @param string $file
     *
     * @return bool|string
     * @throws FileAccessException
     */
    public static function fileGetContents( string $file ): bool|string
    {

        try
        {
            return \file_get_contents( $file );
        }
        catch ( \Throwable )
        {
            throw FileAccessException::Read( $file );
        }

    }

    /**
     * @param string $file
     * @param string $contents
     *
     * @throws FileAccessException
     */
    public static function fileSetContents( string $file, string $contents ) : void
    {

        try
        {
            \file_put_contents( $file, $contents );
        }
        catch ( \Throwable )
        {
            throw FileAccessException::Write( $file );
        }

    }


}

