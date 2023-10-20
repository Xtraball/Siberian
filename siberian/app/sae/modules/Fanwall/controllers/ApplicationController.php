<?php

use Fanwall\Form\Post as FormPost;
use Fanwall\Form\Settings as FormSettings;
use Fanwall\Form\Post\Toggle as FormPostToggle;
use Fanwall\Form\Post\Pin as FormPostPin;
use Fanwall\Form\Post\Delete as FormPostDelete;
use Fanwall\Model\Fanwall;
use Fanwall\Model\Post;
use Fanwall\Model\Approval;
use Siberian\Exception;
use Siberian\Feature;
use Siberian\Json;

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

            // We do not need these elements!
            $form->removeElement('image_uploader');
            $form->removeElement('image_uploader_hidden');

            if ($form->isValid($values)) {

                $post = new Post();
                $post = $post->find($values['post_id']);

                // Images
                $allImages = [];
                foreach ($values['images'] as $uuid => $images) {
                    foreach ($images as $image) {
                        $allImages[] = Siberian_Feature::saveImageForOption($optionValue, $image);
                    }
                }
                $values['image'] = implode_polyfill(',', $allImages);
                $text = trim($values['text']);
                if (empty($text) && empty($values['image'])) {
                    throw new Exception(p__('fanwall', 'You must send at least a message or a picture.'));
                }

                $saveToHistory = false;
                $archivedPost = null;
                if ($post->getId()) {
                    $saveToHistory = true;
                    $archivedPost = [
                        'id' => (integer)$post->getId(),
                        'customerId' => (integer)$post->getCustomerId(),
                        'title' => (string)$post->getTitle(),
                        'subtitle' => (string)$post->getSubtitle(),
                        'text' => (string)$post->getText(),
                        'image' => (string)$post->getImage(),
                        'date' => (integer)$post->getDate(),
                        'latitude' => (float)$post->getLatitude(),
                        'longitude' => (float)$post->getLongitude(),
                        'locationShort' => (string)$post->getLocationShort(),
                    ];
                }

                // Replacing the visual date, with the timestamp, date name/id is suffixed with a uniqid()!
                foreach ($values as $key => $value) {
                    if (preg_match("#^date_#", $key)) {
                        $values['date'] = $value / 1000;
                        break;
                    }
                }

                $values['text'] = base64_encode($values['text']);

                $post
                    ->addData($values)
                    ->addData([
                        'is_active' => true,
                    ])
                    ->setIsVisible(true)
                    ->setStatus('published')
                    ->save();

                // Ok everything good, we can insert archive if edit
                if ($saveToHistory) {
                    try {
                        $history = Json::decode($post->getHistory());
                    } catch (\Exception $e) {
                        $history = [];
                    }

                    $history[] = $archivedPost;

                    $post
                        ->setHistory(Json::encode($history))
                        ->save();
                }

                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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

    public function approvePostAction()
    {
        try {
            $request = $this->getRequest();
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $values = $request->getPost();

            $post = (new Post())->find($values['post_id']);
            if (!$post || !$post->getId()) {
                throw new Exception(p__('fanwall', 'This post does not exists.'));
            }

            $approval = (new Approval())->find(
                [
                    'value_id' => $valueId,
                    'post_id' => $values['post_id']
                ]
            );

            if (!$approval || !$approval->getId()) {
                throw new Exception(p__('fanwall', 'This approval request does not exists.'));
            }

            // Remove the approval request
            $approval->delete();

            $post
                ->setIsVisible(true)
                ->setStatus('published')
                ->save();

            $payload = [
                'success' => true,
                'message' => p__('fanwall', 'The post is approved & published!'),
                'badge' => [
                    'valueId' => $valueId,
                    'count' => (new Fanwall())->getPendingActions($optionValue)
                ]
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function rejectPostAction()
    {
        try {
            $request = $this->getRequest();
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $values = $request->getPost();

            $post = (new Post())->find($values['post_id']);
            if (!$post || !$post->getId()) {
                throw new Exception(p__('fanwall', 'This post does not exists.'));
            }

            $approval = (new Approval())->find(
                [
                    'value_id' => $valueId,
                    'post_id' => $values['post_id']
                ]
            );

            if (!$approval || !$approval->getId()) {
                throw new Exception(p__('fanwall', 'This approval request does not exists.'));
            }

            // Remove the approval request
            $approval->delete();

            $post
                ->setIsVisible(false)
                ->setStatus('rejected')
                ->save();

            $payload = [
                'success' => true,
                'message' => p__('fanwall', 'The post is rejected!'),
                'badge' => [
                    'valueId' => $valueId,
                    'count' => (new Fanwall())->getPendingActions($optionValue)
                ]
            ];

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
    public function editSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new FormSettings();
            if ($form->isValid($values)) {

                $fanWall = (new Fanwall())->find($optionValue->getId(), 'value_id');
                $fanWall
                    ->addData($values);

                $icons = [
                    'icon_post' => 'Posts',
                    'icon_nearby' => 'Nearby',
                    'icon_map' => 'Map',
                    'icon_gallery' => 'Gallery',
                    'icon_new' => 'New post',
                    'icon_profile' => 'Profile',
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
                    'success' => true,
                    'message' => __('Success.'),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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
    public function loadFormAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $postId = $request->getParam('post_id', null);
            $post = (new Post())->find($postId);

            if (!$post || !$post->getId()) {
                throw new Exception(p__('fanwall', 'This post entry do not exists!'));
            }

            $tmpData = $post->getData();

            $tmpData['text'] = base64_decode($tmpData['text']);

            $form = new FormPost();
            $form->removeNav('nav-fanwall-post');
            $form->populate($tmpData);
            $form->setValueId($optionValue->getId());
            $form->setPostId($post->getId());
            $form->setAttrib('id', 'fanwall-post-edit-' . $post->getId());
            $form->setDate($tmpData['date']);
            $form->getElement('text')->setAttrib('id', 'fanwall2-edit-post-' . $postId);
            $form->loadImages();
            $form->loadFormSubmit();

            $payload = [
                'success' => true,
                'form' => $form->render(),
                'message' => __('Success.'),
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Toggle place
     */
    public function togglePostAction()
    {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new FormPostToggle();

            if ($form->isValid($values)) {
                $post = new Post();
                $result = $post
                    ->find($values['post_id'])
                    ->toggle();

                /** Update touch date, then never expires (until next touch) */
                $this
                    ->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'state' => $result,
                    'message' => ($result) ?
                        p__('fanwall', 'Post is published') :
                        p__('fanwall', 'Post is unpublished'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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
     * Toggle place
     */
    public function pinPostAction()
    {
        try {
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new FormPostPin();

            if ($form->isValid($values)) {
                $post = new Post();
                $result = $post
                    ->find($values['post_id'])
                    ->toggleSticky();

                /** Update touch date, then never expires (until next touch) */
                $this
                    ->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = [
                    'success' => true,
                    'state' => $result,
                    'message' => ($result) ?
                        p__('fanwall', 'Post is pinned') :
                        p__('fanwall', 'Post is unpinned'),
                ];
            } else {
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
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
     * Delete place
     */
    public function deletePostAction()
    {
        $values = $this->getRequest()->getPost();

        $form = new FormPostDelete();
        if ($form->isValid($values)) {
            $post = new Post();
            $post->find($values['post_id']);
            $post->delete();

            $this
                ->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = [
                'success' => true,
                'message' => p__('fanwall', 'Post successfully deleted.'),
            ];
        } else {
            $html = [
                'error' => true,
                'message' => $form->getTextErrors(),
                'errors' => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($html);
    }

}
