<?php

namespace scsstojson\Controller;

class variables{

    public $variables;

    public function __construct(){
        // die($this->variables);
    }

    public function variablesReplace($var){
        // if string
        if(is_string($var) && strpos($var, '$') !== false){
            return $this->findVar(trim($var));
        }
        return $var;
    }

    public function findVar($var){
        // die($var);
        $variables = $this->variables;
        $variables = explode($var.':', $variables);
        if(count($variables) > 1){
            $variables = $variables[1];
            $variables = explode(';', $variables);
            $variables = $variables[0];
            $variables = str_replace('!default', '', $variables);
            $variables = str_replace(array( "\r", "\n"), '', $variables);
            $variables = preg_replace('/\(|\)/', '', $variables);
            $variables = $this->stringValuesToArray($variables, ',');
            // $variables[1] = $this->checkVariable($variables[1]);
        }
        return $variables;
    }

    public function checkVariable($variables){
        if(strpos($variables, '$') !== false){
            $variables = preg_replace_callback('/\$([a-zA-Z0-9_-]+)/', function($matches) {
                return trim($this->findVar($matches[1]));
            }, $variables);
        }
        return $variables;
    }

    public function stringValuesToArray($values, $separator = ' ') {
        if(is_string($values) && strpos($values, $separator) !== false){
            $values = explode($separator, $values);
            // explode values
            $values = array_map(function($value) {
                if(strpos($value, ':') !== false) {
                    $array =  explode(':', $value, 2);
                    return array($array[0] => $array[1]);
                }return $value;
            }, $values);
        }

        return $values;
    }


    public function getVariables($var){
        $varType = $this->getVarType($var);
        switch ($varType) {
            case 'stringArray':
                $var = $this->getStringArray($var);
                break;
            case 'math':
                $var = $this->getMath($var);
                break;
            case 'string':
                $var = $this->getString($var);
                break;
            case 'array':
                $var = $this->getArray($var);
                break;
        }
        return $var;
    }

    public function getVarType($var){
        switch ($var) {
            case is_string($var):
                return 'string';
                break;
            case is_array($var):
                return 'array';
                break;
            case is_string($var) && strpos($var, ':') !== false:
                return 'stringArray';
                break;
            case is_string($var) && preg_match('/[+-/*]/', $var):
                return 'math';
                break;
        }
    }

    public function getStringArray($var){
        $var = explode(':', $var);
        $var = array_map('trim', $var);
        $var = array_combine(array($var[0]), array($var[1]));
        return $var;
    }

    public function getMath($var){
        $var = preg_replace('/\(|\)/', '', $var);
        $var = str_replace(' ', '', $var);
        $var = explode(',', $var);
        $var = array_map(function($value) {
            if(strpos($value, ':') !== false) {
                $array =  explode(':', $value, 2);
                return array($array[0] => $array[1]);
            }return $value;
        }, $var);
        return $var;
    }

    public function getString($var){
        return $var;
    }

    public function getArray($var){
        $var = array_map(function($value) {
            return $this->getVariables($value);
        }, $var);
        return $var;
    }
}