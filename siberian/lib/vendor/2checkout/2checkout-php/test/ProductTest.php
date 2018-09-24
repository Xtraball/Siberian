<?php

require_once(dirname(__FILE__) . '/../lib/Twocheckout.php');

class TestProduct extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Twocheckout::username('testlibraryapi901248204');
        Twocheckout::password('testlibraryapi901248204PASS');
        Twocheckout::sandbox(true);
    }

    public function testProductListRetrieve()
    {
        $params = array(
            'pagesize' => 2
        );
        $products = Twocheckout_Product::retrieve($params);
        $this->assertEquals(2, sizeof($products['products']));
    }

    public function testProductCreate()
    {
        $params = array(
            'name' => "test",
            'price' => 0.01
        );
        $response = Twocheckout_Product::create($params);
        $this->assertEquals("Product successfully created", $response['response_message']);
        $params = array('product_id' => $response['product_id']);
        Twocheckout_Product::delete($params);
    }

    public function testProductRetrieve()
    {
        $params = array(
            'product_id' => 9093717691932
        );
        $product = Twocheckout_Product::retrieve($params);
        $this->assertEquals("9093717691932", $product['product']['product_id']);
    }

    public function testProductUpdate()
    {
        $params = array(
            'name' => "test",
            'product_id' => 9093717691932
        );
        $response = Twocheckout_Product::update($params);
        $this->assertEquals("Product successfully updated", $response['response_message']);
    }

    public function testProductDelete()
    {
        $params = array(
            'name' => "test",
            'price' => 0.01
        );
        $response = Twocheckout_Product::create($params);
        $params = array('product_id' => $response['product_id']);
        $response = Twocheckout_Product::delete($params);
        $this->assertEquals("Product successfully deleted.", $response['response_message']);
    }

}