<?php

namespace Push2\Model\Onesignal;

use Core\Model\Base;
use Push2\Model\Onesignal\Targets\AbstractTarget;

/**
 * @method integer getCustomerId()
 */
abstract class PushCapable extends Base
{
    /**
     * @param $customerId
     * @param $title
     * @param $text
     * @param $actionValue
     * @param $valueId
     * @param $appId
     * @return void
     * @throws \Siberian\Exception
     * @throws \Zend_Exception
     * @throws \onesignal\client\ApiException
     */
    public function sendMessageToCustomer($customerId,
                                          $title,
                                          $text,
                                          $actionValue = null,
                                          $valueId = null,
                                          $appId = null)
    {
        $application = (new \Application_Model_Application())->find($appId);
        if (!$application || ($appId === null)) {
            $application = self::sGetApplication();
        }

        $messageValues = [
            'title' => $title,
            'body' => $text,
            'action_value' => $actionValue,
            'value_id' => $valueId,
            'app_id' => $application->getId(),
        ];

        $scheduler = new Scheduler($application);
        $scheduler->buildMessageFromValues($messageValues);
        $scheduler->sendToCustomer($customerId);
    }

    /**
     * @param AbstractTarget $targets
     * @param $title
     * @param $text
     * @param $actionValue
     * @param $valueId
     * @param $appId
     * @return void
     * @throws \Siberian\Exception
     * @throws \Zend_Exception
     * @throws \onesignal\client\ApiException
     */
    public function sendMessageToTargets($targets,
                                         $title,
                                         $text,
                                         $actionValue = null,
                                         $valueId = null,
                                         $appId = null)
    {
        $application = (new \Application_Model_Application())->find($appId);
        if (!$application || ($appId === null)) {
            $application = self::sGetApplication();
        }

        $messageValues = [
            'title' => $title,
            'body' => $text,
            'action_value' => $actionValue,
            'value_id' => $valueId,
            'app_id' => $appId,
        ];

        $scheduler = new Scheduler($application);
        $scheduler->buildMessageFromValues($messageValues);
        $scheduler->send();
    }
}