<?php

namespace Push2\Model\Onesignal;

require_once path('/lib/onesignal/vendor/autoload.php');

use Push2\Model\Onesignal\Targets\AbstractTarget;
use Push2\Model\Onesignal\Targets\Segment;

use Core_Model_Default as BaseModel;

/**
 * Class Message
 * @package Push2\Model\Onesignal
 *
 * @method $this setTitle($title)
 * @method $this setSubtitle($subtitle)
 * @method $this setBody($body)
 * @method $this setBigPicture($big_picture)
 * @method $this setSendAfter($send_after)
 * @method $this setDelayedOption($delayed_option)
 * @method $this setDeliveryTimeOfDay($delivery_time_of_day)
 * @method $this setActionUrl($action_url)
 * @method $this setTargets($targets)
 * @method string getTitle()
 * @method string getSubtitle()
 * @method string getBody()
 * @method string getBigPicture()
 * @method string getSendAfter()
 * @method string getDelayedOption()
 * @method string getDeliveryTimeOfDay()
 * @method string getActionUrl()
 * @method AbstractTarget[] getTargets()
 */
class Message extends BaseModel {

    /**
     * @var string
     */
    public $_db_table = Db\Table\Message::class;

    public function __construct()
    {
        // Default targets are all users
        $this->addTargets(new Segment('Subscribed Users'));
    }

    /**
     * @param $data
     * @return $this
     */
    public function fromArray($data): self {
        $this->setTitle($data['title']);
        $this->setSubtitle($data['subtitle'] ?? null);
        $this->setBody($data['body']);
        $this->setBigPicture($data['big_picture'] ?? null);
        $this->setSendAfter($data['send_after'] ?? null);
        $this->setDelayedOption($data['delayed_option'] ?? null);
        $this->setDeliveryTimeOfDay($data['delivery_time_of_day'] ?? null);
        $this->setActionUrl($data['action_url'] ?? null);

        $this->checkSchedulingOptions();

        return $this;
    }

    /**
     * @return void
     */
    public function checkSchedulingOptions() {
        // send_after is the determining factor for scheduling
        $sendAfter = $this->getSendAfter();
        if (!empty($sendAfter)) {
            $this->setDelayedOption('timezone');
            if (preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/", $this->getDeliveryTimeOfDay()) === 0) {
                $this->setDeliveryTimeOfDay('9:00 AM');
            }
        }
    }

    /**
     * @return $this
     */
    public function clearTargets(): self {
        return $this->setTargets([]);
    }

    /**
     * @param AbstractTarget $targets
     * @return $this
     */
    public function addTargets(AbstractTarget $targets): self {
        $newTargets = $this->getTargets();
        $newTargets[] = $targets;
        return $this->setTargets($newTargets);
    }
}