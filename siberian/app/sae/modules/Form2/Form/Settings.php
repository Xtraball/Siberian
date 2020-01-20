<?php

namespace Form2\Form;

use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Settings
 * @package Form2\Form
 */
class Settings extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/form2/application/edit-settings'))
            ->setAttrib('id', 'form2-form-settings');

        /** Bind as a create form */
        self::addClass('create', $this);

        $this->addSimpleText('email', p__('form2', 'Recipient emails'));

        $text = p__('form2', 'You can add multiple e-mails, separated by a coma.');
        $rawHint = <<<HTML
<div class="alert alert-info">
    {$text}
</div>
HTML;

        $this->addSimpleHtml('raw_hint', $rawHint, ['class' => 'col-md-offset-3 col-md-7']);

        $inAppHistory = $this->addSimpleCheckbox('enable_history',
            p__('form2', 'Enable in-app history'));
        $inAppHistory->setDescription(p__('form2', 'Logged-in users will be able to see & review their submissions.'));

        $this->addSimpleSelect('design', p__('form2', 'Design'), [
            'list' => p__('form2', 'List'),
            'card' => p__('form2', 'Card'),
        ]);


        $valueId = $this->addSimpleHidden('value_id');
        $valueId
            ->setRequired(true);

        $submit = $this->addSubmit(p__('form2', 'Save'));
        $submit->addClass('pull-right');
    }
}