<?php

// 4.20.9 installing Template ACLs
try {
    $designResource = (new \Acl_Model_Resource())->find('editor_design_template', 'code');
    if ($designResource && $designResource->getId()) {
        $templates = (new Template_Model_Design())->findAll();
        foreach ($templates as $template) {
            $code = $template->getCode();
            $name = $template->getName();

            // Create or update
            $resource = new \Acl_Model_Resource();
            $resource
                ->setData(
                    [
                        'parent_id' => $designResource->getId(),
                        'code' => 'template_' . $code,
                        'label' => $name,
                    ]
                )
                ->insertOrUpdate(['code']);
        }
    }
    // Abort if something is wrong!

} catch (\Exception $e) {
    // Silently fails!
}