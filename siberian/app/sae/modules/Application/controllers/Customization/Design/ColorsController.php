<?php

use Siberian\Json;
use Siberian\File;
use Siberian\Exception;

/**
 * Class Application_Customization_Design_ColorsController
 */
class Application_Customization_Design_ColorsController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "save-color" => [
            "tags" => [
                "css_app_#APP_ID#",
                "app_#APP_ID#"
            ],
        ],
        "save-custom" => [
            "tags" => [
                "css_app_#APP_ID#",
                "app_#APP_ID#"
            ],
        ],
    ];

    /**
     *
     */
    public function editAction()
    {
        $this->loadPartials();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $html = [
                "html" => $this->getLayout()->getPartial('content_editor')->toHtml()
            ];
            $this->getLayout()->setHtml(Json::encode($html));
        }
    }

    public function saveColorAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            if (empty($data["block_id"])) {
                throw new Exception(__("#354-00: No data sent."));
            }

            // Current application
            $application = $this->getApplication();

            /**
             * @var $block Template_Model_Block
             */
            $block = (new Template_Model_Block())->find($data["block_id"]);
            if (!$block->getId()) {
                throw new Exception("#354-01: " .
                    p__("application", "An error occurred while saving your color."));
            }

            $block->unsData();

            foreach ($data["colors"] as $key => $rgba) {
                $block->setFromRgba($key, $rgba);
            }

            $block
                ->setBlockId($data["block_id"])
                ->setAppId($application->getId())
                ->save();

            $application
                ->setGenerateScss(1)
                ->save();

            $payload = [
                "success" => true,
                "message" => __("Success"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function saveCustomAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            if (!isset($data["custom_scss"])) {
                throw new Exception("#355-00: " . p__("application", "No data sent."));
            }

            // Current application
            $application = $this->getApplication();
            $application->setData("custom_scss", $data["custom_scss"]);

            $result = Template_Model_Design::generateCss($application, false, false, true);
            if (!$result) {
                throw new Exception("#355-01: " .
                    p__("application", "SCSS Compilation error: %s", Template_Model_Design::$lastException));
            }

            $application->save();

            $payload = [
                "success" => true,
                "message" => p__("application", "SCSS successfully saved"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function exportAction ()
    {
        try {
            $application = $this->getApplication();
            $appId = $application->getId();
            $designId = $application->getDesignId();

            $tdbs = (new Template_Model_Design_Block())->findAllExport($appId);

            $datasetTbs = [];
            foreach ($tdbs as $tdb) {
                $tdbData = $tdb->getData();
                $tdbData['created_at'] = null;
                $tdbData['updated_at'] = null;

                $blockId = $tdb->getBlockId();
                $datasetTbs[$blockId] = $tdbData;
            }

            $colors = Siberian_Yaml::encode($datasetTbs);
            $filename = 'colors-app-' . $appId . '-' . date('Y-m-d_H_i_s') . '.yml';
            $exportPath = path('var/tmp/' . $filename);
            File::putContents($exportPath, $colors);

            $this->_download($exportPath, $filename, 'application/x-yaml');

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
            $this->_sendJson($payload);
        }
    }
}
