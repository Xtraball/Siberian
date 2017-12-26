<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/2/17
 * Time: 8:24 PM
 *
 * @author      Muntashir Al-Islam <muntashir.islam96@gmail.com>
 * @version     2.0.0
 * @copyright   2017 (c) All rights reserved
 * @license     MIT License
 */

namespace PListEditor;

require_once __DIR__."/PListProperty.php";
require_once __DIR__."/PListPropertyList.php";

/**
 * Class PListEditor
 *
 * NOTE: Methods of the class will not return any of the DOM classes
 *      in any circumstances.
 *
 * @package PListEditor
 */
class PListEditor
{
    /** @var \DOMDocument $plist FIXME: should be protected */
    public $plist;

    protected $createMode = false,
              $editMode   = false,
              $fileName;

    /**
     * Read from the given string
     *
     * @param string $string an xml string to be DOM Manipulated
     * @return bool true if the string is a valid xml otherwise false
     * @since 1.0.0
     */
    public function read($string){
        $this->plist = new \DOMDocument('1.0', 'UTF-8');
        $this->plist->formatOutput       = true;
        $this->plist->preserveWhiteSpace = false;

        // NOTE: JSON files are NOT supported
        if(@$this->plist->loadXML($string) !== true){   // && ($this->plist = json_decode($string)) === null){
            return false;
        }
        return true;
    }

    /**
     * Read from the given plist file
     *
     * NOTE: this uses file_get_contents function, which may not
     *      be supported on some hosting sites.
     *
     * @param string $file Path to the plist file
     * @return bool
     * @since 1.0.0
     */
    public function readFile($file){
        if(is_readable($file)){                        // Read from file if the file is readable
            $this->editMode = true;
            $this->fileName = $file;
            return $this->read(file_get_contents($file));
        }
        return false;
    }

    /**
     * Creates a new plist
     * @since 1.0.0
     */
    public function create(){
        $this->createMode = true;
        $this->plist = new \DOMDocument('1.0', 'UTF-8');
        $this->plist->formatOutput       = true;
        $this->plist->preserveWhiteSpace = false;
        $this->plist->appendChild((new \DOMImplementation())->createDocumentType("plist", "-//Apple//DTD PLIST 1.0//EN", "http://www.apple.com/DTDs/PropertyList-1.0.dtd"));  // Set document type
    }

    /**
     * Save or override PList file
     *
     * @param null $fileName
     * @param bool $sudo For terminal TODO: need further enhance, ie. su or sudo
     * @return bool
     * @since 1.0.0
     */
    public function save($fileName = null, $sudo = false){
        // save to the file
        $fileName = $fileName == null ? $this->fileName : $fileName; // new file or old file
        if($fileName == null) return false;
        if($sudo){                                                   // save manually
            $tmp_file = "/tmp/PListProperty.plist";
            if($this->plist->save($tmp_file) === false) return false;
            if(system("sudo mv {$tmp_file} {$fileName}", $return) === false) return false;
            if($return !== 0) return false;
        }else{
            if($this->plist->save($fileName) === false) return false;
        }
        return true;
    }

    /**
     * Get root of property list
     *
     * plist > (array | data | date | dict | real | integer | string | true | false )
     *
     * @param string|null $type
     * @param string|null $value
     * @return null|PListProperty
     * @since 1.0.0
     */
    public function root($type = null, $value = null){
        $types = array("array", "data", "date", "dict", "real", "integer", "string", "true", "false");
        if($this->createMode){
            $plist = $this->plist->createElement("plist");
            $plist->setAttribute("version", "1.0");
            $this->plist->appendChild($plist);
            $plist = new PListProperty($this, $plist);
            if(in_array($type, $types)) $plist->addProperty($type, $value);
            else return null;
            /**
             * BUG: In the case of creating a new plist, it can't format output properly
             *      So a workaround is needed, that is to open the newly created plist
             *      in edit mode.
             */
            $plist = new self();
            $plist->read(self::preview());
            $this->plist = $plist->plist;
            return $plist->root();
        }
        $plist = new PListPropertyList($this, $this->plist->childNodes, false, true);
        /** @var PListProperty $rootProperty */
        foreach($plist as $rootProperty){
            if($rootProperty->type() == "plist" && $rootProperty->hasProperties(true)){
                $innerRoot = $rootProperty->innerProperties();
                /** @var PListProperty $property */
                foreach($innerRoot as $property){        // Only single type is allowed in root
                    if(in_array($property->type(), $types)) return $property;
                }
            }
        }
        return null;
    }

