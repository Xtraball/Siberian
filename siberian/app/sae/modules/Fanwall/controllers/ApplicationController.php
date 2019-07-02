<?php

use Fanwall\Form\Post as FormPost;
use Fanwall\Form\Settings as FormSettings;
use Fanwall\Form\Post\Toggle as FormPostToggle;
use Fanwall\Form\Post\Pin as FormPostPin;
use Fanwall\Form\Post\Delete as FormPostDelete;
use Fanwall\Model\Fanwall;
use Fanwall\Model\Post;
use Siberian\Exception;
use Siberian\Feature;

/**
 * Class Fanwall2_ApplicationController
 */
class Fanwall_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "edit-post" => [
            "tags" => [
                //"feature_paths_valueid_#VALUE_ID#",
                //"assets_paths_valueid_#VALUE_ID#",
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

            $form = new FormPost();
            if ($form->isValid($values)) {

                // Replacing the visual date, with the timestamp, date name/id is suffixed with a uniqid()!
                foreach ($values as $key => $value) {
                    if (preg_match("#^date_#", $key)) {
                        $values["date"] = $value / 1000;
                        break;
                    }
                }

                $post = new Post();
                $post
                    ->addData($values)
                    ->addData([
                        "is_active" => true,
                    ])
                ;

                Feature::formImageForOption($optionValue, $post, $values, "image", true);

                $post->save();

                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => __("Success."),
                ];
            } else {
                /** Do whatever you need when form is not valid */
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

            $form = new FormSettings();
            if ($form->isValid($values)) {

                $fanWall = (new Fanwall())->find($optionValue->getId(), "value_id");
                $fanWall
                    ->addData($values);

                $icons = [
                    "icon_post" => "Posts",
                    "icon_nearby" => "Nearby",
                    "icon_map" => "Map",
                    "icon_gallery" => "Gallery",
                    "icon_new" => "New post",
                ];

                foreach ($icons as $column => $label) {
                    Feature::formImageForOption($optionValue, $fanWall, $values, $column, true);
                }

                $fanWall
                    ->save();

                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => __("Success."),
                ];
            } else {
                /** Do whatever you need when form is not valid */
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
    public function loadFormAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $postId = $request->getParam("post_id", null);
            $post = (new Post())->find($postId);

            if (!$post->getId()) {
                throw new Exception(p__("fanwall","This post entry do not exists!"));
            }

            $tmpData = $post->getData();

            $form = new FormPost();
            $form->removeNav("nav-fanwall-post");
            $form->populate($tmpData);
            $form->setValueId($optionValue->getId());
            $form->setPostId($post->getId());
            $form->setDate($tmpData["date"]);
            $form->loadFormSubmit();

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
     * Toggle place
     */
    public function togglePostAction() {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new FormPostToggle();

            if ($form->isValid($values)) {
                $post = new Post();
                $result = $post
                    ->find($values["post_id"])
                    ->toggle();

                /** Update touch date, then never expires (until next touch) */
                $this
                    ->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "state" => $result,
                    "message" => ($result) ?
                        p__("fanwall", "Post is published") :
                        p__("fanwall", "Post is unpublished"),
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
     * Toggle place
     */
    public function pinPostAction() {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new FormPostPin();

            if ($form->isValid($values)) {
                $post = new Post();
                $result = $post
                    ->find($values["post_id"])
                    ->toggleSticky();

                /** Update touch date, then never expires (until next touch) */
                $this
                    ->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "state" => $result,
                    "message" => ($result) ?
                        p__("fanwall", "Post is pinned") :
                        p__("fanwall", "Post is unpinned"),
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
     * Delete place
     */
    public function deletePostAction() {
        $values = $this->getRequest()->getPost();

        $form = new FormPostDelete();
        if ($form->isValid($values)) {
            $post = new Post();
            $post->find($values["post_id"]);
            $post->delete();

            $this
                ->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = [
                "success" => true,
                "message" => p__("fanwall", "Post successfully deleted."),
            ];
        } else {
            $html = [
                "error" => true,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($html);
    }

}
