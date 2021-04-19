<?php

// 4.20.9 installing Layouts ACLs
try {
    $designResource = (new \Acl_Model_Resource())->find('editor_design_layout', 'code');
    if ($designResource && $designResource->getId()) {
        $layouts = (new Application_Model_Layout_Homepage())->findAll();
        foreach ($layouts as $layout) {
            $code = $layout->getCode();
            $name = $layout->getName();

            // Create or update
            $resource = new \Acl_Model_Resource();
            $resource
                ->setData(
                    [
                        'parent_id' => $designResource->getId(),
                        'code' => 'layout_' . $code,
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
