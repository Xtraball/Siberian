<?php

/**
 * Class Socialgaming_Mobile_ViewController
 */
class Socialgaming_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    public function findallAction()
    {

        if ($this->getRequest()->getParam('value_id')) {
            // Social Gaming
            $application = $this->getApplication();
            $option = $this->getCurrentOptionValue();

            $offset = $this->getRequest()->getParam('offset', 0);

            $current_game = new Socialgaming_Model_Game();
            $current_game->findCurrent($option->getId());

            if (!$current_game->getId()) {
                $current_game->findDefault();
            }

            list($start, $end) = $current_game->getFromDateToDate();

            $log = new LoyaltyCard_Model_Customer_Log();
            $customers = $log->getBestCustomers($application->getId(), $start->toString('y-MM-dd HH:mm:ss'), $end->toString('y-MM-dd HH:mm:ss'), false, $offset);
            $team_leader = $customers->current();
            $customers->removeCurrent();

            $data = [
                "icon_url" => $this->_getColorizedImage($option->getIconId(), $application->getBlock('background')->getColor()),
                "game" => [
                    "name" => $current_game->getName(),
                    "period" => strtoupper($current_game->getGamePeriodLabel()),
                ],
                "team_leader" => [],
                "collection" => [],
            ];

            if ($team_leader) {
                $image_url = $team_leader->getImageLink();
                $data["team_leader"] = [
                    "id" => $team_leader->getId(),
                    "image_url" => $image_url ? $this->getRequest()->getBaseUrl() . $image_url : null,
                    "name" => $team_leader->getFirstname() . ' ' . mb_substr($team_leader->getLastname(), 0, 1, "UTF-8") . '.',
                    "number_of_points" => $this->_('%s point%s', $team_leader->getNumberOfPoints(), $team_leader->getNumberOfPoints() > 1 ? 's' : ''),
                ];
            }


            if ($customers->count()) {
                foreach ($customers as $customer) {
                    $image_url = $customer->getImageLink();
                    $data["collection"][] = [
                        "id" => $customer->getId(),
                        "image_url" => $image_url ? $this->getRequest()->getBaseUrl() . $image_url : null,
                        "name" => $customer->getFirstname() . ' ' . mb_substr($customer->getLastname(), 0, 1, "UTF-8") . '.',
                        "number_of_points" => $this->_('%s point%s', $customer->getNumberOfPoints(), $customer->getNumberOfPoints() > 1 ? 's' : ''),
                    ];
                }
            }

            $data['page_title'] = $option->getTabbarName();

            $this->_sendJson($data);

        }
    }

}