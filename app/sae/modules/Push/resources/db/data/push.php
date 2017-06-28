<?php

/** @todo replace with method */
$this->query("
    UPDATE `push_delivered_message` SET is_displayed = 1 WHERE is_read = 1 and is_displayed = 0;
");


// Not sure why but if we don't split theses alter table it skips some of them;
$this->query("ALTER TABLE `push_messages` MODIFY `push_messages`.`title` TEXT NOT NULL;");
$this->query("ALTER TABLE `push_messages` MODIFY `push_messages`.`text` TEXT NOT NULL;");
$this->query("ALTER TABLE `push_message_global` MODIFY `push_message_global`.`title` TEXT NOT NULL;");
$this->query("ALTER TABLE `push_message_global` MODIFY `push_message_global`.`message` TEXT NOT NULL;");
