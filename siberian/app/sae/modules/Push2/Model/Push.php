<?php

namespace Push2\Model;

use \Core_Model_Default as ModelDefault;

/**
 * Class Message
 * @package Push2\Model
 */
class Push extends ModelDefault {

}

// important!
class_alias(Push::class, 'Push2_Model_Push');