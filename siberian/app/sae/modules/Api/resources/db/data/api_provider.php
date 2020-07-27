<?php
$api_providers = [
    [
        'code' => 'instagram',
        'icon' => 'fa-instagram',
        'keys' => [
            'token',
            'client_id'
        ]
    ],
    [
        'code' => 'facebook',
        'icon' => 'fa-facebook-square',
        'keys' => [
            'app_id',
            'secret_key'
        ]
    ],
    //[
    //    'code' => 'youtube',
    //    'icon' => 'fa-youtube',
    //    'keys' => [
    //        'api_key'
    //    ]
    //],
    [
        'code' => 'soundcloud',
        'icon' => 'fa-soundcloud',
        'keys' => [
            'client_id',
            'secret_id'
        ]
    ],
    [
        'code' => 'googlemaps',
        'icon' => 'fa-map-marker',
        'keys' => [
            'secret_key',
        ]
    ],
    [
        'code' => 'yandex',
        'icon' => 'fa-flag',
        'keys' => [
            'api_key',
        ]
    ],
    //[
    //    'code' => 'plesk',
    //    'icon' => 'fa-shield',
    //    'keys' => [
    //        'host',
    //        'user',
    //        'password',
    //        'webspace',
    //    ]
    //],
    [
        'code' => 'cpanel',
        'icon' => 'fa-shield',
        'keys' => [
            'host',
            'user',
            'password',
            'webspace',
        ]
    ],
    //[
    //    'code' => 'vestacp',
    //    'icon' => 'fa-shield',
    //    'keys' => [
    //        'host',
    //        'user',
    //        'password',
    //        'webspace',
    //    ]
    //],
    [
        'code' => 'vestacpcli',
        'icon' => 'fa-shield',
        'keys' => [
            'user',
            'webspace',
        ]
    ],
    [
        'code' => 'pleskcli',
        'icon' => 'fa-shield',
        'keys' => [
            'ip',
            'webspace',
        ]
    ],
    [
        'code' => 'directadmin',
        'icon' => 'fa-shield',
        'keys' => [
            'host',
            'user',
            'password',
            'webspace',
        ]
    ],
    [
        'code' => 'smtp_credentials',
        'icon' => 'fa-envelope-o',
        'keys' => [
            'auth',
            'server',
            'username',
            'password',
            'ssl',
            'port',
        ]
    ]
];

foreach($api_providers as $api_provider) {

    $data = [
        'code' => $api_provider['code'],
        'name' => ucfirst($api_provider['code']),
        'icon' => $api_provider['icon'],
    ];

    $provider = new Api_Model_Provider();
    $provider
        ->setData($data)
        ->insertOnce(['code']);

    foreach($api_provider['keys'] as $key) {
        $data = [
            'provider_id' => $provider->getId(),
            'key' => $key,
        ];

        $apiModelKey = new Api_Model_Key();
        $apiModelKey
            ->setData($data)
            ->insertOnce(['provider_id', 'key']);
    }

}

# Delete instagram from backoffice
$provider_model = new Api_Model_Provider();
$provider = $provider_model->find('instagram', 'code');
if($provider->getId()) {
    $provider->delete();
}
