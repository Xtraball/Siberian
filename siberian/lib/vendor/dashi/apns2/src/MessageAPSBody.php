<?php
namespace Dashi\Apns2;

class MessageAPSBody extends BaseDataObject
{
    /**
     * @var string|MessageAlertBody
     */
    public $alert;
    /**
     * @var string|null
     */
    public $sound;
    /**
     * @var int|null
     */
    public $badge;
    /**
     * @var int|null
     */
    public $contentAvailable;


    public function __construct($data = [])
    {
        $this->loadFromJSON($data, [
            'alert' => 'Dashi\Apns2\MessageAlertBody'
        ]);
    }
}

