<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // 'paths' => ['api/*'],

    // 5-9 允许访问的跨域路径
    'paths' => ['wx/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,
    // 预检请求的时效 设置为0 表示每一次请求 都需要嗅探 
    // 如果设置1800 表示30分钟需要嗅探1次  如果嗅探成功 30分钟内都不需要在嗅探 直接发送真正的请求即可

    'supports_credentials' => false,// 是否允许携带cookie 

];
