<?php

$datas = array(
    array('code' => 'free', 'name' => 'Free purchase', 'online_payment' => 0)
);

foreach($datas as $data) {
    $method = new Mcommerce_Model_Payment_Method();
    $method
        ->setData($data)
        ->insertOnce(array("code"));
}
