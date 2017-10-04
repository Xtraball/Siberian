 <?php
/**
 * Class Job_Form_Place_Delete
 */
class Job_Form_Place_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/job/place/deletepost"))
            ->setAttrib("id", "form-place-delete")
            ->setConfirmText("You are about to remove this Place ! Are you sure ?");
        ;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('job_place')
            ->where('job_place.place_id = :value')
        ;

        $place_id = $this->addSimpleHidden("place_id", __("Place"));
        $place_id->addValidator("Db_RecordExists", true, $select);
        $place_id->setMinimalDecorator();

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;

        $mini_submit = $this->addMiniSubmit();
    }
}