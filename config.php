<?php

/**
 * This is a demo config file. Copy this into your project as:
 * `config/cloudconvert.php`
 * and customize as needed.
 */

use craft\helpers\App;

return [
    '*' => [
        'apiKey' => App::env('CC_API_KEY'),
        'sandbox' => (bool) App::env('CC_SANDBOX'),
        'enabled' => (bool) App::env('CC_ENABLED') ?: false,
        'thumbnailFolderId' => 1,
        'thumbnailWidth' => 300,
        'thumbnailHeight' => 300,
        'thumbnailFit' => 'crop',
    ],
];
