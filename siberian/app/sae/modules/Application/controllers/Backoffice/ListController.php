<?php

/**
 * Class Application_Backoffice_ListController
 */
class Application_Backoffice_ListController extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s',
                __('Manage'),
                __('Applications')
            ),
            'icon' => 'fa-mobile',
            'words' => [
                'confirmDelete' => __('Yes, Delete!'),
                'cancelDelete' => __('No, go back!'),
                'deleteTitle' => __('Confirmation required'),
                'deleteMessage' => __("<b class=\"delete-warning\">You are going to remove #APP_ID# / #APP_NAME#<br />Removed application CANNOT be restored! Are you ABSOLUTELY sure?</b><br />This action can lead to data loss. To prevent accidental actions we ask you to confirm your intention.<br />Please type <code style=\"user-select: none;\">%s</code> to proceed or close this modal to cancel.", 'yes-delete-app-#APP_ID#')
            ],
        ];

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Controller_Request_Exception
     */
    public function findallAction()
    {
        $application = new Application_Model_Application();

        $offset = $this->getRequest()->getParam('offset', null);
        $limit = Application_Model_Application::BO_DISPLAYED_PER_PAGE;

        $request = $this->getRequest();
        if ($range = $request->getHeader('Range')) {
            $parts = explode('-', $range);
            $offset = $parts[0];
            $limit = ($parts[1] - $parts[0]) + 1;
        }

        $params = [
            'offset' => $offset,
            'limit' => $limit
        ];

        $filters = [];
        if ($_filter = $this->getRequest()->getParam("filter", false)) {
            $filters["(name LIKE ? OR app_id LIKE ? OR bundle_id LIKE ? OR package_name LIKE ?)"] = "%{$_filter}%";
        }

        $order = $this->getRequest()->getParam("order", false);
        $by = filter_var($this->getRequest()->getParam("by", false), FILTER_VALIDATE_BOOLEAN);
        if ($order) {
            $order_by = ($by) ? "ASC" : "DESC";
            $order = sprintf("%s %s", $order, $order_by);
        }

        $to_publish = filter_var($this->getRequest()->getParam("toPublish", false), FILTER_VALIDATE_BOOLEAN);
        $app_ids = [];
        if ($to_publish) {
            $app_ids = $application->findAllToPublish();

            if (empty($app_ids)) {
                $filters["app_id = ?"] = -1;
            } else {
                if (!empty($app_ids)) {
                    $filters["app_id IN (?)"] = $app_ids;
                }

            }
        }

        $removedFromEditor = filter_var($this->getRequest()->getParam('removedFromEditor', false), FILTER_VALIDATE_BOOLEAN);
        if ($removedFromEditor) {
            $filters["is_active = ?"] = 0;
        }

        $total = $application->countAll($filters);

        if ($range = $request->getHeader('Range')) {
            $start = $parts[0];
            $end = ($total <= $parts[1]) ? $total : $parts[1];

            $this->getResponse()->setHeader("Content-Range", sprintf("%s-%s/%s", $start, $end, $total));
            $this->getResponse()->setHeader("Range-Unit", "items");
        }

        $applications = $application->findAll($filters, $order, $params);

        $data = [
            'display_per_page' => $limit,
            'collection' => []
        ];

        foreach ($applications as $application) {
            $data['collection'][] = [
                'id' => $application->getId(),
                'can_be_published' => in_array($application->getId(), $app_ids, false),
                'name' => mb_convert_encoding($application->getName(), 'UTF-8', 'UTF-8'),
                'bundle_id' => $application->getBundleId(),
                'package_name' => $application->getPackageName(),
                'icon' => $application->getIcon(128),
                'is_active' => (boolean) $application->getIsActive(),
                'size_on_disk' => formatBytes($application->getData('size_on_disk'))
            ];
        }

        $this->_sendJson($data['collection']);
    }

    public function findbyadminAction()
    {

        $request = $this->getRequest();
        if ($range = $request->getHeader("Range")) {
            $parts = explode("-", $range);
            $offset = $parts[0];
            $limit = ($parts[1] - $parts[0]) + 1;
        }

        $admin_id = $this->getRequest()->getParam("admin_id");

        $filters = [];
        if ($_filter = $this->getRequest()->getParam("filter", false)) {
            $filters["(a.name LIKE ? OR a.app_id LIKE ? OR a.bundle_id LIKE ? OR a.package_name LIKE ?)"] = "%{$_filter}%";
        }

        $order = $this->getRequest()->getParam("order", false);
        $by = filter_var($this->getRequest()->getParam("by", false), FILTER_VALIDATE_BOOLEAN);
        if ($order) {
            $order_by = ($by) ? "ASC" : "DESC";
            $order = sprintf("%s %s", $order, $order_by);
        }

        $application = new Application_Model_Application();
        if (!Siberian_Version::is("sae")) {
            $show_all = filter_var($this->getRequest()->getParam("show_all_applications", false), FILTER_VALIDATE_BOOLEAN);
            $_admin_id = ($show_all) ? null : $admin_id;

            $applications = $application->findAllByAdmin($_admin_id, $filters, $order, $limit, $offset);
            $total_applications = $application->findAllByAdmin($_admin_id, $filters);

            $total = $total_applications->count();
        } else {
            $applications = [Application_Model_Application::getInstance()];
            $total = 1;
        }

        if ($range = $request->getHeader("Range")) {
            $start = $parts[0];
            $end = ($total < $parts[1]) ? $total - 1 : $parts[1];

            $this->getResponse()->setHeader("Content-Range", sprintf("%s-%s/%s", $start, $end, $total));
            $this->getResponse()->setHeader("Range-Unit", "items");
        }

        $application_admin = new Application_Model_Admin();

        $apps = [];
        foreach ($applications as $application) {
            $result = $application_admin->findAll([
                "app_id" => $application->getId(),
                "admin_id" => $admin_id
            ]);
            $is_allowed = ($result->count() >= 1) ? true : false;

            $apps[] = [
                "id" => $application->getId(),
                "app_id" => $application->getId(),
                "icon" => $application->getIcon(128),
                "name" => $application->getName(),
                "bundle_id" => $application->getBundleId(),
                "package_name" => $application->getPackageName(),
                "is_allowed_to_add_pages" => $is_allowed,
            ];
        }

        $this->_sendJson($apps);

    }

    public function deleteapplicationAction()
    {
        if (Siberian_Version::is('SAE')) {
            $this->_sendJson([
                'error' => true,
                'message' => __("We can't delete the only application from your installation.")
            ]);
        } else {
            try {
                $request = $this->getRequest();
                $params = Siberian_Json::decode($request->getRawBody());
                $appId = $params['appId'];

                $app = (new Application_Model_Application())
                    ->find($appId);

                if (!$app->getId()) {
                    throw new Siberian_Exception(__("This Application doesn't exists, aborting!"));
                }

                $appName = $app->getName();

                // Delete the application!
                $app->wipe();

                $payload = [
                    'success' => true,
                    'message' => __('The Application ' . $appId . ' / ' . $appName . ' has been deleted!')
                ];
            } catch (Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($payload);
        }
    }

}
