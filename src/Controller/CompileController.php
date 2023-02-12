<?php
/**
 * compiler scss to json
 * @param string $scss
 * @return string
 */

namespace  scsstojson\Controller;

use  scsstojson\Controller\variables;

class CompileController{

    public $scssToJson;
    public $scss;
    public $variables = 's';
    public $variablesClass;

    public function __construct($scss, $variables){
        $this->scss = $scss;
        $this->variables = $variables;
        // get variables
        $this->variablesClass = $this->getVariables();
    }

    public function compile(){
        $scss = $this->scss;
        // get scss properties
        $scss = $this->getProperties($scss);
        // convert to json
        $scss = $this->convertToJSON($scss);
        return ($scss);
    }

    public function getVariables(){
        require_once 'variables.php';
        $variablesClass = new variables();
        $variablesClass->variables = $this->variables;
        return $variablesClass;
    }

    public function getProperties($scss){
        // get properties
        $properties = preg_split('/(?<=")[^"]+(?=": \()/m', $scss, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        // remove first element
        array_shift($properties);
        // remove last element
        array_pop($properties);
        // remove comments //
        $properties = preg_replace('/\/\/.*/', '', $properties);
        // remove \n
        $properties = preg_replace('/\n/', '', $properties);
        // remove empty elements
        $properties = array_filter($properties);
        // remove space
        $properties = str_replace('  ', '', $properties);
        // remove "": 
        $properties = str_replace('": ', '', $properties);
        // remove ,""
        $properties = str_replace(',"', '', $properties);
        // remove only first one (
        $properties = preg_replace('/\(/', '', $properties, 1);
        // remove only last one )
        $properties = preg_replace('/\)/', '', $properties, 1);
        // replace array("\r", "\n") with ''
        $properties = str_replace(array( "\r ", "\n "), '', $properties);
        // replace ,/\r|\n/} with '}
        $properties = preg_replace('/,(\r|\n)/', '}', $properties);
        // replace ,} with }
        $properties = str_replace(',)', ')', $properties);
        // return array
        return $this->convertToArray($properties);
    }

    public function convertToJSON($properties){
        // replace () with {}
        $properties = str_replace('(', '{', $properties);
        $properties = str_replace(')', '}', $properties);

        return ($properties);
    }


    public function convertToArray($properties){
        $properties = array_map(function($value) {
            return $this->stringToArray($value);
        }, $properties);
        $properties = $this->propertytoKey($properties);
        return $properties;
    }

    public function propertytoKey($properties){
        // move 'property' value to properties[0] key
        foreach($properties as $key => $value){
            // trim value property
            $value['property'] = trim($value['property']);
            // change key to property
            $properties[$value['property']] = $value;
            // remove property key
            unset($properties[$key]['property']);
            // remove property key
            unset($properties[$key]);
            // replace values to array
            $properties[$value['property']]['values'] = $this->replacevariables($properties[$value['property']]['values']);
            $properties[$value['property']]['values'] = $this->stringValuesToArray($properties[$value['property']]['values'], ' ' );
        }
        return $properties;
    }
    
    public function stringToArray($string) {
        // explode by , outside of parenthesis
        $string = preg_split('/,(?![^()]*\))/', $string);
        
        $string = array_map(function($value) {
            if(strpos($value, ':') !== false) {
                $array =  explode(':', $value, 2);
                if(strpos($array[1], '(') !== false) {
                    // remove first ( and )
                    $array[1] = preg_replace('/\(|\)/', '', $array[1]);
                    // remove space
                    $array[1] = str_replace(' ', '', $array[1]);
                    // explode by ,
                    $array[1] = explode(',', $array[1]);
                    $array[1] = array_map(function($value) {
                        if(strpos($value, ':') !== false) {
                            $array =  explode(':', $value, 2);
                            return array($array[0] => $array[1]);
                        }return $value;
                    }, $array[1]);
                }
                return array($array[0] => $array[1]);
            }return $value;
        }, $string);

        // remove empty elements
        $array = array_filter($string);
        
        return $this->ArrayKeyCorrection($array);
    }

    public function ArrayKeyCorrection($array){
        foreach($array as $ar){
            if(is_array($ar)){
                foreach($ar as $key => $value){
                    // set by key
                    $array[$key] = $value;
                    // unset by index
                    unset($array[array_search($ar, $array)]);
                    // if value is array
                    if(is_array($value)){
                        $array[$key] = $this->ArrayKeyCorrection($value);
                    }
                }
            }
        }
        return $array;
    }

    public function replacevariables($values){
        // replaceVariables by variables class
        return $this->variablesClass->variablesReplace($values);
    }

    public function stringValuesToArray($values, $separator = ' ') {
        if(is_string($values) && strpos($values, ' ') !== false) {
            // explode by ' '
            $values = explode($separator, $values);
            // remove empty elements
            $values = array_filter($values);
            // array combine
            $values = array_combine($values, $values);
        }

        return $values;
    }

}
