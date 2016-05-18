<?php
$apis = array(
    array(
        "code" => "instagram",
        "icon" => "fa-instagram",
        "keys" => array(
            "token",
            "client_id"
        )
    ),
    array(
        "code" => "facebook",
        "icon" => "fa-facebook-square",
        "keys" => array(
            "app_id",
            "secret_key"
        )
    ),
    array(
        "code" => "youtube",
        "icon" => "fa-youtube",
        "keys" => array(
            "api_key"
        )
    ),
    array(
        "code" => "soundcloud",
        "icon" => "fa-soundcloud",
        "keys" => array(
            "client_id",
            "secret_id"
        )
    )
);

foreach($apis as $provider_data) {

    $provider_name = ucfirst($provider_data["code"]);
    $provider = new Api_Model_Provider();

    $provider->setData(array(
        "code" => $provider_data["code"],
        "name" => $provider_name,
        "icon" => $provider_data["icon"]
    ))->save();

    foreach($provider_data["keys"] as $key) {
        $data = array(
            'provider_id' => $provider->getId(),
            'key' => $key
        );

        $key = new Api_Model_Key();
        $key->setData($data)->save();

    }

}
