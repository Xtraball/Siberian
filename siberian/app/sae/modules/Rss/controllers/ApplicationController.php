<?php

use Siberian\Feature;
use Siberian\Exception;
use Siberian\Json;

/**
 * Class Rss_ApplicationController
 */
class Rss_ApplicationController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        "edit-post" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ],
        ],
        "edit-settings" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ],
        ],
        "update-feed-positions" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ],
        ],
    ];

    /**
     *
     */
    public function editPostAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__("rss","This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__("rss","Values are required!"));
            }

            $lastPosition = (new Rss_Model_Feed())->getLastPosition($optionValue->getId());

            $form = new Rss_Form_Feed();
            if ($form->isValid($values)) {

                $feed = new Rss_Model_Feed();
                $feed->find($values["feed_id"]);
                $feed->setData($values);

                Feature::formImageForOption($optionValue, $feed, $values, "thumbnail", true);

                $feed->setPosition($lastPosition + 1);
                $feed->setVersion(2);
                $feed->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => p__("rss","Feed saved"),
                ];
            } else {
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function editSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            if (!$optionValue->getId()) {
                throw new Exception(p__("rss","This feature doesn't exists!"));
            }

            if (empty($values)) {
                throw new Exception(p__("rss","Values are required!"));
            }

            $form = new Rss_Form_Feed();
            if ($form->isValid($values)) {

                $optionValue
                    ->setSettings(Json::encode($values))
                    ->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => p__("rss","Settings saved"),
                ];
            } else {
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true)
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function updateFeedPositionsAction()
    {
        try {
            $request = $this->getRequest();
            $indexes = $request->getParam("indexes", null);

            if (empty($indexes)) {
                throw new Exception(p__("rss", "Nothing to re-order!"));
            }

            foreach ($indexes as $index => $feedId) {
                $feed = (new Rss_Model_Feed())
                    ->find($feedId);

                if (!$feed->getId()) {
                    throw new Exception(p__("rss", 'Something went wrong, the feed do not exists!'));
                }

                $feed
                    ->setPosition($index + 1)
                    ->save();
            }

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
}
