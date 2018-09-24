<?php

require_once(dirname(__FILE__) . '/../lib/Twocheckout.php');

class TestNotification extends PHPUnit_Framework_TestCase
{

    public function testNotificationCheck()
    {
        $params = array(
            'vendor_id' => '1817037',
            'sale_id' => '4774380224',
            'invoice_id' => '4774380233',
            'md5_hash' => '566C45D68B75357AD43F9010CFFE8CF5',
            'secret' => 'tango'
        );
        $result = Twocheckout_Notification::check($params, 'tango');
        $this->assertEquals("Success", $result['response_code']);
    }

}