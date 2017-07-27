<?php

return [
    'debug'    => false,
    'get_wsdl' => false,

    'client_id'     => 'CCCCCCCCCCCCCCCCCCCCCCC',
    'client_secret' => 'CCCCCCCCCCCCCCCCCCCCCCC',

    'default_wsdl'  => 'https://webservice.exacttarget.com/etframework.wsdl',
    'xml_loc'       => '/some/path/to/cache/ExactTargetWSDL.xml',
    'base_url'      => 'https://www.exacttargetapis.com',
    'base_auth_url' => 'https://auth.exacttargetapis.com',

    // proxy settings
    'proxy'         => [
        'host'     => 'localhost',
        'port'     => '8080',
        'username' => null,
        'password' => null,
    ],

    // JWT SSO configuration
    'jtw'           => [
        // JWT Token
        'token' => null,

        // JWT key(s), can be an array
        'keys'  => null,

        'tenant_key' => null,
    ],
];
