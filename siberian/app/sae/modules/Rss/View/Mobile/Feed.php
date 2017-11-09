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
        $allDays.= ' ';
        $hours.= ' ';
        $minutes.= ' ';
        
        if($allDays > 0) {
            $allDays .= $this->_('day');
            if($allDays > 1) {
                $allDays .= "s";
            }
        } else {
            $allDays = '';
        }
        if($hours > 0) {
            $hours .= $this->_('hour');
            if($hours > 1) {
                $hours .= "s";
            }
        } else {
            $hours = '';
        }
        if($minutes > 0) {
            $minutes .= $this->_('minute');
            if($minutes > 1) {
                $minutes .= "s";
            }
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