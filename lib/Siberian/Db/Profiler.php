<?php

class Siberian_Db_Profiler extends Zend_Db_Profiler {

    /**
     * counter of the total elapsed time
     * @var double
     */
    protected $_totalElapsedTime;

    /**
     * Siberian_Db_Profiler constructor.
     * @param bool $enabled
     */
    public function __construct($enabled = false) {
        parent::__construct($enabled);
    }

    /**
     * @param int $queryId
     */
    public function queryEnd($queryId) {
        $state = parent::queryEnd($queryId);

        if (!$this->getEnabled() || $state == self::IGNORED) {
            return;
        }

        # get profile of the current query
        $profile = $this->getQueryProfile($queryId);

        # update totalElapsedTime counter
        $this->_totalElapsedTime += $profile->getElapsedSecs();

        # create the message to be logged
        $message = "\r\nElapsed Secs: " . round($profile->getElapsedSecs(), 5) . "\r\n";
        $message .= "Query: " . $profile->getQuery() . "\r\n";

        Siberian_Debug::message($message, "SQL");
    }
}