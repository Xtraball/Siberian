<?php

// Update roles parent_id with default value!

try {
    $this->query("UPDATE acl_role SET parent_id = 1 WHERE role_id != NULL;");
    $this->query("UPDATE acl_role SET parent_id = NULL WHERE role_id = 1;");
} catch (\Exception $e) {
    // Silent!
}