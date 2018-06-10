<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-20
 * @version        0.1.0
 */


namespace Niirrty\Config;


trait ConfigElementTrait
{


   /** @type string */
   protected $_name;

   /** @type string|null */
   protected $_description;


   /**
    * Gets the name of the configuration element.
    *
    * @return string
    */
   public function getName() : string
   {

      return $this->_name;

   }

   /**
    * Gets the optional Description, or NULL if no description exists.
    *
    * @return null|string
    */
   public function getDescription() : ?string
   {

      return $this->_description;

   }


}

