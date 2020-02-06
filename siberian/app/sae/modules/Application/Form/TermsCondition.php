<?php

/**
 * Class Application_Form_TermsCondition
 */
class Application_Form_TermsCondition extends Siberian_Form_Abstract
{
    /**
     *
     */
    public function init()
    {
        parent::init();

        /** Bind as a onchange form */
        self::addClass('create', $this);
    }

    /**
     * @param $path
     * @param $type
     * @param $key
     * @param string $content
     * @throws Zend_Form_Exception
     */
    public function generate($path, $type, $key, $content = '')
    {
        $this->setAction(__path($path));

        $terms = $this->addSimpleTextarea($key, false, ['ckeditor' => 'complete']);
        $terms
            ->setAttrib('id', $key . '_' . $type)
            ->setNewDesignLarge()
            ->setRichtext();

        $terms->setValue($content);

        $typeHidden = $this->addSimpleHidden('type');
        $typeHidden
            ->setAttrib('id', 'type_' . $type)
            ->setValue($type);

        $this->addNav('save', 'Save', false, true);
    }
}