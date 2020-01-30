<?php

namespace Sourcecode\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Sourcecode
 * @package Sourcecode\Form
 */
class Sourcecode extends FormAbstract
{
    /**
     *
     */
    public function init()
    {
        parent::init();

        /** Bind as a onchange form */
        self::addClass('create', $this);

        $this->setAction(__path('/sourcecode/application/editpost'));

        $terms = $this->addSimpleTextarea('html_code', false, false, ['ckeditor' => 'source']);
        $terms
            ->setNewDesignLarge()
            ->setRichtext();

        $this->addSimpleHidden('value_id');

        $this->addNav('save', 'Save', false, true);
    }

    /**
     * @param $htmlcode
     * @return $this
     */
    public function setHtmlCode($htmlcode): self
    {
        $this->getElement('html_code')->setValue($htmlcode);

        return $this;
    }
}