<?php

class Mcommerce_Mobile_Sales_CustomerController extends Mcommerce_Controller_Mobile_Default {

    /*public function viewAction() {
	
	   $html=parent::viewAction();
	   $mcommerce = new Mcommerce_Model_Mcommerce();
       $mcommerce->find(array("value_id" => $value_id));
	   $mcommerce->getRequireAddress();
	   $this->_sendHtml($html);
    }*/
	 
    public function updateAction() {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $form = $data["form"];
            
            $customer= $form["customer"];
            
            $html = array();
			
			


             try {
			 

				/*hjdc RequireAddress*/
				                
				$mcommerce = new Mcommerce_Model_Mcommerce();
                $mcommerce->find(array("mcommerce_id" => $this->getCart()->getData("mcommerce_id")));
				$mcommerce->getId();


				
				$required_fields = array($this->_('Firstname') => 'firstname', $this->_('Lastname') => 'lastname', $this->_('Email') => 'email', $this->_('Phone') => 'phone');
				
				if((int)$mcommerce->getData("require_address")==1)
				{$required_fields_compl = array($this->_('Street') => 'street', $this->_('Postcode') => 'postcode', $this->_('City') => 'city');
				$required_fields = array_merge($required_fields, $required_fields_compl);
				}
				
				/*hjdc RequireAddress*/
				
				if((int)$mcommerce->getData("age_control")==1)
				{
				$required_fields_birthday = array($this->_('Date of birth') => 'birthday');	
				$required_fields = array_merge($required_fields, $required_fields_birthday);	
				}

				
                $errors = array();
                foreach($required_fields as $label => $field) {
                    if(empty($customer[$field])) $errors[] = $label;
                }
                if(!empty($errors)) {
                    $message = $this->_('Please fill in the following fields:');
                    foreach($errors as $field) {
                        $message .= '<br />- '.$field;
                    }
                    throw new Exception(preg_replace('#<br\s*/?>#i', "\r\n", $this->_($message)));
                }
				
				
				

				
				if((int)$mcommerce->getData("age_control")==1)
				{
                   $dna = strtotime($customer['birthday']);
                   $now = time();
                   $age = date('Y',$now)-date('Y',$dna);
                   if(strcmp(date('md', $dna),date('md', $now))>0) $age--;  
			       if((int)$mcommerce->getData("age_minimum")>$age)
			        {$message_age = $this->_('Minimum age to order :').' '.$mcommerce->getData("age_minimum").' '.$this->_('years old');
				     throw new Exception($this->_($message_age));  
				   }
				}
				

                foreach($customer as $key => $data) {
                    if(empty($data)) $customer[$key] = null;
                }

                if(!empty($customer['street']) AND !empty($customer['postcode']) AND !empty($customer['city'])) {
				  if(!empty($customer['streetc'])) {
                    $address = join(', ', array(
                        $customer['street'],
						$customer['streetc'],
                        $customer['postcode'],
                        $customer['city']
                    ));} else 
					{
                    $address = join(', ', array(
                        $customer['street'],
                        $customer['postcode'],
                        $customer['city']
                    ));}
					

                    $address = str_replace(' ', '+', $address);
                    $url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address";
                    if($coordinates_datas = @file_get_contents($url) AND $coordinates_datas = @json_decode($coordinates_datas)) {
                        if(!empty($coordinates_datas->results[0]->geometry->location)) {
                            $latlng = $coordinates_datas->results[0]->geometry->location;
                            $customer['latitude'] = !empty($latlng->lat) ? $latlng->lat : null;
                            $customer['longitude'] = !empty($latlng->lng) ? $latlng->lng : null;
                        }
                    }
                } else {
                    $customer['latitude'] = null;
                    $customer['longitude'] = null;
                }
                 
                $datas = array(
                    "customer_firstname" =>  $customer["firstname"],
                    "customer_lastname" =>  $customer["lastname"],
                    "customer_email" =>  $customer["email"],
                    "customer_phone" =>  $customer["phone"],
                    "customer_email" =>  $customer["email"],
                    "customer_street" =>  $customer["street"],
					"customer_streetc" =>  $customer["streetc"],
                    "customer_postcode" =>  $customer["postcode"],
                    "customer_city" =>  $customer["city"],
                    "customer_latitude" =>  $customer["latitude"],
                    "customer_longitude" =>  $customer["longitude"],
                    "customer_birthday" =>  $customer["birthday"]
                );
                $this->getCart()->addData($datas)->save();

                $html = array(
				    'test'=> $test,
                    'customer' => $customer,
                    'datas' => $datas,
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