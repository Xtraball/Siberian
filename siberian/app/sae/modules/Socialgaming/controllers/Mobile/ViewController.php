<?php

use Siberian\Exception;

/**
 * Class Socialgaming_Mobile_ViewController
 */
class Socialgaming_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    public function findallAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $appId = $application->getId();
            $option = $this->getCurrentOptionValue();
            $offset = $request->getParam('offset', 0);

            if (!$option->getId()) {
                throw new Exception(p__('social_gaming', 'This feture does not exists!'));
            }

            $currentGame = (new Socialgaming_Model_Game())->findCurrent($option->getId());

            if (!$currentGame &&
                !$currentGame->getId()) {
                $currentGame->findDefault();
            }

            list($start, $end) = $currentGame->getFromDateToDate();

            $customers = (new LoyaltyCard_Model_Customer_Log())->getBestCustomers(
                $appId,
                $start->toString('y-MM-dd HH:mm:ss'),
                $end->toString('y-MM-dd HH:mm:ss'),
                false,
                $offset);
            $teamLeader = $customers->current();
            $customers->removeCurrent();

            $data = [
                'icon_url' => $this->_getColorizedImage(
                    $option->getIconId(),
                    $application->getBlock('background')->getColor()),
                'game' => [
                    'name' => $currentGame->getName(),
                    'period' => strtoupper($currentGame->getGamePeriodLabel()),
                ],
                'team_leader' => [],
                'collection' => [],
            ];

            if ($teamLeader) {
                $imageUrl = $teamLeader->getImageLink();
                $data['team_leader'] = [
                    'id' => $teamLeader->getId(),
                    'image_url' => $imageUrl ? $request->getBaseUrl() . $imageUrl : null,
                    'name' => $teamLeader->getFirstname() . ' ' . mb_substr($teamLeader->getLastname(), 0, 1, 'UTF-8') . '.',
                    'number_of_points' => $this->_('%s point%s', $teamLeader->getNumberOfPoints(), $teamLeader->getNumberOfPoints() > 1 ? 's' : ''),
                ];
            }

            if ($customers->count()) {
                foreach ($customers as $customer) {
                    $imageUrl = $customer->getImageLink();
                    $data['collection'][] = [
                        'id' => $customer->getId(),
                        'image_url' => $imageUrl ? $request->getBaseUrl() . $imageUrl : null,
                        'name' => $customer->getFirstname() . ' ' . mb_substr($customer->getLastname(), 0, 1, 'UTF-8') . '.',
                        'number_of_points' => $this->_('%s point%s', $customer->getNumberOfPoints(), $customer->getNumberOfPoints() > 1 ? 's' : ''),
                    ];
                }
            }

            $data['page_title'] = $option->getTabbarName();

            $payload = [
                'success' => true,
                'page_title' => $option->getTabbarName(),
                'team_leader' => $data['team_leader'],
                'icon_url' => $data['icon_url'],
                'game' => $data['game'],
                'collection' => $data['collection'],
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

}