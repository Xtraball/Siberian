<?php

class Siberian_Debug_Collector_Sql extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable {

    protected $profiles = array();

    /**
     * @param $profile
     */
    public function addProfile($profile){
        $this->profiles[] = $profile;
    }

    /**
     * @return array
     */
    public function collect() {
        $data = array(
            'count' => count($this->profiles),
            'messages' => array(),
        );

        foreach ($this->profiles as $profile) {
            $time = round($profile->getElapsedSecs(), 5);

            $data["messages"][] = array(
                "message" => sprintf("Time: %s > %s", str_pad($time, 7, "0", STR_PAD_LEFT), $profile->getQuery()),
                "is_string" => true,
                "label" => "SQL",
                "time" => microtime(true)
            );
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sql';
    }

    /**
     * @return array
     */
    public function getWidgets() {
        return array(
            "sql" => array(
                "icon" => "database",
                "widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "sql.messages",
                "default" => "[]"
            ),
            "sql:badge" => array(
                "map" => "sql.count",
                "default" => 0
            )
        );
    }
}
