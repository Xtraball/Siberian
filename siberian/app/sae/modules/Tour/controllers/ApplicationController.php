<?php

use Siberian\Exception;

class Tour_ApplicationController extends Application_Controller_Default
{

    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            if (empty($data)) {
                throw new Exception(p__('tour', 'Params are missing!'));
            }

            if (!array_key_exists('step-elem-id', $data) ||
                !array_key_exists('step-language-code', $data)) {
                throw new Exception(p__('tour', 'Missing element to configure!'));
            }


            //Check if step exists
            $step = new Tour_Model_Step();
            $step = $step->find(array(
                'element_id' => $data['step-elem-id'],
                'language_code' => $data['step-language-code'],
                'url' => $data['step-url']
            ));
            $step_exists = $step->getId() ? true : false;

            // Delete step if needed
            if ($step_exists && $data['step-delete']) {
                $step->delete();
                $step_exists = false;
            } else {
                $step
                    ->setTitle($data['step-title'])
                    ->setLanguageCode($data['step-language-code'])
                    ->setText($data['step-text'])
                    ->setPlacement($data['step-placement'])
                    ->setElementId($data['step-elem-id'])
                    ->setUrl($data['step-url']);

                if (!$step_exists) {
                    $step->setOrderIndex($data['step-order']);
                }

                $step->save();
            }

            $payload = [
                'success' => true,
                'message' => p__('tour', 'Step saved successfully.'),
                'step_exists' => $step_exists,
                'elem_id' => $data['step-elem-id']
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function reorderAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            if (empty($data)) {
                throw new Exception(p__('tour', 'Params are missing!'));
            }

            if (!array_key_exists('new_order', $data)) {
                throw new Exception(p__('tour', 'Missing elements to configure!'));
            }

            foreach ($data['new_order'] as $step_order) {
                $step = new Tour_Model_Step();
                $step->find(['element_id' => $step_order['id']]);
                if ($step->getId()) {
                    $step->setOrderIndex($step_order['order'])->save();
                }
            }

            $payload = [
                'success' => true,
                'message' => p__('tour', 'Saved!'),
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function findforlanguageAction()
    {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();

            if (empty($data)) {
                throw new Exception(p__('tour', 'Params are missing!'));
            }

            if (!array_key_exists('language-code', $data) ||
                !array_key_exists('url', $data)) {
                throw new Exception(p__('tour', 'Missing element to configure!'));
            }

            $existing_steps = new Tour_Model_Step();
            $existing_steps = $existing_steps->findAllForJS($data['language-code'], $data['url']);

            $payload = [
                'success' => true,
                'message' => p__('tour', 'Elements loaded!'),
                'steps' => $existing_steps
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
