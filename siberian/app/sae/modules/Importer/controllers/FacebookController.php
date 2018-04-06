<?php

/**
 * Class Importer_FacebookController
 */
class Importer_FacebookController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        'parse' => [
            'tags' => [
                'homepage_app_#APP_ID#'
            ],
        ],
    ];

    /**
     *
     */
    public function getmodaltemplateAction()
    {
        $request = $this->getRequest();

        $data = $request->getParams();
        $color = $data['color'] ? $data['color'] : 'red';

        $layout = $this->getLayout();
        $layout
            ->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')
            ->setTitle(__('Import features from Facebook'))
            ->setBorderColor('border-'.$color);

        $layout->addPartial(
                'modal_content',
                'admin_view_default',
                '/application/customization/index/import_fb_modal.phtml'
            )
            ->setBorderColor('border-'.$color)
            ->setColor('color-'.$color)
            ->setFontColor('font-blue');

        $payload = [
            'modal_html' => $layout->render()
        ];

        $this->_sendJson($payload);
    }

    public function checkAction() {
        $data = $this->getRequest()->getParams();
        $pageId = $data['page_url'];

        if ($pageId) {
            $importFb = new Importer_Model_FacebookParser();
            $token = $importFb->getToken();

            if ($token) {
                $page_id = str_replace('https://wwww.facebook.com/');
            } else {
                $payload = [
                    'error' => true,
                    'message' => __('Sorry, it seems that Facebook credentials are invalid. Please contact an administrator.')
                ];
            }
        } else {
            $payload = [
                'error' => true,
                'message' => __('Sorry, we need a page id.')
            ];
        }

        $this->_sendJson($payload);
    }

    public function parseAction() {
        $data = $this->getRequest()->getParams();

        try {
            $features = [];
            if ($pageId = $data['page_id']) {
                $importFb = new Importer_Model_FacebookParser();
                $token = $importFb->getToken();

                if ($token) {
                    $page_data = $importFb->parsePage($pageId, $token);

                    if (!$page_data) {
                        $payload = [
                            'error' => true,
                            'message' => __('Sorry, no data was found for this page. Maybe you used the wrong page id?')
                        ];
                    } else {
                        $pageId = $page_data['id'];
                        $app_id = $this->getApplication()->getId();
                        $albums = $importFb->parsePageAlbums($pageId, $token);
                        $contact_infos = $page_data;
                        $messages = [
                            'facebook' => false,
                            'contact' => false,
                            'image' => false,
                            'calendar' => false,
                            'places' => false
                        ];

                        //Import 1 : Facebook
                        $importerFb = new Importer_Model_SocialFacebook();
                        if ($contact_infos['link']) {
                            if ($value_id = $importerFb->createOption($app_id, 'facebook')) {
                                $data_fb = array(
                                    'value_id' => $value_id,
                                    'fb_user' => $contact_infos['link']
                                );
                                $result = $importerFb->importFromFacebook($data_fb);
                                $messages['facebook'] = $result;

                                $features[] = $value_id;
                            }
                        }
                        //Import 2 : Contacts
                        $importerContact = new Importer_Model_Contact();
                        if ($value_id = $importerContact->createOption($app_id, 'contact')) {
                            $contact_infos['value_id'] = $value_id;
                            $result = $importerContact->importFromFacebook($contact_infos, $app_id);
                            $messages['contact'] = $result;

                            $features[] = $value_id;
                        }
                        //Import 3 : Gallery
                        $importerGallery = new Importer_Model_ImageGallery();
                        if ($albums['data'] AND count($albums['data'])) {
                            $data['albums'] = $albums['data'];
                            if ($value_id = $importerGallery->createOption($app_id, 'image_gallery')) {
                                $data['value_id'] = $value_id;
                                $data['page_id'] = $pageId;
                                $result = $importerGallery->importFromFacebook($data, $app_id);
                                $messages['image'] = $result;

                                $features[] = $value_id;
                            }
                        }
                        //Import 4 : Calendar
                        if ($page_data['events']) {
                            $importerEvent = new Importer_Model_Event();
                            if ($value_id = $importerEvent->createOption($app_id, 'calendar')) {
                                $event_infos = array(
                                    'value_id' => $value_id,
                                    'name' => $contact_infos['name'] ? $contact_infos['name'] : $pageId,
                                    'event_type' => 'fb',
                                    'url' => $pageId
                                );
                                $result = $importerEvent->importFromFacebook($event_infos, $app_id);
                                $messages['calendar'] = $result;

                                $features[] = $value_id;
                            }
                        }
                        //Import 5 : Places
                        if ($contact_infos['location']['latitude'] AND $contact_infos['location']['longitude']) {
                            $importerPlaces = new Importer_Model_Places();
                            if ($value_id = $importerPlaces->createOption($app_id, 'places')) {
                                $contact_infos['value_id'] = $value_id;
                                $result = $importerPlaces->importFromFacebook($contact_infos, $app_id);
                                $messages['places'] = $result;

                                $features[] = $value_id;
                            }
                        }

                        $payload = [
                            'success' => true,
                            'messages' => $messages,
                            'real_id' => $pageId
                        ];
                    }
                } else {
                    $payload = [
                        'success' => false,
                        'message' => __('Sorry, it seems that Facebook credentials are invalid. Please contact an administrator.')
                    ];
                }
            } else {
                $payload = [
                    'success' => false,
                    'message' => __('Sorry, we need a page id.')
                ];
            }
        } catch (Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage()
            ];

            // Clean-up possible created options ...
            foreach ($features as $valueId) {
                $optionValue = (new Application_Model_Option_Value())
                    ->find($valueId);

                if ($optionValue->getId()) {
                    $optionValue->delete();
                }
            }
        }


        $this->_sendJson($payload);
    }

    public function finalizeAction() {
        $data = $this->getRequest()->getParams();
        $pageId = $data['real_id'] ? $data['real_id'] : $data['page_id'];
        if($pageId) {
            try{
                $this->getApplication()->addData(array('facebook_linked_page' => $pageId))->save();
                $html = array(
                    'success' => 1
                );
            } catch(Siberian_Exception $e) {
                $html = array(
                    'success' => 0
                );
            }
        } else {
            $html = array(
                'success' => 0
            );
        }

        $this->_sendJson($html);
    }
}

