<?php

/**
 * Class Weblink_Application_MultiController
 */
class Weblink_Application_MultiController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "editpost" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ],
        "reorder" => [
            "tags" => [
                "homepage_app_#APP_ID#"
            ],
        ]
    ];

    public function reorderAction()
    {
        try {
            $request = $this->getRequest();
            $datas = $request->getPost();

            $optionValue = $this->getCurrentOptionValue();
            if (!$optionValue->getId()) {
                throw new \Siberian\Exception("This option doesn't exists.");
            }

            if (!array_key_exists('positions', $datas) &&
                !is_array($datas['positions'])) {
                throw new \Siberian\Exception("New positions are missing.");
            }

            // Iterate over new id/positions and save them!
            foreach ($datas['positions'] as $position) {
                $weblinkMulti = (new \Weblink_Model_Weblink_Link())
                    ->find($position['id']);

                if ($weblinkMulti->getId()) {
                    $weblinkMulti
                        ->setPosition($position['position'])
                        ->save();
                }
            }

            /** Update touch date, then never expires (until next touch) */
            $optionValue
                ->touch()
                ->expires(-1);

            $payload = [
                'success' => true,
                'message' => 'Links are now re-ordered!',
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function editpostAction()
    {

        if ($datas = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if (empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $html = [
                    'success' => '1',
                    'success_message' => $this->_('Link has been successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];

                // Prépare la weblink
                $weblink = $option_value->getObject();
                if (!$weblink->getId()) {
                    $weblink->setValueId($datas['value_id']);
                }

                // S'il y a une cover image
                if (!empty($datas['file'])) {

                    if (!empty($datas['file'])) {

                        $file = pathinfo($datas['file']);
                        $filename = $file['basename'];

                        $relative_path = $option_value->getImagePathTo("cover");
                        $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                        $img_src = Core_Model_Directory::getTmpDirectory(true) . '/' . $filename;
                        $img_dst = $folder . '/' . $filename;

                        if (!is_dir($folder)) {
                            mkdir($folder, 0777, true);
                        }

                        if (!copy($img_src, $img_dst)) {
                            throw new exception($this->_('An error occurred while saving your picture. Please try againg later.'));
                        } else {
                            $weblink->setCover($relative_path . '/' . $filename);
                        }

                        if (empty($datas['link'])) $html['success_message'] = $this->_("The image has been successfully saved");
                    }
                } else if (!empty($datas['remove_cover'])) {
                    $weblink->setCover(null);
                    if (empty($datas['link'])) $html['success_message'] = $this->_("The image has been successfully deleted");
                }

                // Sauvegarde le weblink
                $weblink->save();

                if (!empty($datas['link'])) {
                    $link_datas = $datas['link'];
                    $link_datas['hide_navbar'] = $link_datas['hide_navbar'] === "on" ? "1" : "0";
                    $link_datas['use_external_app'] = $link_datas['use_external_app'] === "on" ? "1" : "0";

                    if (empty($link_datas['url']) OR !Zend_Uri::check($link_datas['url'])) {
                        throw new Exception($this->_('Please enter a valid url'));
                    }

                    // Prépare le link
                    $link = new Weblink_Model_Weblink_Link();
                    if (!empty($link_datas['link_id'])) {
                        $link->find($link_datas['link_id']);
                    }

                    $is_deleted = !empty($link_datas["is_deleted"]);
                    $isNew = !$link->getId();
                    $link_datas['weblink_id'] = $weblink->getId();

                    // Test s'il y a un picto
                    if (!empty($link_datas['picto']) AND file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $link_datas['picto'])) {

                        $file = pathinfo(Core_Model_Directory::getTmpDirectory() . "/" . $link_datas['picto']);
                        $filename = $file['basename'];

                        $relative_path = $option_value->getImagePathTo("pictos");
                        $folder = Application_Model_Application::getBaseImagePath() . $relative_path;
                        $img_src = Core_Model_Directory::getTmpDirectory(true) . '/' . $filename;
                        $img_dst = $folder . '/' . $filename;

                        if (!is_dir($folder)) {
                            mkdir($folder, 0777, true);
                        }

                        if (!copy($img_src, $img_dst)) {
                            throw new exception($this->_("An error occurred while saving your picto. Please try againg later."));
                        } else {
                            $link_datas['picto'] = $relative_path . '/' . $filename;
                        }
                    }
                    // Sauvegarde le link
                    $link->addData($link_datas)->save();

                    if ($is_deleted) {
                        $html['success_message'] = $this->_('Link has been successfully deleted');
                        $html['is_deleted'] = 1;
                    }
                }

                if ($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('row_', 'admin_view_default', 'weblink/application/multi/edit/row.phtml')
                        ->setCurrentLink($link)
                        ->setCurrentOptionValue($option_value)
                        ->toHtml();
                }

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

            } catch (Exception $e) {
                $html = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function cropAction()
    {

        if ($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = [
                    'success' => 1,
                    'file' => $file
                ];
            } catch (Exception $e) {
                $datas = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
        }

    }

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction()
    {
        if ($this->getCurrentOptionValue()) {
            $weblink = new Weblink_Model_Weblink();
            $result = $weblink->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "links-" . date("Y-m-d_h-i-s") . ".yml", "text/x-yaml");
        }
    }

}