<?php

class Socialgaming_MobileController extends Application_Controller_Mobile_Default
{

    public function viewAction() {

        // Sociel Gaming
        $app = $this->getApplication();
        $option = $this->getCurrentOptionValue();

        $current_game = new Socialgaming_Model_Game();
        $current_game->findCurrent($option->getId());

        if(!$current_game->getId()) {
            $current_game->findDefault();
        }

        list($start, $end) = $current_game->getFromDateToDate();


        $log = new LoyaltyCard_Model_Customer_Log();
        $customers = $log->getBestCustomers($app->getId(), $start->toString('y-MM-dd HH:mm:ss'), $end->toString('y-MM-dd HH:mm:ss'), false);
        $team_leader = $customers->current();
        $customers->removeCurrent();

        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $this->getLayout()->getPartial('content')->setCurrentGame($current_game)->setTeamLeader($team_leader)->setCustomers($customers);

        $html = array('html' => $this->getLayout()->render());
        if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        $html['title'] = $option->getTabbarName();
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}