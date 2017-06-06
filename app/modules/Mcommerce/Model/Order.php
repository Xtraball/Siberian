<?php

class Mcommerce_Model_Order extends Core_Model_Default {
 
    /**
     * Store du panier
     *
     * @var Mcommerce_Model_Store
     */
    protected $_store;

    /**
     * Lignes du panier
     *
     * @var array
     */
    protected $_lines;

    /**
     * Statuts disponibles de la commande
     *
     * @var array
     */
    protected static $_statuses = array(
        -1 => 'Cancelled',
        1 => 'Waiting for payment',
        2 => 'Paid',
        3 => 'Done',
    );

    const CANCEL_STATUS = -1;
    const DEFAULT_STATUS = 1;
    const PAID_STATUS = 2;
    const DONE_STATUS = 3;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Order';
        return $this;
    }

	
	/*address hjdc*/
    public function getLastOrder($mcommerce_id,$customer_id) {
	
			$last_customer_order = $this->findAll(array('mcommerce_id' => $mcommerce_id, 'customer_id' => $customer_id), 'order_id DESC', array('limit' => 1))->current();
			 if($last_customer_order AND $last_customer_order->getId()) {
			   $order = $this->find($last_customer_order->getId());
			   return array("customer_email" => $order->getData('customer_email'),
			                "customer_firstname" => $order->getData('customer_firstname'),
							"customer_lastname" => $order->getData('customer_lastname'),
							"customer_street" => $order->getData('customer_street'),
							"customer_streetc" => $order->getData('customer_streetc'),
							"customer_postcode" => $order->getData('customer_postcode'),
							"customer_city" => $order->getData('customer_city'),
							"customer_phone" => $order->getData('customer_phone'),
							"customer_birthday" => $order->getData('customer_birthday'),
							);
            }
			return null;
    }
	/*address hjdc*/
	
	
    public function fromCart($cart) {

        $this->addData($cart->getData())->unsId();
        $delivery_method = new Mcommerce_Model_Delivery_Method();
        $delivery_method->find($cart->getDeliveryMethodId());
        if($delivery_method->getId()) {
            $this->setDeliveryMethod($delivery_method->getName());
        }

        $payment_method = new Mcommerce_Model_Payment_Method();
        $payment_method->find($cart->getPaymentMethodId());
        if($payment_method->getId()) {
            $this->setPaymentMethod($payment_method->getName());
        }

        $toUnset = array('line_id', 'cart_id');
        $toUnset = array_combine($toUnset, $toUnset);

        foreach($cart->getLines() as $cart_line) {
            $line = new Mcommerce_Model_Order_Line();
            $line_datas = array_diff_key($cart_line->getData(), $toUnset);
            $line->addData($line_datas);
            $line->setCartLineId($cart_line->getId());
            $line->unsId();
            $this->addLine($line);
        }

        return $this;

    }

    public function save() {

        if(!$this->getStatusId()) {
            $this->setStatusId(self::DEFAULT_STATUS);
        }
        if(!$this->getNumber()) {
            $this->setNumber();
        }

        parent::save();

        if($this->_lines) {
            foreach($this->_lines as $line) {
                $line->setOrderId($this->getId())
                    ->save()
                ;
            }
        }
    }

    public static function getStatuses() {

        $statuses = array();
        foreach(self::$_statuses as $key => $status) {
            $statuses[] = new Core_Model_Default(array(
                'id' => $key,
                'label' => parent::_($status)
            ));
        }

        return $statuses;
    }

    public function getStatus() {
        return !empty(self::$_statuses[$this->getStatusId()]) ? parent::_(self::$_statuses[$this->getStatusId()]) : '';
    }

    public function setNumber() {
        $last_order = $this->findAll(array('mcommerce_id' => $this->getMcommerceId(), 'store_id' => $this->getStoreId()), 'order_id DESC', array('limit' => 1))->current();
        $last_number = 0;

        if($last_order AND $last_order->getId()) {
            $last_number = intval(preg_replace('/[^0-9]/', '', $last_order->getNumber()));
        }

        $this->setData('number', 'O'.str_pad(++$last_number, 7, 0, STR_PAD_LEFT));

        return $this;
    }

    /**
     * Ajoute une ligne en mémoire
     *
     * @param Mcommerce_Model_Order_Line $line
     * @return Mcommerce_Model_Order
     */
    public function addLine($line) {
        if(!$this->getLine($line->getId())) {
            $this->getLines()->addRow(-1, $line);
        }
        return $this;
    }

    /**
     * Supprime une ligne en mémoire
     *
     * @return \Mcommerce_Model_Order
     */
    public function removeLine($line_id) {
        foreach($this->getLines() as $pos => $line) {
            if($line->getId() == $line_id) $this->getLines()->removeRow($pos);
        }
        return $this;
    }

    /**
     * Récupère une ligne en mémoire
     *
     * @param type $line_id
     * @return mixed null | Mcommerce_Model_Order_Line
     */
    public function getLine($line_id) {
        $line = $this->getLines()->findById($line_id);
        return $line->getId() ? $line : null;
    }

    /**
     * Récupère les lignes du panier en base
     *
     * @return Siberian_Db_Table_Rowset Collection de lignes du panier
     */
    public function getLines() {

        if(!$this->_lines) {
            $line = new Mcommerce_Model_Order_Line();
            $this->_lines = $line->findAll(array('order_id' => $this->getId()));
        }

        return $this->_lines;
    }

    /**
     * Récupère le store de la commande
     *
     * @return Mcommerce_Model_Store
     */
    public function getStore() {

        if(!$this->_store) {
            $this->_store = new Mcommerce_Model_Store();
            $this->_store->find($this->getStoreId());
        }

        return $this->_store;

    }

    public function getPdf() {

        // Define the font styles
        $font_regular = Zend_Pdf_Font::fontWithPath(Zend_Pdf_Font::FONT_DEJAVUSANS);
        $font_bold = Zend_Pdf_Font::fontWithPath(Zend_Pdf_Font::FONT_DEJAVUSANS_BOLD);

        // Create a blank PDF, define the color's lines and the ordinate
        $pdf = new Siberian_Pdf();
        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $page->setLineColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $pdf->pages[] = $page;
        $y = 760;

        // Customer information
        $page->setFont($font_bold, 20);
        $page->drawText($this->_("Client"), 50, $y); $y -= 30;

        $page->setFont($font_regular, 12);
        $page->drawText($this->getCustomerFirstname() . " " . $this->getCustomerLastname(), 50, $y);$y-=15;
        if($this->getCustomerStreet() AND $this->getCustomerCity()) {
            $page->drawText($this->getCustomerStreet(), 50, $y);$y-=15;
			if($this->getCustomerStreetc()) {$page->drawText($this->getCustomerStreetc(), 50, $y);$y-=15;}
            $page->drawText($this->getCustomerPostcode() . ", " . $this->getCustomerCity(), 50, $y);$y-=15;
        }
        $page->drawText($this->getCustomerPhone(), 50, $y); $y -= 45;


        // Order general information
        $page->setFont($font_bold, 20);
        $page->drawText($this->_("Order details"), 50, $y); $y -= 30;

        $page->setFont($font_bold, 12);
        $page->drawText($this->_("Order Number"), 50, $y);
        $page->drawText($this->_("Delivery Method"), 250, $y);
        $page->drawText($this->_("Payment Method"), 450, $y);$y-=15;

        $page->setFont($font_regular, 12);
        $page->drawText($this->getNumber(), 50, $y);
        $page->drawText($this->getDeliveryMethod(), 250, $y);
        $page->drawText($this->getPaymentMethod(), 450, $y);$y-=45;
		
		
 // Delivery details
    if($this->getDeliveryDatetime()!=null || $this->getDeliveryComment()!=null)
	{
	    $page->setFont($font_bold, 20);
        $page->drawText($this->_("Delivery details"), 50, $y); $y -= 30;

        $page->setFont($font_regular, 12);
        if($this->getDeliveryDatetime()!=null) 
		   $page->drawText($this->getDeliveryDatetime(), 50, $y);$y-=15;
		if($this->getDeliveryComment()!=null)   
		    {
			
               $lines = array();
               $charsPerLine = 70;
               foreach (explode(nl2br("\n"), nl2br($this->getDeliveryComment())) as $line) 
			     {$lines = array_merge($lines, explode("\n",wordwrap($line, $charsPerLine, "\n")));}
			   
			   foreach ( $lines as $i=>$line ) {$page->drawText($line, 50, $y);$y-=15;}			
			}		
        $y -= 45;		
    }
	
	
        // Order items
        $page->setFont($font_bold, 11);
        $page->drawText($this->_("Product"), 50, $y);
        $page->drawText($this->_("Unit Price"), 380, $y);
        $page->drawText($this->_("Qty"), 460, $y);
        $page->drawText($this->_("Total"), 500, $y);$y-=10;
        $page->drawLine(50, $y, 550, $y);$y-=1;
        $page->drawLine(50, $y, 550, $y);$y-=15;

        foreach ($this->getLines() as $line) {

            $format = unserialize($line->getFormat());
            $text_format = isset($format['title']) ? $this->_("Format:") . " " . $format['title'] : "";

            $y_ref = $y;
            $max_text_length = 65;
            $name = $line->getName();
            $page->setFont($font_regular, 11);

            $content_line = "";

            // Item
            if (strlen($name) > $max_text_length) {

                $words = explode(' ', $name);
                for ($i = 0; $i < count($words); $i++) {

                    $word = $words[$i];
                    if (strlen($content_line) + strlen($word) < $max_text_length) {
                        $content_line .= "$word ";
                    } else {
                        $page->drawText($content_line, 55, $y_ref);
                        $content_line = $word;
                        $y_ref -= 15;
                    }
                }
            } else {
                $content_line = $name;
            }

            $page->drawText($content_line, 55, $y_ref);

            $page->drawText(html_entity_decode($line->getFormattedBasePriceInclTax(), ENT_COMPAT, "UTF-8"), 380, $y)
                ->drawText($line->getQty(), 467, $y)
                ->drawText(html_entity_decode(count($line->getOptions()) ? $line->getFormattedBasePriceInclTax() : $line->getFormattedTotalInclTax(), ENT_COMPAT, "UTF-8"), 500, $y)
            ;

            // Options
            if(count($line->getOptions())) {

                $y_ref -= 15;
                $page->setFont($font_regular, 9);
                foreach($line->getOptions() as $option) {
                    $page->drawText("+ {$option->getName()}", 55, $y_ref)
                        ->drawText(html_entity_decode($option->getFormattedPriceInclTax(), ENT_COMPAT, "UTF-8"), 382, $y_ref)
                        ->drawText($option->getQty(), 467, $y_ref)
                        ->drawText(html_entity_decode($option->formatPrice($option->getPriceInclTax() * $option->getQty()), ENT_COMPAT, "UTF-8"), 502, $y_ref)
                    ;
                }

            }

            if(!empty($text_format)) {
                $y_ref -= 15;
                $page->drawText($text_format, 55, $y_ref);
            }

            $y_ref -= 10;
            $page->drawLine(50, $y_ref, 550, $y_ref);
            $y = $y_ref - 15;
        }

        $y += 14;
        $page->drawLine(50, $y, 550, $y);

        // Totals
        $y -= 45;
        $page->setFont($font_bold, 12);
        $page->drawText($this->_("Total"), 50, $y);$y-=10;
        $page->drawLine(50, $y, 550, $y);$y--;
        $page->drawLine(50, $y, 550, $y);$y-=15;

        // Titles
        $y_ref = $y;
        $page->setFont($font_bold, 11);
        $page->drawText($this->_("Subtotal"), 50, $y_ref);$y_ref-=15;
        $padding = 0;
        if($this->getDeliveryCost() > 0) {
            $page->drawText($this->_("Delivery Fees"), 50, $y_ref);$y_ref-=15;
            $page->drawText($this->_("Total Excl. Tax"), 50, $y_ref);$y_ref-=15;
            $padding = 30;
        }
        $page->drawText($this->_("Total Tax"), 50, $y_ref);$y_ref-=15;
        $page->drawText($this->_("Total"), 50, $y_ref);

        // Values
        $y_ref = $y;
        $page->setFont($font_regular, 11);
        $page->drawText(html_entity_decode($this->getFormattedSubtotalExclTax(), ENT_COMPAT, "UTF-8"), 502, $y_ref);$y_ref-=15;
        if($this->getDeliveryCost() > 0) {
            $page->drawText(html_entity_decode($this->getFormattedDeliveryCost(), ENT_COMPAT, "UTF-8"), 502, $y_ref);$y_ref-=15;
            $page->drawText(html_entity_decode($this->getFormattedTotalExclTax(), ENT_COMPAT, "UTF-8"), 502, $y_ref);$y_ref-=15;
        }
        $page->drawText(html_entity_decode($this->getFormattedTotalTax(), ENT_COMPAT, "UTF-8"), 502, $y_ref);$y_ref-=15;
        $page->drawText(html_entity_decode($this->getFormattedTotal(), ENT_COMPAT, "UTF-8"), 502, $y_ref);

        if($this->getPaidAmount() && !$this->getHidePaidAmount()) {
            // Delivery Amount Client
            $y -= (45+$padding);
            $page->drawLine(252, $y, 550, $y);$y--;
            $page->drawLine(252, $y, 550, $y);$y-=15;

            // Titles
            $y_ref = $y;
            $page->setFont($font_bold, 11);
            $page->drawText($this->_("Client will pay"), 302, $y_ref);$y_ref-=15;
            $page->drawText($this->_("Remaining due"), 302, $y_ref);

            // Values
            $y_ref = $y;
            $page->setFont($font_regular, 11);
            $page->drawText(html_entity_decode($this->getFormattedPaidAmount(), ENT_COMPAT, "UTF-8"), 502, $y_ref);$y_ref-=15;
            $page->drawText(html_entity_decode($this->formatPrice($this->getPaidAmount() - $this->getTotal()), ENT_COMPAT, "UTF-8"), 502, $y_ref);
        }

        return $pdf;
    }

    /**
     * Récupère le mode de livraison traduit
     *
     * @return string
     */
    public function getDeliveryMethod() {
        return $this->_($this->getData('delivery_method'));
    }

    public function sendToCustomer() {

        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->getLayoutInstance()
            ->loadEmail('mcommerce', 'send_order_to_customer');

        $layout->getPartial('content_email')->setCurrentOrder($this);
        $content = $layout->render();

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($content);
        $mail->setFrom($this->getStore()->getEmail(), $this->_('%s - Customer Service', $this->getStore()->getName()));
        $mail->addTo($this->getCustomerEmail(), $this->getCustomerFirstname() . ' ' . $this->getCustomerLastname());
        $mail->setSubject($this->_('Order confirmation').' : '.$this->getNumber());
        $mail->send();

        return $this;

    }

    public function sendToStore() {

        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->getLayoutInstance()
            ->loadEmail('mcommerce', 'send_order_to_store');

        $layout->getPartial('content_email')->setCurrentOrder($this);
        $content = $layout->render();

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($content);
        $mail->setFrom($this->getStore()->getEmail(), $this->_('Customer Service'));
        $mail->addTo($this->getStore()->getEmail(), $this->getStore()->getName());
        $mail->setSubject($this->_("New order from the application").' : '.$this->getNumber());
        $mail->send();

        $printer = $this->getStore()->getPrinter();
        if($printer->getId()) {

            try {

                $mail = new Zend_Mail('UTF-8');
                $mail->setFrom($this->getStore()->getEmail(), $this->_('Customer Service'));
                $mail->addTo($printer->getEmail(), $this->getStore()->getName());
                $mail->setSubject($this->_("New order from the application").' : '.$this->getNumber());
                $mail->setBodyHtml("");
                $mail->createAttachment(
                    $this->getPdf()->render(),
                    Zend_Mime::TYPE_OCTETSTREAM,
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    "order.pdf"
                );
                $mail->send();

            } catch(Exception $e) {
                $logger = Zend_Registry::get("logger");
                $logger->sendException("- Error when generating the MCommerce PDF order: \n\n".print_r($e, true), "mcommerce_pdf_order_", false);
            }

        }


        return $this;

    }

}
