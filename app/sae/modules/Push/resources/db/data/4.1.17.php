<?php

$device = new Push_Model_Android_Device();

$result = $device->getTable()->findAll();
foreach($result as $row) {
	if(!(Zend_Json::encode($row->getRegistrationId()))) {
		$row->delete();
	}
}
