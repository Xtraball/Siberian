<?php
namespace Dashi\Apns2;

class MessageAlertBody extends BaseDataObject
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string|null
     */
    public $body;
    /**
     * @var string|null
     */
    public $titleLocKey;
    /**
     * @var string|null
     */
    public $titleLocArgs;
    /**
     * @var string|null
     */
    public $actionLocKey;
    /**
     * @var string|null
     */
    public $locKey;
    /**
     * @var string|null
     */
    public $locArgs;
    /**
     * @var string|null
     */
    public $launchImage;
}
