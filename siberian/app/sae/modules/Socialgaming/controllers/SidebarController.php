<?php

class Socialgaming_SidebarController extends Core_Controller_Default
{

    public function loadchallengersAction() {

        // Sociel Gaming
        $admin = $this->getCurrentAdmin();

        $current_game = new Socialgaming_Model_Game();
        $current_game->findCurrent($admin->getId());

        if(!$current_game->getId()) {
            $current_game->findDefault();
        }

        list($start, $end) = $current_game->getFromDateToDate();


        $log = new LoyaltyCard_Model_Customer_Log();
        $customers = $log->getBestCustomers(null, $start->toString('y-MM-dd HH:mm:ss'), $end->toString('y-MM-dd HH:mm:ss'), false);
        $team_leader = $customers->current();
        $customers->removeCurrent();

        $partial = $this->getLayout()->setBaseRender('content', 'html/sidebar/social_gaming.phtml', 'core_view_default');
        $partial->setCurrentGame($current_game)->setTeamLeader($team_leader)->setCustomers($customers);
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

}