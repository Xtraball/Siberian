<?php

class Rss_Application_FeedController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = array(
        "search" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ),
        ),
        "delete" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ),
        )
    );

    public function searchAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {
                if(empty($datas['link']) OR !Zend_Uri::check($datas['link'])) throw new Exception(__('Please enter a valid url'));

                try {
                    $rss_feed = Zend_Feed_Reader::import($datas['link']);
                } catch(Zend_Feed_Exception $e) {
                    $rss_feed = null;
                }


                $feeds = array();
                try {
                    $result = Zend_Feed_Reader::findFeedLinks($datas['link']);

                    foreach($result as $feed) {
                        if(
                            empty($feed['href']) ||
                            $feed['type'] == "text/html" ||
                            ($rss_feed && $feed['href'] == $rss_feed->getLink())
                        ) continue;
                        $feeds[] = $feed['href'];
                    }

                    if($rss_feed && !array_search($rss_feed->getFeedLink(), $feeds) && count($feeds) > 0) {
                        $feeds = array_merge([$rss_feed->getFeedLink()], $feeds);
                    }
                } catch(Zend_Feed_Exception $e) {
                    // Ignore
                }

                //On a soit un flux directement, soit une URL sans flux trouvable
                if(count($feeds) == 0) {
                    if($rss_feed) {
                        $html = $this->_saveFeed($datas);
                    } else {
                        //Aucun flux à cette adresse
                        $html = array(
                            'message' => __("No RSS feed could be found"),
                            'message_button' => 1,
                            'message_loader' => 1
                        );
                    }
                //On a une adresse avec des flux
                } else {
                    if(!isset($datas['feed_id'])) {
                        $id = 'new';
                    } else {
                        $id = $datas['feed_id'];
                    }
                    $html = array('links' => $feeds, 'id' => $id);
                }

            } catch(Exception $e) {
                $html = array(
                    'message' => __("No RSS feed could be found"),
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
                if(empty($datas['link']) OR !Zend_Uri::check($datas['link'])) throw new Exception(__('Please enter a valid url'));

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception(__('An error occurred while saving your RSS feed'));

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
                        throw new Exception(__('An error occurred while saving your RSS feed'));
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

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $html = array(
                    'success' => 1,
                    'success_message' => __('RSS feed successfully saved'),
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
                if(empty($positions)) throw new Exception(__("An error occurred while saving. Please try again later."));

                // Supprime les positions en trop, au cas où...
                foreach($positions as $key => $position) {
                    if(!is_numeric($position)) unset($positions[$key]);
                }

                // Met à jour les positions des flux
                $rss = new Rss_Model_Feed();
                $rss->updatePositions($positions);

                // Renvoie OK
                $html = array('success' => 1);

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

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

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

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

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $rss = new Rss_Model_Feed();
            $result = $rss->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "rss-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}
