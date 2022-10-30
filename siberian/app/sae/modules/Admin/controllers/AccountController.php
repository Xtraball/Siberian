<?php

use Siberian\File;
use Siberian\Hook;
use Siberian\Mail;
use Siberian\Layout;

/**
 * Class Admin_AccountController
 */
class Admin_AccountController extends Admin_Controller_Default
{
    /**
     * @throws Zend_Session_Exception
     */
    public function editAction()
    {
        $this->loadPartials();
        $current_admin = $this->getSession()->getAdmin();
        $this->getLayout()->getPartial("content")->setMode("edit")->setEditAdmin($current_admin);
    }

    /**
     *
     */
    public function savepostAction()
    {

        if ($data = $this->getRequest()->getPost()) {

            try {

                // Protection for demo mode!
                if (__getConfig('is_demo')) {
                    $protectedEmails = ['client@client.com', 'demo@demo.com'];
                    if (in_array($data['email'], $protectedEmails)) {
                        throw new \Siberian\Exception(__('You are not allowed to edit this account in demo!'));
                    }

                    $editedAdmin = (new Admin_Model_Admin())->find($data['admin_id']);
                    if ($editedAdmin && $editedAdmin->getId() && in_array($editedAdmin->getEmail(), $protectedEmails)) {
                        throw new \Siberian\Exception(__('You are not allowed to edit this account in demo!'));
                    }
                }

                $admin = new Admin_Model_Admin();
                $current_admin = $this->getSession()->getAdmin();
                $check_email_admin = new Admin_Model_Admin();

                if (!empty($data['admin_id'])) {
                    $admin->find($data['admin_id']);
                    if (!$admin->getId()) {
                        throw new \Siberian\Exception(__('There is no user with this ID.'));
                    }
                }

                if ($admin->getId() &&
                    $admin->getId() != $this->getAdmin()->getId() &&
                    (
                        $admin->getParentId() &&
                        $admin->getParentId() != $this->getAdmin()->getId() ||
                        !$admin->getParentId()
                    )) {

                    throw new \Siberian\Exception(__("An error occurred while saving your account. Please try again later."));
                }

                if (!$admin->getId() ||
                    $admin->getId() != $this->getAdmin()->getId()) {
                    $admin->setParentId($this->getAdmin()->getId());
                }

                $check_email_admin->find($data['email'], 'email');
                if ($check_email_admin->getId() && $check_email_admin->getId() != $admin->getId()) {
                    throw new \Siberian\Exception(__('This email address is already used'));
                }

                if (isset($data['password'])) {
                    if ($data['password'] != $data['confirm_password']) {
                        throw new \Siberian\Exception(__('Your password does not match the entered password.'));
                    }
                    if (!empty($data['old_password']) && !$admin->isSamePassword($data['old_password'])) {
                        throw new \Siberian\Exception(__("The old password does not match the entered password."));
                    }
                    if (!empty($data['password'])) {
                        $admin->setPassword($data['password']);
                        unset($data['password']);
                    }
                }

                if (empty($data["role_id"]) && $data['mode'] === 'management') {
                    throw new \Siberian\Exception(__('The account role is required'));
                } else {
                    if ($data['mode'] === 'management') {
                        $admin->setRoleId($data["role_id"]);
                    }
                }

                // Available roles for the current admin!
                if ($data['mode'] === 'management') {
                    $role = (new Acl_Model_Role())->find($current_admin->getRoleId());
                    $availableRoles = (new Acl_Model_Role())->getChilds($role);
                    if ($role->getIsSelfAssignable()) {
                        array_unshift($availableRoles, $role->_asArray($role));
                    }

                    $isAllowedRole = false;
                    foreach ($availableRoles as $availableRole) {
                        if ($availableRole['value'] == $data['role_id']) {
                            $isAllowedRole = true;
                        }
                    }

                    if (!$isAllowedRole) {
                        throw new \Siberian\Exception(__("Your are not allowed to assign this role."));
                    }
                } else {
                    unset($data['role_id']);
                }

                $admin
                    ->setCompany($data['company'])
                    ->setEmail($data['email'])
                    ->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'])
                    ->setAddress($data['address'])
                    ->setAddress2($data['address2'])
                    ->setZipCode($data['zip_code'])
                    ->setCity($data['city'])
                    ->setCountryCode($data['country_code'])
                    ->setRegionCode($data['region_code'])
                    ->setPhone($data['phone'])
                    ->setWebsite($data['website'])
                    ->setOptinEmail($data['optin_email'] === 'on')
                    ->save();

                // Clear admin cache, if acl changed for example.
                /**
                 * @var $cacheOutput Zend_Cache_Frontend_Output
                 */
                $cacheOutput = Zend_Registry::get("cacheOutput");
                $cacheOutput->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ["cache_admin"]);

                //For SAE we link automatically the user to the uniq app
                if (Siberian_Version::is("sae")) {
                    $this->getApplication()->addAdmin($admin);
                }
                $payload = [
                    'success' => true,
                    'message' => __('The account has been successfully saved'),
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

    public function deleteAction()
    {

        if ($admin_id = $this->getRequest()->getParam('admin_id') AND !$this->getSession()->getAdmin()->getParentId()) {

            try {

                $admin = new Admin_Model_Admin();
                $admin->find($admin_id);

                if (!$admin->getId()) {
                    throw new Exception(__("This administrator does not exist"));
                } else if (!$admin->getParentId()) {
                    throw new Exception(__("You can't delete the main account"));
                }

                if (__getConfig('is_demo')) {
                    if (in_array($admin->getEmail(), ['client@client.com', 'demo@demo.com'])) {
                        throw new \Siberian\Exception(__('You are not allowed to delete this account in demo!'));
                    }
                }

                $admin->delete();

                $html = [
                    'success' => 1,
                    'admin_id' => $admin_id
                ];

            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendHtml($html);

        }

    }

    public function loginAction()
    {
        $this->loadPartials();
    }

    public function loginpostAction()
    {

        if (!$this->getSession()->isLoggedIn() && ($datas = $this->getRequest()->getPost())) {

            $this->getSession()->resetInstance();
            $canBeLoggedIn = false;

            try {

                if (empty($datas['email']) OR empty($datas['password'])) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }
                $admin = new Admin_Model_Admin();
                $admin->findByEmail($datas['email']);

                if ($admin->authenticate($datas['password'])) {
                    $this->getSession()
                        ->setAdmin($admin);
                }

                if (!$this->getSession()->isLoggedIn()) {
                    throw new Exception(__('Authentication failed. Please check your email and/or your password'));
                }

            } catch (Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('/');
        return $this;

    }

    /**
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function signuppostAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            Hook::trigger('admin.register', [
                'origin' => 'web',
                'request' => $request
            ]);

            // Check l'email et le mot de passe
            if (empty($data['email']) OR !Zend_Validate::is($data['email'], 'emailAddress')) {
                throw new Exception(__('Please enter a valid email address.'));
            }
            if (empty($data['password']) OR strlen($data['password']) < 6) {
                throw new Exception(__('The password must be at least 6 characters.'));
            }
            if (empty($data['confirm_password']) OR $data['password'] != $data['confirm_password']) {
                throw new Exception(__('The password and the confirmation does not match.'));
            }

            $admin = new Admin_Model_Admin();
            $admin->findByEmail($data['email']);

            if ($admin->getId()) {
                throw new Exception(__('We are sorry but this email address is already used.'));
            }

            $role = new Acl_Model_Role();
            if ($default_role = $role->findDefaultRoleId()) {
                $admin->setRoleId($default_role);
            }

            // Créé le user
            $admin->setEmail($data['email'])
                ->setPassword($data['password'])
                ->save();

            // Met le user en session
            $this->getSession()
                ->setAdmin($admin);

            $admin->sendAccountCreationEmail($data["password"]);

            $redirect_to = 'admin/application/list';

            Hook::trigger('admin.register.success', [
                'origin' => 'web',
                'adminId' => $admin->getId(),
                'admin' => $admin,
                'token' => Zend_Session::getId(),
                'request' => $request,
            ]);

        } catch (Exception $e) {
            if ($this->getSession()->isLoggedIn()) {
                $redirect_to = 'admin/application/list';
            } else {
                $this->getSession()->addError($e->getMessage());
                $redirect_to = "/";
            }

            Hook::trigger('admin.register.error', [
                'origin' => 'web',
                'message' => $e->getMessage(),
                'request' => $request,
            ]);
        }

        $this->redirect($redirect_to);
    }

    public function forgotpasswordpostAction()
    {

        if ($datas = $this->getRequest()->getPost() AND !$this->getSession()->isLoggedIn('admin') AND !$this->getSession()->isLoggedIn('pos')) {

            try {

                if (empty($datas['email'])) {
                    throw new Exception(__('Please enter your email address'));
                }

                $admin = new Admin_Model_Admin();
                $admin->findByEmail($datas['email']);

                if (!$admin->getId()) {
                    throw new Exception(__("Your email address does not exist"));
                }

                $password = generate_strong_password(10);

                $admin->setPassword($password)->save();

                // E-Mail back the user!
                $title = p__('admin', 'New password');
                $baseEmail = $this->baseEmail('forgot_password', $title, '', true);
                $baseEmail->setContentFor('header', 'show_logo', true);
                $mail = new Mail();

                $baseEmail->setContentFor('content_email', 'password', $password);

                $content = $baseEmail->render();

                $mail->setBodyHtml($content);
                $mail->addTo($admin->getEmail(), $admin->getName());
                $mail->setSubject(__('%s - Your new password'), ["_sender_name"]);
                $mail->send();


                $this->getSession()->addSuccess(__('Your new password has been sent to the entered email address'));

            } catch (Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('/');
        return $this;

    }

    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail($nodeName,
                              $title,
                              $message = '',
                              $showLegals = false)
    {
        $layout = new Layout();
        $layout = $layout->loadEmail('admin', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }

    public function logoutAction()
    {
        $this->getSession()->resetInstance();
        $this->_redirect('');
        return $this;
    }

    /**
     *
     */
    public function mydataAction()
    {
        $session = $this->getSession();
        $admin = $session->getAdmin();
        $request = $this->getRequest();
        $page = $request->getParam('page', 'profile');
        $download = filter_var($request->getParam('download', false), FILTER_VALIDATE_BOOLEAN);

        if (!$download) {
            $nav = [
                'profile' => [
                    'uri' => '/admin/account/mydata?page=profile',
                    'label' => __('Profile'),
                ],
            ];
        } else {
            $nav = [
                'profile' => [
                    'uri' => './index.html',
                    'label' => __('Profile'),
                ],
            ];
        }

        switch ($page) {
            case 'profile':
                $content = $this->getProfileContent($this->getBaseLayout($admin), $nav);
                break;
        }


        if (!$download) {
            echo $content;
            die;
        } else {
            // Create folder tree & files
            $baseTmp = Core_Model_Directory::getTmpDirectory(true);
            $baseTmp = $baseTmp . '/export-' . uniqid();

            mkdir($baseTmp, 0777, true);

            $profile = $baseTmp . '/index.html';
            $profileLayout = $this->getBaseLayout($admin);
            $profileContent = $this->getProfileContent($profileLayout, $nav);
            File::putContents($profile, $profileContent);

            $baseZip = $baseTmp . '.zip';

            $result = Core_Model_Directory::zip($baseTmp, $baseZip);

            // Clean-up folder!
            Core_Model_Directory::delete($baseTmp);

            $admin = $this->getSession()->getAdmin();
            $slug = slugify($admin->getFirstname() . ' ' . $admin->getLastname());

            $this->_download($result, 'export-' . $slug . '.zip', 'application/octet-stream');
        }
    }

    /**
     * @return Siberian_Layout
     */
    public function getBaseLayout($admin)
    {
        $layout = new Siberian\Layout();

        $layout
            ->setBaseRender('gdpr', 'admin/account/gdpr/base.phtml', 'core_view_default');

        $layout
            ->getBaseRender()
            ->setAdmin($admin);

        return $layout;
    }

    /**
     * @param $layout
     * @param $nav
     * @return string
     */
    private function getProfileContent($layout, $nav)
    {
        $layout->addPartial(
            'content', 'admin_view_default',
            'admin/account/gdpr/profile.phtml'
        );

        $layout
            ->getBaseRender()
            ->setNav($nav)
            ->setNavActive('profile');

        $session = $this->getSession();
        $admin = $session->getAdmin();

        $layout
            ->getPartial('content')
            ->setAdmin($admin);

        return $layout->render();
    }

}
