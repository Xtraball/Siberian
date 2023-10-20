<?php

namespace Siberian\Wrapper;

/**
 * Class Siberian_Wrapper_Sqlite_Exception
 */
class SqliteException extends \Exception
{
    /**
     * Siberian_Wrapper_Sqlite_Exception constructor.
     * @param $query
     * @param $outputMessage
     */
    public function __construct($query, $outputMessage)
    {
        $this->message = "Error with query '$query'\n" . implode_polyfill("\n", $outputMessage);
    }
}
