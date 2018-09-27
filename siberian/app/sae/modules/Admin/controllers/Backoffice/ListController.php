<?php

/**
 * Class Admin_Backoffice_ListController
 */
class Admin_Backoffice_ListController extends Backoffice_Controller_Default
{
    public function loadAction()
    {
        $payload = [
            'title' => sprintf('%s > %s > %s',
                __('Manage'),
                __('Editor access'),
                __('Users')
            ),
            'icon' => 'fa-users',
        ];

        $this->_sendJson($payload);
    }

    public function findallAction()
    {

        $offset = $this->getRequest()->getParam("offset") ? $this->getRequest()->getParam("offset") : null;
        $limit = Admin_Model_Admin::BO_DISPLAYED_PER_PAGE;

        $request = $this->getRequest();
        if ($range = $request->getHeader("Range")) {
            $parts = explode("-", $range);
            $offset = $parts[0];
            $limit = ($parts[1] - $parts[0]) + 1;
        }

        $params = [
            "offset" => $offset,
            "limit" => $limit
        ];

        $filters = [];
        if ($_filter = $this->getRequest()->getParam("filter", false)) {
            $filters["(email LIKE ? OR admin_id LIKE ? OR company LIKE ? OR firstname LIKE ? OR lastname LIKE ?)"] = "%{$_filter}%";
        }

        $order = $this->getRequest()->getParam("order", false);
        $by = filter_var($this->getRequest()->getParam("by", false), FILTER_VALIDATE_BOOLEAN);
        if ($order) {
            $order_by = ($by) ? "ASC" : "DESC";
            $order = sprintf("%s %s", $order, $order_by);
        }

        $admin = new Admin_Model_Admin();
        $all_admins = $admin->findAll($filters, $order);
        $total = $all_admins->count();

        $admins = $admin->findAll($filters, $order, $params);
        $data = [
            "display_per_page" => $limit,
            "collection" => []
        ];

        if ($range = $request->getHeader("Range")) {
            $start = $parts[0];
            $end = ($total <= $parts[1]) ? $total : $parts[1];

            $this->getResponse()->setHeader("Content-Range", sprintf("%s-%s/%s", $start, $end, $total));
            $this->getResponse()->setHeader("Range-Unit", "items");
        }

        foreach ($admins as $admin) {
            $data["collection"][] = [
                "id" => $admin->getId(),
                "email" => $admin->getEmail(),
                "name" => $admin->getFirstname() . " " . $admin->getLastname(),
                "company" => $admin->getCompany(),
                "key" => sha1($admin->getFirstname() . $admin->getId()),
                "created_at" => $admin->getFormattedCreatedAt(__("MM/dd/yyyy"))
            ];
        }

        $this->_sendHtml($data["collection"]);
    }

    public function deleteAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if (empty($data["admin_id"])) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                $admin = new Admin_Model_Admin();
                $admin->find($data["admin_id"]);

                if (!$admin->getId()) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                if (__getConfig('is_demo')) {
                    if (in_array($admin->getEmail(), ['client@client.com', 'demo@demo.com'])) {
                        throw new \Siberian\Exception(__('You are not allowed to delete this account in demo!'));
                    }
                }

                $admin->delete();

                $data = [
                    "success" => 1,
                    "message" => __("User successfully deleted")
                ];
            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendHtml($data);

        }

    }

    public function loginasAction()
    {

        if ($admin_id = $this->getRequest()->getParam("admin_id")) {

            $admin = new Admin_Model_Admin();
            $admin->find($admin_id);

            if ($admin->getId()) {

                $key = sha1($admin->getFirstname() . $admin->getId());

                if ($key == $this->getRequest()->getParam('key', 'aa')) {
                    $front_session = $this->getSession(Core_Model_Session::TYPE_ADMIN);
                    $front_session->resetInstance()->setAdmin($admin);
                    $this->_redirect('');
                    return $this;
                }

            }

        }

    }

}
