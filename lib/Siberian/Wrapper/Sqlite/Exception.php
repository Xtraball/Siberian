<?php

class Siberian_Wrapper_Sqlite_Exception extends Exception {

	public function __construct($query, $outputMessage) {
		$this->message = "Error with query '$query'\n".implode("\n", $outputMessage);
	}
}