    /**
     * Preview the plist file as xml
     *
     * @return string
     * @since 1.0.0
     */
    public function preview(){
        return $this->plist->saveXML();
    }
}

/**
 * Decode a plist file into an associative array or json string
 *
 * This function reads the file as string and calls the
 * \PListEditor\plist_decode() function for the decoding task.
 *
 * @see \PListEditor\plist_decode() - Decodes a plist string
 *
 * @param string $filename Full path to the plist file
 * @param bool   $json     If json is preferred as output
 * @return array|string An associative array or a json string
 */
function plist_decode_file($filename, $json = false){
    return plist_decode(file_get_contents($filename), $json);
}

/**
 * Decodes a plist string into an associative array or json string
 *
 * NOTE: This does not preserve ALL the data types (ie. Data, Date, etc).
 *  Don't use this function if type preservation is important.
 *
 * @see \PListEditor\plist_decode_file() - Decodes a plist file
 * @param string $string The plist (xml) string
 * @param bool   $json   If json is preferred as output
 * @return array|string
 * @since 2.0.0
 */
function plist_decode($string, $json = false){
    $result = [];
    $plist = new PListEditor();
    $plist->read($string);
    $root = $plist->root();
    if($root != null){
        parse($root, $result);
    }
    return $json ? json_encode($result, JSON_PRETTY_PRINT) : $result;
}

/**
 * Parse properties of plist_decode
 *
 * NOTE: This function should not be used anywhere,
 *  visit see also for it's uses.
 *
 * @see \PListEditor\plist_decode() - Decodes a plist string
 * @see \PListEditor\plist_decode_file() - Decodes a plist file
 *
 * @param PListProperty $property
 * @param array         $to
 * @return string
 * @since 2.0.0
 */
function parse($property, &$to){
    $isDict = $property->hasKey();
    switch($property->type()){
        case PListProperty::PL_DICT:
            if(!$isDict){
                $target = &$to;
            }else{
                $key = $property->key();
                $to[$key] = [];
                $target = &$to[$key];
            }
            foreach($property->innerProperties() as $innerProperty)
                parse($innerProperty, $target);
            break;
        case PListProperty::PL_ARRAY:
            if($isDict){
                $key = $property->key();
                $to[$key] = [];
                $target = &$to[$key];
            }else{
                $target = &$to;
            }
            if($property->hasProperties()){
                foreach($property->innerProperties() as $innerProperty){
                    $member = [];
                    $type = parse($innerProperty, $member);
                    if($type == PListProperty::PL_DICT)
                        array_push($target, $member);
                    else
                        array_push($target, $member[0]);
                }
            }
            break;
        case PListProperty::PL_FALSE:
            if($isDict) $to[$property->key()] = false;
            else array_push($to, false);
            break;
        case PListProperty::PL_TRUE:
            if($isDict) $to[$property->key()] = true;
            else array_push($to, true);
            break;
        case PListProperty::PL_INTEGER:
            if($isDict) $to[$property->key()] = (int)$property->value();
            else array_push($to, (int)$property->value());
            break;
        case PListProperty::PL_REAL:
            if($isDict) $to[$property->key()] = (double)$property->value();
            else array_push($to, (double)$property->value());
            break;
        default:
            if($isDict) $to[$property->key()] = $property->value();
            else array_push($to, $property->value());
    }
    return $property->type();
}