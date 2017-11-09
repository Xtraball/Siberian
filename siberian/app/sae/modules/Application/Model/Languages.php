<?php

class Application_Model_Languages {

    private static $_supportedLanguages = array(
        "pt-PT" => "Brazilian Portuguese",
        "da" => "Danish",
        "nl" => "Dutch",
        "en-US" => "English",
        "en-AU" => "English_Australian",
        "en-CA" => "English_CA",
        "en-UK" => "English_UK",
        "de" => "German",
        "el" => "Greek",
        "fi" => "Finnish",
        "fr-FR" => "French",
        "fr-CA" => "French_CA",
        "id" => "Indonesian",
        "it" => "Italian",
        "ja" => "Japanese",
        "ko" => "Korean",
        "ma" => "Malay",
        "es-ES" => "Spanish",
        "es-MX" => "Spanish_MX",
        "pt-BR" => "Portuguese",
        "ru" => "Russian",
        "sv" => "Swedish",
        "nb" => "Norwegian",
        "th" => "Thai",
        "tr" => "Turkish",
        "vi" => "Vietnamese",
        "zh-Hans" => "Simplified Chinese",
        "zh-Hant" => "Traditional Chinese",
    );

    public static function getSupportedLanguages() {
        return self::$_supportedLanguages;
    }

    public static function getLabelFromCodeIso($iso) {
        return self::$_supportedLanguages[$iso];
    }
}