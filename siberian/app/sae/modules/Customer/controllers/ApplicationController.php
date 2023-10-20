<?php

use Siberian\Json;
use Siberian\Exception;

/**
 * Class Customer_ApplicationController
 */
class Customer_ApplicationController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        "edit-settings" => [
            "tags" => [
                "app_#APP_ID#",
                "homepage_app_#APP_ID#"
            ],
        ],
    ];

    /**
     *
     */
    public function listAction()
    {
        $this->loadPartials();
    }

    public function newAction()
    {
        $this->_forward("edit");
    }

    public function editAction()
    {
        $customer = new Customer_Model_Customer();
        if ($customer_id = $this->getRequest()->getParam('customer_id')) {
            $customer->find($customer_id);
            if (!$customer->getId()) {
                $this->getRequest()->addError(__("This user does not exist."));
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setCurrentCustomer($customer);
    }

    /**
     *
     */
    public function editSettingsAction()
    {
        try {
            $request = $this->getRequest();
            $optionValue = $this->getCurrentOptionValue();

            $form = new Customer_Form_Settings();
            $values = $request->getParams();
            if ($form->isValid($values)) {

                $settings = [
                    'design' => $values['design'],
                    'email_validation' => filter_var($values['email_validation'], FILTER_VALIDATE_BOOLEAN),
                    'enable_facebook_login' => filter_var($values['enable_facebook_login'], FILTER_VALIDATE_BOOLEAN),
                    'enable_registration' => filter_var($values['enable_registration'], FILTER_VALIDATE_BOOLEAN),
                    'enable_commercial_agreement' => filter_var($values['enable_commercial_agreement'], FILTER_VALIDATE_BOOLEAN),
                    'enable_commercial_agreement_label' => $values['enable_commercial_agreement_label'],
                    'enable_password_verification' => filter_var($values['enable_password_verification'], FILTER_VALIDATE_BOOLEAN),
                    'extra_mobile' => filter_var($values['extra_mobile'], FILTER_VALIDATE_BOOLEAN),
                    'extra_mobile_required' => filter_var($values['extra_mobile_required'], FILTER_VALIDATE_BOOLEAN),
                    'extra_civility' => filter_var($values['extra_civility'], FILTER_VALIDATE_BOOLEAN),
                    'extra_civility_required' => filter_var($values['extra_civility_required'], FILTER_VALIDATE_BOOLEAN),
                    'extra_birthdate' => filter_var($values['extra_birthdate'], FILTER_VALIDATE_BOOLEAN),
                    'extra_birthdate_required' => filter_var($values['extra_birthdate_required'], FILTER_VALIDATE_BOOLEAN),
                    'extra_nickname' => filter_var($values['extra_nickname'], FILTER_VALIDATE_BOOLEAN),
                    'extra_nickname_required' => filter_var($values['extra_nickname_required'], FILTER_VALIDATE_BOOLEAN),
                ];

                // Enforces logic on backend side (to be sure)
                if ($settings['extra_mobile_required']) {
                    $settings['extra_mobile'] = true;
                }
                if ($settings['extra_civility_required']) {
                    $settings['extra_civility'] = true;
                }
                if ($settings['extra_birthdate_required']) {
                    $settings['extra_birthdate'] = true;
                }
                if ($settings['extra_nickname_required']) {
                    $settings['extra_nickname'] = true;
                }

                $optionValue
                    ->setSettings(Json::encode($settings, JSON_UNESCAPED_UNICODE))
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => __('Settings saved!'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $appId = $application->getId();
            $data = $request->getPost();

            $customer = new Customer_Model_Customer();
            if (!empty($data['customer_id'])) {
                $customer->find($data['customer_id']);
                if (!$customer->getId() || $customer->getAppId() !== $appId) {
                    throw new Exception(p__('customer', "This customer doesn't exists."));
                }
            }

            // My account required fields!
            $myAccountTab = $application->getOption('tabbar_account');
            $accountSettings = Json::decode($myAccountTab->getSettings());
            $requireMobile = $accountSettings['extra_mobile_required'];
            $requireCivility = $accountSettings['extra_civility_required'];

            $isNew = !$customer->getId();

            $errors = [];

            if ($requireCivility && empty($data['civility'])) {
                $errors[] = p__('customer', 'Civility');
            }
            if (empty($data['firstname'])) {
                $errors[] = p__('customer', 'Firstname');
            }
            if (empty($data['lastname'])) {
                $errors[] = p__('customer', 'Lastname');
            }
            if (empty($data['email'])) {
                $errors[] = p__('customer', 'Email');
            }
            if ($requireMobile && empty($data['mobile'])) {
                $errors[] = p__('customer', 'Mobile');
            }
            if ($isNew && empty($data['password'])) {
                $errors[] = p__('customer', 'Password');
            }

            if (count($errors) > 0) {
                $errorMessages = [];
                $errorMessages[] = p__('customer', 'The following fields are required!');
                foreach ($errors as $error) {
                    $errorMessages[] = $error;
                }
                $message = implode_polyfill('<br />- ', $errorMessages);
                throw new Exception($message);
            }

            if (!empty($data['email']) && !Zend_Validate::is($data['email'], 'emailAddress')) {
                throw new Exception(__("Please enter a valid email address"));
            }

            //Test if the email is already used
            if (empty($data['customer_id'])) {
                $customers = $customer->findAll(["email = ?" => $data["email"], "app_id = ?" => $this->getApplication()->getId()]);
                if ($customers->count()) {
                    $message = __("We are sorry but the %s account is already linked to one of our customers", $data["email"]);
                    throw new Exception($message);
                }
            }

            $data['show_in_social_gaming'] = (int) !empty($data['show_in_social_gaming']);
            $data['can_access_locked_features'] = (int) !empty($data['can_access_locked_features']);

            if ($isNew) {
                $data['app_id'] = $this->getApplication()->getId();
                $data['privacy_policy'] = false;
                $data['communication_agreement'] = false;
            }

            if (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            }

            $customer->setData($data);
            if (!empty($data['password'])) {
                $customer->setPassword($data['password']);
            }
            $customer->save();

            $this->getSession()->addSuccess(__("Info successfully saved"));

            $payload = [
                'success' => true,
                'message' => p__('customer', 'Customer saved!'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);







    }

    public function deleteNewAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $customerId = $request->getParam("customer_id", null);

            $customer = (new Customer_Model_Customer())
                ->find($customerId);
            if (!$customer->getId() || $customer->getAppId() != $application->getId()) {
                throw new \Siberian\Exception("#07888-01" . __("We are unable to delete this customer!"));
            }

            $customer->delete();

            $payload = [
                'success' => true,
                'message' => __('Success'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function deleteAction()
    {

        if ($customer_id = $this->getRequest()->getPost('customer_id')) {

            try {

                $customer = new Customer_Model_Customer();
                $customer->find($customer_id);
                if (!$customer->getId() || $customer->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                $customer_id = $customer->getId();
                $customer->delete();

                $html = ["success" => 1, "customer_id" => $customer_id];

            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
            die;

        }
    }

    public function getuserslistpaginateAction()
    {
        $data = $this->getRequest()->getPost();

        $html = [
            "customers" => []
        ];

        if ($data["limit"]) {

            $search_params = [
                "app_id" => $this->getApplication()->getId()
            ];

            if ($data["search_value"]) {
                $search_params["search"] = "(email LIKE '%" . $data["search_value"] . "%' OR firstname LIKE '%" . $data["search_value"] . "%' OR lastname LIKE '%" . $data["search_value"] . "%')";
            }

            $customer_model = new Customer_Model_Customer();
            $customer_model = $customer_model->findAllCustomersByApp($search_params, ["limit" => $data["limit"], "offset" => $data["offset"]]);

            $customers = [];
            foreach ($customer_model as $customer) {
                $customer->setCreatedAt($customer->getFormattedCreatedAt(__(Zend_Date::DATE_MEDIUM)));
                $customers[] = $customer->getData();
            }

            $html["customers"] = $customers;
        }

        $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
        die;
    }

    public function fetchCustomersAction()
    {
        try {
            $request = $this->getRequest();
            $limit = $request->getParam('perPage', 25);
            $offset = $request->getParam('offset', 0);
            $sorts = $request->getParam('sorts', []);
            $queries = $request->getParam('queries', []);

            $filter = null;
            if (array_key_exists('search', $queries)) {
                $filter = $queries['search'];
            }

            //foreach ()

            $params = [
                'limit' => $limit,
                'offset' => $offset,
                'sorts' => $sorts,
                'filter' => $filter,
            ];

            $application = $this->getApplication();
            $customers = (new Customer_Model_Customer())
                ->findAllForApp($application->getId(), $params);

            $countAll = (new Customer_Model_Customer())
                ->countAllForApp($application->getId());

            $countFiltered = (new Customer_Model_Customer())
                ->countAllForApp($application->getId(), $params);

            $customersJson = [];
            foreach ($customers as $customer) {
                $data = $customer->getData();
                $data['name'] = ucfirst($customer->getName());
                $data['civility'] = $data['civility'] ?? '';
                if (!empty($data['civility'])) {
                    $data['civility'] .= '.';
                    $data['civility'] = ucfirst($data['civility']);
                }
                $data['mobile'] = $data['mobile'] ?? '-';
                $data['privacy_policy'] = (bool)$data['privacy_policy'];
                $data['communication_agreement'] = (bool)$data['communication_agreement'];
                $data['show_in_social_gaming'] = (bool)$data['show_in_social_gaming'];
                $data['created_at'] = datetime_to_format($data['created_at']);
                $customersJson[] = $data;
            }

            $payload = [
                'records' => $customersJson,
                'queryRecordCount' => $countFiltered[0],
                'totalRecordCount' => $countAll[0]
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

}
