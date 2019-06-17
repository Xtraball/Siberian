<?php

try {
    $this->query("ALTER TABLE `comment_answer` MODIFY `text` TEXT;");
} catch (Exception $e) {
    // Ignore error
}

