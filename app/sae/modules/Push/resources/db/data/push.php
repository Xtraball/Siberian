<?php

/** @todo replace with method */
$this->query("
    UPDATE `push_delivered_message` SET is_displayed = 1 WHERE is_read = 1 and is_displayed = 0;
");