<?php

class Rss_Application_FeedController extends Application_Controller_Default
{

    public function searchAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {
                if(empty($datas['link']) OR !Zend_Uri::check($datas['link'])) throw new Exception($this->_('Please enter a valid url'));

                $result = Zend_Feed_Reader::findFeedLinks($datas['link']);

                //On a soit un flux directement, soit une URL sans flux trouvable
                if(count($result) == 0) {
                    try {
                        $feed = Zend_Feed_Reader::import($datas['link']);
                        $html = $this->_saveFeed($datas);
                    }
                    //Aucun flux à cette adresse
                    catch(Exception $e) {
                        $html = array(
                            'message' => $this->_("No RSS feed could be found"),
                            'message_button' => 1,
                            'message_loader' => 1
                        );
                    }
                //On a une adresse avec des flux
                } else {
                    $feeds = array();
                    foreach($result as $feed) {
                        if(!empty($feed['href'])) {
                            $feeds[] = $feed['href'];
                        }
                    }
                    if(!isset($datas['feed_id'])) {
                        $id = 'new';
                    } else {
                        $id = $datas['feed_id'];
                    }
                    $html = array('links' => $feeds, 'id' => $id);
                }

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $this->_("No RSS feed could be found"),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function _saveFeed($datas) {

            $html = '';

            try {

                // Test s'il y a une erreur dans la saisie
                if(empty($datas['link']) OR !Zend_Uri::check($datas['link'])) throw new Exception($this->_('Please enter a valid url'));

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving your RSS feed'));

                if(!isset($datas['picture'])) {
                    $datas['picture'] = 0;
                }

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $isNew = true;
                $html = '';
                $feed = new Rss_Model_Feed();
                $feeds = new Rss_Model_Feed();
                $feeds = $feeds->findAll(array(), 'position DESC');
                if($feeds->count() > 0) $last_position = $feeds->seek($feeds->count()-1)->current()->getPosition();
                else $last_position = 0;

                // Si un id est passé en paramètre
                if(!empty($datas['feed_id'])) {
                    // Charge le flux
                    $feed->find($datas['feed_id']);
                    // Si le flux existe mais qu'il n'appartient pas à cette option_value
                    if($feed->getId() AND $feed->getValueId() != $option_value->getId()) {
                        // Envoi l'erreur
                        throw new Exception($this->_('An error occurred while saving your RSS feed'));
                    }
                    $isNew = !$feed->getId();
                    $last_position = $feed->getPosition();
                }
                if(!empty($last_position)) {
                    $datas["position"] = $last_position+1;
                } else {
                    $datas["position"] = 0;
                }

                $feed->setData($datas)->save();

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('RSS feed successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('row_'.$feed->getId(), 'admin_view_default', 'rss/application/feed/edit/row.phtml')
                        ->setCurrentFeed($feed)
                        ->setCurrentOptionValue($option_value)
                        ->toHtml()
                    ;
                }

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            return $html;

    }

    public function sortAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                // Récupère les positions
                $positions = $this->getRequest()->getParam('row');
                if(empty($positions)) throw new Exception($this->_("An error occurred while saving. Please try again later."));

                // Supprime les positions en trop, au cas où...
                foreach($positions as $key => $position) {
                    if(!is_numeric($position)) unset($positions[$key]);
                }

                // Met à jour les positions des flux
                $rss = new Rss_Model_Feed();
                $rss->updatePositions($positions);

                // Renvoie OK
                $html = array('success' => 1);

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

    public function deleteAction() {
        try {

            $id = $this->getRequest()->getParam('id');
            // Met à jour les positions des flux
            $rss = new Rss_Model_Feed();
            $rss->find($id)->delete();

            // Renvoie OK
            $html = array('success' => 1);

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