<?php

class Socialgaming_Model_Game extends Core_Model_Default {

    const PERIOD_WEEKLY = 0;
    const PERIOD_MONTHLY = 1;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Socialgaming_Model_Db_Table_Game';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "socialgaming-view",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    public function findCurrent($value_id) {
        $row = $this->getTable()->findCurrent($value_id);
        $this->_prepareDatas($row);
        return $this;
    }

    public function findNext($value_id) {
        $row = $this->getTable()->findNext($value_id);
        $this->_prepareDatas($row);

        return $this;
    }

    public function findDefault() {
        $this->setPeriodId(1);

        return $this;
    }

    public function getFromDateToDate() {

        $start = new Siberian_Date();
        $end = new Siberian_Date();

        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $start->setWeekday(1); break;
            case self::PERIOD_MONTHLY : $start->setDay(1); break;
        }

        $start->setBeginningOfTheDay();
        $end->setEndOfTheDay();

        return array($start, $end);
    }

    public function save() {

        if(!$this->getPeriodId()) $this->setPeriodId(self::PERIOD_WEEKLY);

        return parent::save();
    }

    public function setEndAt() {

        $now = Zend_Date::now();
        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $now->setWeekday(7); break;
            case self::PERIOD_MONTHLY : $now->setDay($now->get(Zend_Date::MONTH_DAYS)); break;
        }

        $this->setData('end_at', $now->toString('yyyy-MM-dd'));

        return $this;
    }

    public function getPeriodLabel() {
        $name = '';

        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $name = __('A week'); break;
            case self::PERIOD_MONTHLY : $name = __('A month'); break;
        }

        return $name;
    }

    public function getGamePeriodLabel() {
        $name = '';

        switch($this->getPeriodId()) {
            case self::PERIOD_WEEKLY : $name = __('Rank of the week'); break;
            case self::PERIOD_MONTHLY : $name = __('Rank of the month'); break;
        }

        return $name;
    }

    public function getPeriods() {
        return array(
            self::PERIOD_WEEKLY => __('A week'),
            self::PERIOD_MONTHLY => __('A month')
        );
    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null) {
        if($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $socialgaming_model = new Socialgaming_Model_Game();
            $socialgamings = $socialgaming_model->findAll(array(
                "value_id = ?" => $value_id,
            ));

            $socialgaming_data = array();
            foreach($socialgamings as $socialgaming) {
                $socialgaming_data[] = $socialgaming->getData();
            }

            $dataset = array(
                "option" => $current_option->forYaml(),
                "socialgaming" => $socialgaming_data,
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
                throw new Exception("#089-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#089-01: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if(isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            if(isset($dataset["socialgaming"])) {
                foreach($dataset["socialgaming"] as $socialgaming) {
                    $new_socialgaming = new Socialgaming_Model_Game();
                    $new_socialgaming
                        ->setData($socialgaming)
                        ->setPeriodId($socialgaming["period_id"])
                        ->setData("value_id", $application_option->getId())
                        ->unsData("id")
                        ->unsData("game_id")
                        ->save()
                    ;
                    /** Use sleep to shift the created_at time */
                    sleep(1);
                }

            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }
}
