<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Delimiter
    |--------------------------------------------------------------------------
    |
    | You can specify the delimiter to be used when parsing a query parameter as
    | an array.
    |
    */

    'delimiter' => ',',

    /*
    |--------------------------------------------------------------------------
    | Enable matches
    |--------------------------------------------------------------------------
    |
    | You can enable or disable the matches feature, which allows your users to
    | select which columns they want to use to execute a search on the query.
    |
    | Enabling this will also provide a 'searches' property when serialized to
    | allow you to bind the options to a form input.
    |
    */

    'matches' => false,

    /*
    |--------------------------------------------------------------------------
    | Config
    |--------------------------------------------------------------------------
    |
    | You can customise the query parameters that are used to refine the query
    | if not supplied at a refiner level. If your refiner is scoped, these
    | will be prefixed with the scope name.
    |
    */

    'config' => [
        /** The parameter name when using a text search. */
        'searches' => 'search',
        /** The parameter name when selecting which columns to match on. */
        'matches' => 'match',
        /** The parameter name for the sort field and direction. */
        'sorts' => 'sort',
    ],
];
