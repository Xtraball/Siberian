<?php
/**
 * Class Promotion_Form_Promotion_Delete
 */
class Promotion_Form_Promotion_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/promotion/application/deletepost'))
            ->setAttrib('id', 'form-promotion-delete')
            ->setConfirmText('You are about to remove this Promotion ! Are you sure ?');
        ;

        // Bind as a delete form!
        self::addClass('delete', $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('promotion')
            ->where('promotion.promotion_id = :value')
        ;

        $place_id = $this->addSimpleHidden('promotion_id', __('Promotion'));
        $place_id->addValidator('Db_RecordExists', true, $select);
        $place_id->setMinimalDecorator();

        $value_id = $this->addSimpleHidden('value_id');
        $value_id
            ->setRequired(true)
        ;

        $this->addMiniSubmit();
    }
}