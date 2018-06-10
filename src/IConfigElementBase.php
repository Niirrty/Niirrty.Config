<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-20
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Config;


interface IConfigElementBase
{


   /**
    * Gets the name of the configuration element.
    *
    * @return string
    */
   public function getName() : string;

   /**
    * Gets the optional Description, or NULL if no description exists.
    *
    * @return null|string
    */
   public function getDescription() : ?string;


}

