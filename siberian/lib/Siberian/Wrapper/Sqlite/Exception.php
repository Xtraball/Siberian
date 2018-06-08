<?php

/**
 * Class Siberian_Wrapper_Sqlite_Exception
 */
class Siberian_Wrapper_Sqlite_Exception extends Exception
{
    /**
     * Siberian_Wrapper_Sqlite_Exception constructor.
     * @param $query
     * @param $outputMessage
     */
    public function __construct($query, $outputMessage)
    {
        $this->message = "Error with query '$query'\n" . implode("\n", $outputMessage);
    }
}
