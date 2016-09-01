<?php

class Application_Model_Languages {

    private static $_supportedLanguages = array(
        "da" => "Danish", 
        "de" => "German", 
        "en-UK" => "English (UK)", 
        "en-US" => "English (US)", 
        "es-ES" => "Spainish",
        "fi" => "Finnish", 
        "fr-FR" => "French", 
        "it" => "Italian", 
        "pt-BR" => "Portugese (BR)", 
        "pt-PT" => "Portugese (PT)", 
        "sv" => "Swedish", 
        "zh" => "Chinese", 
    );

    public static function getSupportedLanguages() {
        return self::$_supportedLanguages;
    }
}