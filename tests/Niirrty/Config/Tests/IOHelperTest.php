<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-06-08
 * @version        0.1.0
 */


namespace Niirrty\Config\Tests;


use Niirrty\Config\Provider\IOHelper;
use Niirrty\IO\FileAccessException;
use PHPUnit\Framework\TestCase;


class IOHelperTest extends TestCase
{


    public function test_fOpenException1()
    {

        $this->expectException( FileAccessException::class );
        IOHelper::fOpen( __DIR__ . '/sdsd.php', 'rb' );

    }

    public function test_fOpenException3()
    {

        $this->expectException( FileAccessException::class );
        IOHelper::fOpen( __DIR__ . '/etc/sdsd.php', 'a+b' );

    }

    public function test_fOpenException2()
    {

        $this->expectException( FileAccessException::class );
        IOHelper::fOpen( __DIR__ . '/etc/sdsd.php', 'wb' );

    }

    public function test_fileGetContentsException()
    {

        $this->expectException( FileAccessException::class );
        IOHelper::fileGetContents( __DIR__ . '/sdsd.php' );

    }

    public function test_fileSetContentsException()
    {

        $this->expectException( FileAccessException::class );
        IOHelper::fileSetContents( __DIR__ . '/etc/sdsd.php', '<?php phpinfo(); ?>' );

    }


}

