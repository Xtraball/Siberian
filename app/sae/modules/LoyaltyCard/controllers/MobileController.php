<?php

class Loyaltycard_MobileController extends Application_Controller_Mobile_Default
{

    public function validateAction() {

        try {

            $html = array();
            if($datas = $this->getRequest()->getPost()) {

                // Récupération de l'option_value en cours
                $option_value = $this->getCurrentOptionValue();

                // Récupération de l'application en cours
                $application = $this->getApplication();

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
                $password->findByPassword($password_entered, $option_value->getId());

                // Test si le mot de passe a été trouvé
                if(!$password->getId()) {
                    // Ajoute une erreur à la carte en cours
                    $card->addError();
                    // Calcul le nombre de tentative restante
                    $only = 3 - $card->getNumberOfError();
                    // S'il reste au moins 1 tentative de saisie, on envoie un message d'erreur
                    if($only > 0) {
                        $html['customer_card_id'] = $card->getCustomerCardId();
                        throw new Exception($this->_('Wrong password.<br />Be careful !<br />%d remaining attempt%s before locking your card.<br />Ask the store person for validating your point', $only, $only > 1 ? 's' : ''));
                    }
                    else {
                        // Sinon, on ferme le clavier et on annonce que la carte est bloquée
                        $html['close'] = true;
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
                        $card->createLog($password->getId(), 0, $nbr);

                        // On renvoie un message de validation
                        $s = $nbr>1?'s':'';
                        $msg = $this->_('Point%s successfully validated', $s, $s);
                        $html = array('ok' => true, 'message' => $msg, 'close' => true);

                    }
                    // Sinon, on cloture la carte
                    else {
                        $card->setIsUsed(1)
                            ->setUsedAt($card->formatDate(null, 'y-MM-dd HH:mm:ss'))
                            ->setValidateBy($password->getId())
                            ->save()
                        ;
                        $html = array('ok' => true, 'message' => $this->_('You just finished your card'), 'close' => true);
                    }

                    if($this->getSession()->getCustomer()->canPostSocialMessage()) {
                        $html['canPostMessage'] = true;
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

}