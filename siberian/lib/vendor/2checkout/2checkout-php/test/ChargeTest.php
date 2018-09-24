<?php

require_once(dirname(__FILE__) . '/../lib/Twocheckout.php');

class TestCharge extends PHPUnit_Framework_TestCase
{

    public function testChargeForm()
    {
        $params = array(
            'sid' => '1817037',
            'mode' => '2CO',
            'li_0_name' => 'Test Product',
            'li_0_price' => '0.01'
        );
        Twocheckout_Charge::form($params, "Click Here!");
    }

    public function testChargeFormAuto()
    {
        $params = array(
            'sid' => '1817037',
            'mode' => '2CO',
            'li_0_name' => 'Test Product',
            'li_0_price' => '0.01'
        );
        Twocheckout_Charge::form($params, 'auto');
    }

    public function testDirect()
    {
        $params = array(
            'sid' => '1817037',
            'mode' => '2CO',
            'li_0_name' => 'Test Product',
            'li_0_price' => '0.01',
            'card_holder_name' => 'Testing Tester',
            'email' => 'christensoncraig@gmail.com',
            'street_address' => '123 test st',
            'city' => 'Columbus',
            'state' => 'Ohio',
            'zip' => '43123',
            'country' => 'USA'
        );
        Twocheckout_Charge::direct($params, "Click Here!");
    }

    public function testDirectAuto()
    {
        Twocheckout::sandbox(true);
        $params = array(
            'sid' => '1817037',
            'mode' => '2CO',
            'li_0_name' => 'Test Product',
            'li_0_price' => '0.01',
            'card_holder_name' => 'Testing Tester',
            'email' => 'christensoncraig@gmail.com',
            'street_address' => '123 test st',
            'city' => 'Columbus',
            'state' => 'Ohio',
            'zip' => '43123',
            'country' => 'USA'
        );
        Twocheckout_Charge::direct($params, 'auto');
    }

    public function testChargeLink()
    {
        Twocheckout::sandbox(true);
        $params = array(
            'sid' => '1817037',
            'mode' => '2CO',
            'li_0_name' => 'Test Product',
            'li_0_price' => '0.01'
        );
        Twocheckout_Charge::link($params);
    }

    public function testChargeAuth()
    {
        Twocheckout::privateKey('BE632CB0-BB29-11E3-AFB6-D99C28100996');
        Twocheckout::sellerId('901248204');
        Twocheckout::sandbox(true);

        try {
            $charge = Twocheckout_Charge::auth(array(
                "sellerId" => "901248204",
                "merchantOrderId" => "123",
                "token" => 'MjFiYzIzYjAtYjE4YS00ZmI0LTg4YzYtNDIzMTBlMjc0MDlk',
                "currency" => 'USD',
                "total" => '10.00',
                "billingAddr" => array(
                    "name" => 'Testing Tester',
                    "addrLine1" => '123 Test St',
                    "city" => 'Columbus',
                    "state" => 'OH',
                    "zipCode" => '43123',
                    "country" => 'USA',
                    "email" => 'testingtester@2co.com',
                    "phoneNumber" => '555-555-5555'
                ),
                "shippingAddr" => array(
                    "name" => 'Testing Tester',
                    "addrLine1" => '123 Test St',
                    "city" => 'Columbus',
                    "state" => 'OH',
                    "zipCode" => '43123',
                    "country" => 'USA',
                    "email" => 'testingtester@2co.com',
                    "phoneNumber" => '555-555-5555'
                )
            ));
            $this->assertEquals('APPROVED', $charge['response']['responseCode']);
        } catch (Twocheckout_Error $e) {
            $this->assertEquals('Bad request - parameter error', $e->getMessage());
        }
    }
}
