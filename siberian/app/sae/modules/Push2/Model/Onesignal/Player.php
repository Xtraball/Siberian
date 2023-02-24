<?php

namespace Push2\Model\Onesignal;

require_once path('/lib/onesignal/vendor/autoload.php');

use Core_Model_Default as BaseModel;

/**
 * Class Message
 * @package Push2\Model\Onesignal
 *
 * @method Db\Table\Player getTable()
 */
class Player extends BaseModel
{
    public $_db_table = Db\Table\Player::class;

    /**
     * @param $customer_id
     * @param $message
     * @return void
     * @throws \Siberian\Exception
     * @throws \Zend_Exception
     * @throws \onesignal\client\ApiException
     */
    public function sendPush($customer_id, $message) {
        $player = (new Player())->find($customer_id, 'customer_id');
        if (!$player || !$player->getId()) {
            throw new \Siberian\Exception(__('Player not found'));
        }
        $player_id = $player->getPlayerId();

        $application = (new \Application_Model_Application())->find($player->getAppId());
        if (!$application || !$application->getId()) {
            throw new \Siberian\Exception(__('Application not found'));
        }

        $message->clearTargets();
        $message->addTarget(new Targets\Player($player_id));

        $notification = new Notification($application->getOnesignalAppId(), $application->getOnesignalAppKeyToken());
        $notification->setApplication($application);
        $notification->regularPush($message);
        $notification->sendNotification();
    }
}