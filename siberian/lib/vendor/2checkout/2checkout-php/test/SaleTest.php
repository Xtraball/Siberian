<?php
require_once(dirname(__FILE__) . '/../lib/Twocheckout.php');
class TestSale extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Twocheckout::username('testlibraryapi901248204');
        Twocheckout::password('testlibraryapi901248204PASS');
        Twocheckout::sandbox(true);
    }

    public function testSaleRetrieve()
    {
        $params = array(
            'sale_id' => 9093717691800
        );
        $sale = Twocheckout_Sale::retrieve($params);
        $this->assertEquals("9093717691800", $sale['sale']['sale_id']);
    }

    public function testSaleRetrieveList()
    {
        $params = array(
            'pagesize' => 2
        );
        $sale = Twocheckout_Sale::retrieve($params);
        $this->assertEquals(2, sizeof($sale['sale_summary']));
    }

    public function testSaleRefundSale()
    {
        $params = array(
            'sale_id' => 9093717691800,
            'category' => 1,
            'comment' => 'Order never sent.'
        );
        try {
            $sale = Twocheckout_Sale::refund($params);
            $this->assertEquals("OK", sizeof($sale['response_code']));
        } catch (Twocheckout_Error $e) {
            $this->assertEquals("Invoice was already refunded.", $e->getMessage());
        }
    }

    public function testSaleRefundLineitem()
    {
        $params = array(
            'lineitem_id' => 9093717693210,
            'category' => 1,
            'comment' => 'Order never sent.'
        );
        try {
            $sale = Twocheckout_Sale::refund($params);
            $this->assertEquals("OK", $sale['response_code']);
        } catch (Twocheckout_Error $e) {
            $this->assertEquals("Lineitem was already refunded.", $e->getMessage());
        }
    }

    public function testSaleStopSale()
    {
        $params = array(
            'sale_id' => 9093717763224
        );
        try {
            $response = Twocheckout_Sale::stop($params);
            $this->assertEquals("OK", $response['response_code']);
        } catch (Twocheckout_Error $e) {
            $this->assertEquals("No recurring lineitems to stop.", $e->getMessage());
        }
    }

    public function testSaleStopLineitem()
    {
        $params = array(
            'lineitem_id' => 9093717693210
        );
        try {
            $response = Twocheckout_Sale::stop($params);
            $this->assertEquals("OK", $response['response_code']);
        } catch (Twocheckout_Error $e) {
            $this->assertEquals("Lineitem is not scheduled to recur.", $e->getMessage());
        }
    }

    public function testSaleActive()
    {
        $params = array(
            'sale_id' => 9093717763224
        );
        try {
            $response = Twocheckout_Sale::active($params);
            $this->assertEquals("OK", $response['response_code']);
        } catch (Twocheckout_Error $e) {
            $this->assertEquals("No active recurring lineitems.", $e->getMessage());
        }
    }

    public function testSaleComment()
    {
        $params = array(
            'sale_id' => 9093717691800,
            'sale_comment' => "test"
        );
        $result = Twocheckout_Sale::comment($params);
        $this->assertEquals("Created comment successfully.", $result['response_message']);
    }

    public function testSaleShip()
    {
        $params = array(
            'sale_id' => 9093717691800,
            'tracking_number' => "test"
        );
        try {
            $result = Twocheckout_Sale::ship($params);
            $this->assertEquals("OK", $result['response_code']);
        } catch (Exception $e) {
            $this->assertEquals("Sale already marked shipped.", $e->getMessage());
        }
    }

    public function testSaleReauth()
    {
        $params = array(
            'sale_id' => 9093717691800
        );
        try {
            $result = Twocheckout_Sale::reauth($params);
            $this->assertEquals("OK", $result['response_code']);
        } catch (Exception $e) {
            $this->assertEquals("Payment is already pending or deposited and cannot be reauthorized.", $e->getMessage());
        }
    }

}