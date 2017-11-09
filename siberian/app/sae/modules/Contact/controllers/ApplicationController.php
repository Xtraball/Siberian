<?php

class Contact_ApplicationController extends Application_Controller_Default
{
    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
                "homepage_app_#APP_ID#",
            ),
        ),
    );

    public function editpostAction() {

        if($data = $this->getRequest()->getPost()) {

            try {
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if(empty($data['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                $html = '';
                $contact = new Contact_Model_Contact();
                $contact->find($option_value->getId(), 'value_id');

                if(!empty($data['file'])) {

                    $file = pathinfo($data['file']);
                    $filename = $file['basename'];
                    $relative_path = $option_value->getImagePathTo();
                    $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                    $img_dst = $folder.'/'.$filename;
                    $img_src = Core_Model_Directory::getTmpDirectory(true).'/'.$filename;

                    if(!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    if(!copy($img_src, $img_dst)) {
                        throw new exception($this->_('An error occurred while saving your picture. Please try again later.'));
                    } else {
                        $data['cover'] = $relative_path.'/'.$filename;
                    }
                }
                else if(!empty($data['remove_cover'])) {
                    $data['cover'] = null;
                }

                $contact->setData($data);

                if($contact->getStreet() AND $contact->getPostcode() AND $contact->getCity()) {
                    $latlon = Siberian_Google_Geocoding::getLatLng(array(
                        "street" => $contact->getStreet(),
                        "postcode" => $contact->getPostcode(),
                        "city" => $contact->getCity()
                    ));

                    if(!empty($latlon[0]) && !empty($latlon[1])) {
                        $contact->setLatitude($latlon[0])
                            ->setLongitude($latlon[1])
                        ;
                    }
                } else {
                    $contact->setLatitude(null)
                        ->setLongitude(null)
                    ;
                }

                $contact->save();

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function cropAction() {

        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => $this->_('Info successfully saved'),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
         }

    }

}