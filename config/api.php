<?php

/**
 * Extensible, config for any of the API endpoints we want to poke at.
 */
return [
    'user_data' => env('API_USER_DATA_URL'),
    'import_chunksize' => env('IMPORT_CHUNKSIZE'),
];
