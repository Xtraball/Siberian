<?php

interface Core_Model_Exporter {

    /** Export the complete Feature with data */
    public function exportAction();

    /** Import YML to a new feature */
    public function importAction();

}