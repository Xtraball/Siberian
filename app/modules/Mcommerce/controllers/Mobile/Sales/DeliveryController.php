<?php

class Mcommerce_Mobile_Sales_DeliveryController extends Mcommerce_Controller_Mobile_Default {


    public function findstoreAction() {

        $option = $this->getCurrentOptionValue();
        $cart = $this->getCart();
        $store = $cart->getStore();
		

        $html = array();

        $storeJson = array(
            "id" => $store->getId(),
            "name" => $store->getName(),
            "deliveryMethods" => array(),
            "clients_calculate_change" => (boolean) $store->getClientsCalculateChange()
        );

        foreach ($store->getDeliveryMethods() as $deliveryMethod) {

            $deliveryMethod->setCart($this->getCart());

            if($deliveryMethod->isAvailable()) {
                $storeJson["deliveryMethods"][] = array(
                    "id" => $deliveryMethod->getId(),
                    "code" => $deliveryMethod->getCode(),
                    "name" =>$deliveryMethod->getName(),
                    "price" => (double) $deliveryMethod->getPrice(),
                    "formattedPrice" => $deliveryMethod->getPrice() > 0 ? $deliveryMethod->getFormattedPrice() : null
                );
            }
        }

        $html["store"] = $storeJson;

        $html["clients_calculate_change"] = (boolean) ($cart->getDeliveryMethod()->getCode() == "home_delivery" && $cart->getStore()->getClientsCalculateChange());

        $this->_sendHtml($html);
    }

    public function updateAction() {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $datas= $data["form"];

            $html = array();

            try {

                if(empty($datas['delivery_method_id']))
				{$message = $this->_('Please choose a delivery method');
				 throw new Exception($this->_($message)); }

                if(empty($datas['store_id'])) 
				{$message = $this->_('An error occurred while saving. Please try again later.');
				throw new Exception($this->_($message));}


				
				$store = new Mcommerce_Model_Store();
                $store->find($datas['store_id']);
				$mcommerce = new Mcommerce_Model_Mcommerce();
                $mcommerce->find(array("mcommerce_id" => $store->getMcommerceId()));
		        $mcommerce->getId();
				
				
				
				if((int)$mcommerce->getData("require_datedelivery")==1)
				{
				
				if(empty($datas['delivery_date'])) 
				{$message = $this->_('Please choose a delivery date');
				throw new Exception($this->_($message));}

				if(empty($datas['delivery_time'])) 
				{$message = $this->_('Please choose a delivery time');
				throw new Exception($this->_($message));}				
				 
				 $date0 = strtotime($datas['delivery_date']);
				 $date1=date('Y-m-d',$date0);
				 $time0 = strtotime($datas['delivery_time']);
				 $time1=date('H:i:s',$time0);
				 $ddate=$date1.' '.$time1;
				 $date = strtotime($ddate);
				 $datestr = date('Y-m-d H-i-s',$date);

				 if($date<time())
				 {$message = $this->_('The date and time of delivery must be later than now.');
				  throw new Exception($this->_($message));}
				  
                $this->getCart()
                    ->setDeliveryDatetime($datestr)
                    ->save();				  
				}
				
				if  ((int)$mcommerce->getData("show_customercomment")==1)
                   { 
					 $this->getCart()
                          ->setDeliveryComment($datas['delivery_comment'])
                          ->save();
				   }
				
				
				

                $method = $store->getDeliveryMethod($datas['delivery_method_id']);
                $delivery_tax = $store->getTax($method->getTaxId());
                $delivery_tax_rate = $delivery_tax->getRate();

                $data_cart = array(
                    "store_id" => $datas['store_id'],
                    "paid_amount" => $datas['paid_amount'] ? $datas['paid_amount'] : null,
                    "delivery_cost" => $method->getPrice(),
                    "delivery_tax_rate" => $delivery_tax_rate
                );


                $this->getCart()
                    ->setData($data_cart)
                    ->setDeliveryMethodId($datas['delivery_method_id'])
                    ->save()
                ;

                $html = array(
                    'store_id' => $this->getCart()->getStoreId(),
                    'delivery_method_id' => $this->getCart()->getDeliveryMethodId(),
                    'cartId' => $this->getCart()->getId()
                );
            }
            catch(Exception $e ) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);
        } 

    }
}