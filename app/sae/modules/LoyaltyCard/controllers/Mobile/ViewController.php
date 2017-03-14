<?php

class Loyaltycard_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    public function findallAction() {

        if($this->getRequest()->getParam('value_id')) {
            $data = array();
            $fcc = new LoyaltyCard_Model_Customer();
            $customer_id = $this->getSession()->getCustomerId() | 0;
            $cards = $fcc->findAllByOptionValue($this->getCurrentOptionValue()->getId(), $customer_id);
            $current_card = new LoyaltyCard_Model_Customer();
            $promotions = array();
            $cardIsLocked = false;

            foreach($cards as $card) {
                if($card->getIsLocked()) {
                    $cardIsLocked = true;
                } elseif($card->getNumberOfPoints() == $card->getMaxNumberOfPoints()) {
                    $promotions[] = $card;
                } elseif($card->getNumberOfPoints() < $card->getMaxNumberOfPoints()) {
                    $current_card = $card;
                }
            }

            $data["promotions"] = array();
            foreach($promotions as $promotion) {
                $data["promotions"][] = array(
                    "id" => $promotion->getId(),
                    "advantage" => $promotion->getAdvantage()
                );
            }

            $data["card"] = array();
            if($current_card->getCardId()) {

                $data["card"] = array(
                    "id" => $current_card->getId(),
                    "is_visible" => (bool) $current_card->getCardId(),
                    "name" => $current_card->getName(),
                    "advantage" => $current_card->getAdvantage(),
                    "conditions" => $current_card->getConditions(),
                    "number_of_points" => $current_card->getNumberOfPoints(),
                    "max_number_of_points" => $current_card->getMaxNumberOfPoints()
                );

                $data["points"] = $this->_getPoints($current_card);

            }

            $_pictos = $this->_getPictos($current_card);
            $data["picto_urls"] = array(
                "normal_url" => $_pictos["inactive"],
                "validated_url" => $_pictos["active"],
            );

            $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
            $data["pad_title"] = $this->_("Enter the password");
            $data["card_is_locked"] = $cardIsLocked;

            $tc = new Application_Model_Tc();
            $tc->findByType($this->getApplication()->getId(), "loyaltycard");
            $text = $tc->getText();
            $data["tc_id"] = !empty($text) ? $tc->getId() : null;

            $this->_sendHtml($data);
        }

    }

    public function validateAction() {

        try {

            $html = array();
            if($datas = Zend_Json::decode($this->getRequest()->getRawBody())) {

                $application_id = $this->getApplication()->getAppId();

                // Récupération de l'option_value en cours
                $option_value = $this->getCurrentOptionValue();

                // Récupération du client en cours
                $customer_id = $this->getSession()->getCustomerId();
                // Si le client n'est pas connecté
                if(empty($customer_id)) throw new Exception($this->_('You must be logged in to validate points'));

                $customer_card_id = $datas['customer_card_id'];

                // Récupération de la carte de fidélité de l'utilisateur en cours
                $card = new LoyaltyCard_Model_Customer();
                // Récupère la carte du client ou, à défaut, en créé une nouvelle
                $cards = $card->findAllByOptionValue($option_value->getId(), $customer_id);

                foreach($cards as $tmp_card) {
                    // Si la carte n'existe pas, customer_card_id == 0
                    if($tmp_card->getCustomerCardId() == $customer_card_id) $card = $tmp_card;
                }

                // Déclaration des variables annexes
                $password_entered = $datas['password'];
                $nbr = !empty($datas['number_of_points']) ? $datas['number_of_points'] : 1;

                // Ou si le mot de passe est vide ou non numérique
                if($card->getValueId() != $option_value->getId() OR empty($password_entered) OR !preg_match('/[0-9]/', $nbr)) {
                    throw new Exception($this->_('An error occurred while validating point. Please try again later.'));
                }

                // Récupération du mot de passe
                $password = new LoyaltyCard_Model_Password();

                if($datas["mode_qrcode"]) {
                    $password->findByUnlockCode($password_entered, $option_value->getId());
                } else {
                    $password->findByPassword($password_entered, $option_value->getId());
                }

                // Test si le mot de passe a été trouvé
                if(!$password->getId()) {
                    // Ajoute une erreur à la carte en cours
                    $card->addError();
                    // Calcul le nombre de tentative restante
                    $only = 3 - $card->getNumberOfError();
                    // S'il reste au moins 1 tentative de saisie, on envoie un message d'erreur
                    if($only > 0) {
                        $html['customer_card_id'] = $card->getCustomerCardId();
                        throw new Exception($this->_('Wrong password. Be carefull! %d remaining attempt%s before locking your card. Ask the store person for validating your point', $only, $only > 1 ? 's' : ''));
                    }
                    else {
                        // Sinon, on ferme le clavier et on annonce que la carte est bloquée
                        $html['close_pad'] = true;
                        $html['card_is_locked'] = true;
                        throw new Exception($this->_('You have exceeded the number of attempts to validate points. Your card is locked for 24h'));
                    }
                }
                // Sinon on valide (le point ou la carte)
                else {

                    // S'il reste des points à valider
                    if($card->getNumberOfPoints() < $card->getMaxNumberOfPoints()) {
                        // On met à jour la carte de fidélité du client
                        $card->setNumberOfPoints($card->getNumberOfPoints()+$nbr)
                            ->setCustomerId($this->getSession()->getCustomerId())
                            ->setNumberOfError(0)
                            ->setLastError(null)
                            ->save()
                        ;
                        // On log l'employé ayant validé le ou les points
                        $card->createLog($password->getId(), $nbr);

                        // On renvoie un message de validation
                        $s = $nbr>1?'s':'';
                        $msg = $this->_('Point%s successfully validated', $s, $s);
                        $html = array(
                            'success' => true,
                            'message' => $msg,
                            'close_pad' => true,
                            'customer_card_id' => $card->getCustomerCardId(),
                            'number_of_points' => $card->getNumberOfPoints()
                        );

                    }
                    // Sinon, on cloture la carte
                    else {
                        $card->setIsUsed(1)
                            ->setUsedAt($card->formatDate(null, 'y-MM-dd HH:mm:ss'))
                            ->setValidateBy($password->getId())
                            ->save()
                        ;
                        $html = array(
                            'success' => true,
                            'message' => $this->_('You just finished your card'),
                            'promotion_id_to_remove' => $card->getId(),
                            'close_pad' => true
                        );
                    }
                }
            }

        }
        catch(Exception $e) {
            $html['error'] = 1;
            $html['message'] = $e->getMessage();
        }

        $this->_sendHtml($html);

    }

    public function unlockbyqrcodeAction() {

        try {

            $html = array();
            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

                $application_id = $this->getApplication()->getAppId();

                // Récupération de l'option_value en cours
                $option_value = $this->getCurrentOptionValue();

                // Récupération du client en cours
                $customer_id = $this->getSession()->getCustomerId();
                // Si le client n'est pas connecté
                if(empty($customer_id)) throw new Exception($this->_('You must be logged in to validate points'));

                $customer_card_id = $data['customer_card_id'];

                // Récupération de la carte de fidélité de l'utilisateur en cours
                $card = new LoyaltyCard_Model_Customer();
                // Récupère la carte du client ou, à défaut, en créé une nouvelle
                $cards = $card->findAllByOptionValue($option_value->getId(), $customer_id);

                foreach($cards as $tmp_card) {
                    // Si la carte n'existe pas, customer_card_id == 0
                    if($tmp_card->getCustomerCardId() == $customer_card_id) $card = $tmp_card;
                }

                // Déclaration des variables annexes
                $password_entered = $data['password'];
                $nbr = !empty($data['number_of_points']) ? $data['number_of_points'] : 1;

                // Récupération du mot de passe
                $password = new LoyaltyCard_Model_Password();

                $password->findByUnlockCode($password_entered, $option_value->getId());

                // Test si le mot de passe a été trouvé
                if(!$password->getId()) {
                    throw new Exception($this->_('An error occurred with your QRCode.'));
                }
                // Sinon on valide (le point ou la carte)
                else {

                    // S'il reste des points à valider
                    if($card->getNumberOfPoints() < $card->getMaxNumberOfPoints()) {
                        // On met à jour la carte de fidélité du client
                        $card->setNumberOfPoints($card->getNumberOfPoints()+$nbr)
                            ->setCustomerId($this->getSession()->getCustomerId())
                            ->setNumberOfError(0)
                            ->setLastError(null)
                            ->save()
                        ;
                        // On log l'employé ayant validé le ou les points
                        $card->createLog($password->getId(), $nbr);

                        // On renvoie un message de validation
                        $s = $nbr>1?'s':'';
                        $msg = $this->_('Point%s successfully validated', $s, $s);
                        $html = array(
                            'success' => true,
                            'message' => $msg,
                            'close_pad' => true,
                            'customer_card_id' => $card->getCustomerCardId(),
                            'number_of_points' => $card->getNumberOfPoints()
                        );

                    }
                    // Sinon, on cloture la carte
                    else {
                        $card->setIsUsed(1)
                            ->setUsedAt($card->formatDate(null, 'y-MM-dd HH:mm:ss'))
                            ->setValidateBy($password->getId())
                            ->save()
                        ;
                        $html = array(
                            'success' => true,
                            'message' => $this->_('You just finished your card'),
                            'promotion_id_to_remove' => $card->getId(),
                            'close_pad' => true
                        );
                    }

                }
            }

        }
        catch(Exception $e) {
            $html['error'] = 1;
            $html['message'] = $e->getMessage();
        }

        $this->_sendHtml($html);

    }
    
    protected function _getPoints($current_card) {

        $regular_image_url = $this->_getImage('pictos/point.png');
        $validated_image_url = $this->_getColorizedImage($this->_getImage('pictos/point_validated.png', true), $this->getApplication()->getBlock('connect_button')->getBackgroundColor());

        /** If images */
        $image_active = ($current_card->getImageActive()) ? $this->getRequest()->getBaseUrl()."/images/application".$current_card->getImageActive() :  $this->getRequest()->getBaseUrl().$validated_image_url;
        $image_inactive = ($current_card->getImageInactive()) ? $this->getRequest()->getBaseUrl()."/images/application".$current_card->getImageInactive() :  $this->getRequest()->getBaseUrl().$regular_image_url;

        $points = array();

        for($i = 0; $i < $current_card->getMaxNumberOfPoints(); $i++) {

            $is_validated = false;

            if($i < $current_card->getNumberOfPoints()) {
                $is_validated = true;
            }

            $points[] = array(
                "is_validated" => $is_validated,
                "image_url" => $image_inactive,
                "validated_image_url" => $image_active,
            );
        }

        return $points;
    }

    protected function _getPictos($current_card) {

        $regular_image_url = $this->_getImage('pictos/point.png');
        $validated_image_url = $this->_getColorizedImage($this->_getImage('pictos/point_validated.png', true), $this->getApplication()->getBlock('connect_button')->getBackgroundColor());

        /** If images */
        $image_active = ($current_card->getImageActive()) ? $this->getRequest()->getBaseUrl()."/images/application".$current_card->getImageActive() :  $this->getRequest()->getBaseUrl().$validated_image_url;
        $image_inactive = ($current_card->getImageInactive()) ? $this->getRequest()->getBaseUrl()."/images/application".$current_card->getImageInactive() :  $this->getRequest()->getBaseUrl().$regular_image_url;

        return array(
            "active" => $image_active,
            "inactive" => $image_inactive,
        );
    }

}