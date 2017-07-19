<?php

class Wordpress_ApplicationController extends Application_Controller_Default
{

    protected $_categories = array();

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "homepage_app_#APP_ID#"
            ),
        )
    );

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {
                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception(__('#122: An error occurred while saving'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                if(empty($datas['url'])) {
                    throw new Exception(__('Please enter a valid url'));
                }

                // Récupère le Wordpress en cours et met à jour son url
                $wordpress = $option_value->getObject();
                $wordpress->setUrl($datas['url']);

                // Appelle le Wordpress (Vérifie si l'url passée en paramètre est OK)
                $wordpress->checkModule();

                if(!$wordpress->getId()) {
                    $wordpress->setValueId($datas['value_id'])
                        ->setUrl($datas['url'])
                        ->save()
                    ;
                }

                // Sauvegarde le Wordpress
                $wordpress->save();

                // Récupère les catégory_ids déjà en base
                $category_ids = $wordpress->getCategoryIds();
                // Récupère les category_ids passés en post

                $datas['category_ids'] = !empty($datas['category_ids']) ? $datas['category_ids'] : array();

                // Filtre les catégories à supprimer
                $category_ids_to_delete = array_diff($category_ids, $datas['category_ids']);
                // Supprime les catégories décochées
                if(!empty($category_ids_to_delete)) {
                    $category = new Wordpress_Model_Wordpress_Category();
                    $categories = $category->findAll(array('wp_id' => $wordpress->getId(), 'wp_category_id IN (?)' => $category_ids_to_delete));
                    foreach($categories as $category) $category->delete();
                }
                // Filtre les nouvelles catégories
                $category_ids_to_save = array_diff($datas['category_ids'], $category_ids);
                // Insert en base les nouvelles catégories
                foreach($category_ids_to_save as $category_id) {
                    $category = new Wordpress_Model_Wordpress_Category();
                    $category->addData(array(
                        'wp_id' => $wordpress->getId(),
                        'wp_category_id' => $category_id
                    ))->save();
                }

                // Vide la cache
                Zend_Registry::get('cache')->remove('wordpress_cache_'.sha1($wordpress->getId()));

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);

                // Retourne le message de success
                $html = array(
                    'success' => 1,
                    'success_message' => __('Categories successfully saved'),
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

    public function searchAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {
                // Déclaration des variables
                $categories = array();

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception(__('#123: An error occurred while saving'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                // Appelle le Wordpress et récupère la réponse
                $wordpress = $option_value->getObject();
                $root_category = $wordpress->getRemoteRootCategory($datas['url']);

                // Test si les données sont OK
                if($root_category->getChildren()) {

                    // Sauvegarde le Wordpress
                    if(!$wordpress->getId()) {
                        $wordpress->setValueId($datas['value_id']);
                    }
                    $wordpress->setUrl($datas['url'])
                        ->save()
                    ;

                    $html = $this->getLayout()->addPartial('categories_html', 'admin_view_default', 'wordpress/application/edit/categories.phtml')
                        ->setCategory($root_category)
                        ->setCheckAllCategories(true)
                        ->toHtml()
                    ;

                    $html = array(
                        'success' => 1,
                        'categories_html' => $html
                    );

                }
                else {
                    throw new Exception(__("We are sorry but our Wordpress plugin hasn\'t been detected on your website. Please be sure it is correctly installed and activated."));
                }

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

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $wordpress = new Wordpress_Model_Wordpress();
            $result = $wordpress->exportAction($this->getCurrentOptionValue());

            $this->_download($result, "wordpress-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }

}