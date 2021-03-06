<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-05-25
 * @version        0.1.0
 */


return [

    [
        'name'        => 'default',
        'description' => 'A optional section description…',
        'items'       => [
            [
                'name'        => 'foo',
                'description' => 'A optional item description…',
                'nullable'    => false,
                'type'        => 'bool',
                'value'       => new stdClass(),
            ],
            [
                'name'     => 'bar',
                'nullable' => true,
                'type'     => 'int',
                'value'    => 1234,
            ],
            [
                'name'     => 'baz',
                'nullable' => true,
                'type'     => 'string',
            ],
        ],
    ],

];

