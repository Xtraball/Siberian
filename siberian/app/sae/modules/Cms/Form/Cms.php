<?php

/**
 * Class Cms_Form_Cms
 */
class Cms_Form_Cms extends Cms_Form_Base {

    /**
     * @var bool
     */
    public $display_back_button = false;

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/cms/application_page/editpostv2'))
            ->setAttrib('id', 'form-cms')
        ;

        $nav_group = $this->addNav('nav-cms', __('Save'), $this->display_back_button);

        $this->addSimpleHidden('page_id');

        $value_id = $this->addSimpleHidden('value_id');
        $value_id->setRequired(true);

        $this->addSections();

        $nav_group->addElement($this->getElement('sections_html'));
    }

    /**
     * @param $blocks
     * @param $value_id
     * @return $this
     * @throws Siberian_Exception
     */
    public function loadBlocks($blocks) {

        $value_id = $this->getElement('value_id')->getValue();
        if(empty($value_id)) {
            throw new Siberian_Exception(__('Unable to load CMS Blocks without value_id.'));
        }

        $container = $this->getElement('sections_html_container');

        $html_blocks = array();
        foreach($blocks as $block) {
            $block_template = str_replace('/block/', '/block_v2/', $block->getTemplate());

            switch($block->getType()) {
                case 'text':
                    $form = new Cms_Form_Block_Text();
                    break;
                case 'image':
                    $form = new Cms_Form_Block_Image();
                    break;
                case 'video':
                    $form = new Cms_Form_Block_Video();
                    break;
                case 'address':
                    $form = new Cms_Form_Block_Address();
                    if($this->feature_code == 'places') {
                        $form->setRequired(true);
                    }
                    break;
                case 'button':
                    $form = new Cms_Form_Block_Button();
                    break;
                case 'file':
                    $form = new Cms_Form_Block_File();
                    break;
                case 'slider':
                    $form = new Cms_Form_Block_Slider();
                    break;
                case 'cover':
                    $form = new Cms_Form_Block_Cover();
                    break;
                default:
                    throw new Siberian_Exception(__('This block type doesn\'t exist'));
            }

            $form->setValueId($value_id);

            $block_forms[] = [
                'template' => $block_template,
                'block' => $block,
                'form' => $form
            ];
        }

        return $block_forms;
    }

    /**
     * @param $page_id
     */
    public function setPageId($page_id) {
        $this->getElement('page_id')->setValue($page_id)->setRequired(true);
    }
}