<?php

class Socialgaming_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = array();
            $datas['value_id'] = $this->getCurrentOptionValue()->getId();

            $game = new Socialgaming_Model_Game();
            $current_game = new Socialgaming_Model_Game();
            $current_game->findCurrent($this->getCurrentOptionValue()->getId());

            $next_game = new Socialgaming_Model_Game();
            $next_game->findNext($this->getCurrentOptionValue()->getId());

            try {

                // Sauvegarde le jeu
                if($current_game->getId()) {
                    $next_game->setData($datas)->save();

                    // Si le jeu n'a pas de date de fin
                    if(!$current_game->getEndAt()) {
                        // Met à jour la date de fin du jeu en cours
                        $current_game->setEndAt()->save();
                    }

                }
                else {
                    $current_game->setData($datas)->save();
                }

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('Game successfully saved'),
                    'message_button' => 0,
                    'message_timeout' => 3,
                    'message_loader' => 1
                );

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $socialgaming = new Socialgaming_Model_Game();
            $result = $socialgaming->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "socialgaming-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}