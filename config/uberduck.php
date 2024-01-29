<?php

return [
    'api_key' => getenv('UBERDUCK_KEY'),
    'api_secret' => getenv('UBERDUCK_SECRET'),
    'api_host' => getenv('UBERDUCK_HOST') ? getenv('UBERDUCK_HOST') : 'https://api.uberduck.ai/'
];
