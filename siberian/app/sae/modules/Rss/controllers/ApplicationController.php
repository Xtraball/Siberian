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
    public function loadFormAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $feedId = $request->getParam("feed_id", null);

            $feed = (new Rss_Model_Feed())->find($feedId);

            if (!$feed->getId()) {
                throw new Exception(p__("rss","This feed entry do not exists!"));
            }

            $form = new Rss_Form_Feed();
            $form->populate($feed->getData());
            $form->setValueId($optionValue->getId());
            $form->setFeedId($feed->getId());

            $payload = [
                "success" => true,
                "form" => $form->render(),
                "message" => __("Success."),
            ];

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

                // Testing the feed
                try {
                    $feedIo = \FeedIo\Factory::create()->getFeedIo();
                    $feedIo->read($values["link"]);
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    if (preg_match("/malformed xml string/m", $message)) {
                        throw new \Siberian\Exception(htmlspecialchars(__("The <?xml tag is missing, or the Rss feed is malformed.")));
                    } else {
                        throw $e;
                    }
                }

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

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    "rss",
                    "value_id_" . $optionValue->getId(),
                ]);

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
                "exception" => $e->getTrace(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function deleteFeedAction()
    {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new Rss_Form_Feed_Delete();
            if ($form->isValid($values)) {
                $feed = new Rss_Model_Feed();
                $feed->find($values["feed_id"]);

                $valueId = $feed->getValueId();

                $feed->delete();

                /** Update touch date, then never expires (until next touch) */
                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    "rss",
                    "value_id_" . $valueId,
                ]);

                $payload = [
                    "success" => true,
                    "message" => p__("rss", "Feed deleted."),
                ];
            } else {
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true),
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

            $form = new Rss_Form_Settings();
            if ($form->isValid($values)) {

                $values["displayThumbnail"] = (boolean) filter_var($values["displayThumbnail"], FILTER_VALIDATE_BOOLEAN);
                $values["displayCover"] = (boolean) filter_var($values["displayCover"], FILTER_VALIDATE_BOOLEAN);

                $optionValue
                    ->setSettings(Json::encode($values))
                    ->save();

                /** Update touch date, then never expires (until next touch) */
                $optionValue
                    ->touch()
                    ->expires(-1);

                // Clear cache on save!
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                    "rss",
                    "value_id_" . $optionValue->getId(),
                ]);

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
            $optionValue = $this->getCurrentOptionValue();
            $indexes = $request->getParam("indexes", null);

            if (empty($indexes)) {
                throw new Exception(p__("rss", "Nothing to re-order!"));
            }

            foreach ($indexes as $index => $feedId) {
                $feed = (new Rss_Model_Feed())
                    ->find($feedId);

                if (!$feed->getId()) {
                    throw new Exception(p__("rss", "Something went wrong, the feed do not exists!"));
                }

                $feed
                    ->setPosition($index + 1)
                    ->save();
            }

            /** Update touch date, then never expires (until next touch) */
            $optionValue
                ->touch()
                ->expires(-1);

            // Clear cache on save!
            $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                "rss",
                "value_id_" . $optionValue->getId(),
            ]);

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
