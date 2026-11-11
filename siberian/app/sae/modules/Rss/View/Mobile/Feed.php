<?php

class Rss_View_Mobile_Feed extends Core_View_Mobile_Default
{

    public function snippet($text,$length=64,$tail="...") {
        $text = trim($text);
        $txtl = strlen($text);
        if($txtl > $length) {
            for($i=1;$text[$length-$i]!=" ";$i++) {
                if($i == $length) {
                    return substr($text,0,$length) . $tail;
                }
            }
            $text = substr($text,0,$length-$i+1) . $tail;
        }
        return $text;
    }

    public function getUpdatedAt($entry) {

        $date = new Zend_Date($entry->getTimestamp());
        $now = Zend_Date::now();
        $difference = $now->sub($date);

        $seconds = $difference->toValue() % 60; $allMinutes = ($difference->toValue() - $seconds) / 60;
        $minutes = $allMinutes % 60; $allHours = ($allMinutes - $minutes) / 60;
        $hours =  $allHours % 24; $allDays = ($allHours - $hours) / 24;
        
        if($allDays > 0) {
            $allDays = $allDays . ' ' . $this->_('day') . ($allDays > 1 ? "s" : "");
        } else {
            $allDays = '';
        }
        if($hours > 0) {
            $hours = $hours . ' ' . $this->_('hour') . ($hours > 1 ? "s" : "");
        } else {
            $hours = '';
        }
        if($minutes > 0) {
            $minutes = $minutes . ' ' . $this->_('minute') . ($minutes > 1 ? "s" : "");
        } else {
            $minutes = '';
        }

        $updated_at = '';
        if($allDays != '') {
            $updated_at = $allDays;
        } elseif($hours != '') {
            $updated_at = $hours;
        } elseif($minutes != '') {
            $updated_at = $minutes;
        }

        return $updated_at;

    }
}