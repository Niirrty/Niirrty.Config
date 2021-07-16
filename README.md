# Niirrty.Config

A configuration helping library

A config value must be always a part of a section and a configuration can contain multiple sections

## Supported config formats

Currently the following formats are supported:

* JSON
* XML
* PHP

But you can easy create your own by extending from `Niirrty\Config\Provider\BaseConfigProvider`
and implement the `Niirrty\Config\Provider\IConfigProvider` interface.

A Provider is used to read config data from a specific format and write config data to a specific format.


## Installation

This is a package available via composer:

```bash
composer require niirrty/niirrty.config ^0.4 
```

or inside the `require` area of the `composer.json`:

```json
{
   "require": {
                "php": ">=8.0",
                "niirrty/niirrty.config": "^0.4"
              }
}
```


## Usage

To get config data from a specific JSON config file:

```php
<?php

use Niirrty\Config\Provider\JSONConfigProvider;

try
{
   // Read configuration from JSON file
   $config = JSONConfigProvider::Init(
         __DIR__ . '/data/config.json', // JSON config file path
         [ 'json' ],                    // Allowed file extension(s)
         'JSON'                         // The provider name
      )
      ->read();
      
   // Output the config data, converted to a array
   print_r( $config->toArray() );
   
   // Save config (after changes) to an other file
   if ( ! \file_exists( __DIR__ . '/data/config-saved.json' ) )
   {
      // Set/Change value for section 'default' and item 'foo'
      $config[ 'default::foo' ] = true;
      // You can do the same by:
      # $config[ 'default' ][ 'foo' ] = true;
      // or by:
      # $config->setValue( 'default', 'foo', true );
      // assign new config file path to provider
      $config->getProvider()->setOption( 'file', __DIR__ . '/data/config-saved.json' );
      // Save the config with the owning provider
      $config->getProvider()->write( $config );
   }

}
catch ( \Throwable $ex )
{
   // Handle errors
   echo $ex;
}
```

The other supported file formats can be accessed by the `\Niirrty\Config\Provider\PHPConfigProvider` and
`\Niirrty\Config\Provider\JSONConfigProvider` classes.

## Format Conversation

There is no special stuff required. For example: You can read with the JSON provider and write with a XML Provider

```php
<?php

use Niirrty\Config\Provider\JSONConfigProvider;
use Niirrty\Config\Provider\XMLConfigProvider;

try
{
   // Read configuration from JSON file
   $config = JSONConfigProvider::Init(
         __DIR__ . '/data/config.json', // JSON config file path
         [ 'json' ],                    // Allowed file extension(s)
         'JSON'                         // The provider name
      )
      ->read();
      
   // Output the config data, converted to a array
   print_r( $config->toArray() );
   
   // Set/Change value for section 'default' and item 'foo'
   $config[ 'default::foo' ] = true;
   // Save the config with a new XML provider
   XMLConfigProvider::Init(
         __DIR__ . '/data/config.json', // XML config file path
         [ 'xml' ],                     // Allowed file extension(s)
         'XML'                          // The provider name
      )
      ->write( $config );

}
catch ( \Throwable $ex )
{
   // Handle errors
   echo $ex;
}
```

## JSON config format

The JSON config file must be defined by the following format:

```json
[

   {
      "name": "Section name",
      "description": "A optional section description…",
      "items": [
         {
            "name": "config item name 1",
            "description": "A optional item description…",
            "nullable": false,
            "type": "bool",
            "value": false
         }
      ]
   }

]
```

A section must be defined by the 'name' and the 'items'. The 'description' ist optionally.

A item must be defined by the 'name' and 'value' properties. The 'description' is optionally.
'nullable' defaults to false, and the 'type' defaults to "string"

A config can define 0 or more sections. A section must define 0 or more items.

## PHP config Format

The PHP config file must be defined by the following format:

```php
<?php

return [

   [
      'name'         => 'Section name',
      'description'  => 'A optional section description…',
      'items'        => [
         [
            'name'         => 'config item name 1',
            'description'  => 'A optional item description…',
            'nullable'     => false,
            'type'         => 'bool',
            'value'        => false
         ],
         // more items…
      ],
      // more sections…
   ]

];
```

A section must be defined by the 'name' and the 'items'. The 'description' ist optionally.

A item must be defined by the 'name' and 'value' properties. The 'description' is optionally.
'nullable' defaults to false, and the 'type' defaults to string

A config can define 0 or more sections. A section must define 0 or more items.

### Associative array format

You can also use associative arrays. The keys must be the section and item names

```php
<?php

return [

   'Section name' => [
      'description'  => 'A optional section description…',
      'items'        => [
         'config item name 1' => [
            'description'  => 'A optional item description…',
            'nullable'     => false,
            'type'         => 'bool',
            'value'        => false
         ],
         // more items…
      ],
      // more sections…
   ]

];
```


## XML config Format

The XML config file must be defined by the following format:

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<Config>

   <Section name="Section name">

      <Description>'A optional section description…</Description>

      <Item name="config item name 1"
            type="bool" nullable="false"
            value="false"
            description="A optional item description…"/>
            
      <!--
      More items
      <Item…/>
      -->

   </Section>

</Config>
```

A section must be defined by the 'name' and the 'items'. The 'description' is optionally.

A item must be defined by the 'name' and 'value' properties. The 'description' is optionally.
'nullable' defaults to false, and the 'type' defaults to string

A config can define 0 or more sections. A section must define 0 or more items.

If the item description and/or value contains more than one line or other special meaning chars, it can also been
defined as separate elements

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<Config>

   <Section name="Section name">

      <Description>'A optional section description…</Description>

      <Item name="config item name 1" type="bool" nullable="false">
         <Description>A optional item description…</Description>
         <Value>false</Value>
      </Item>

   </Section>

</Config>
```

