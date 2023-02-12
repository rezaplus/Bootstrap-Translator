<?php
/**
 * compiler scss to json
 * @param string $scss
 * @return string
 */

 namespace scsstojson\Model;


use scsstojson\Controller\CompileController;

 class ScssToJson{
    public $scss;
    public $variables;

    public function __construct(){
    }

    // Convert scss to json
    public function run(){
        $compile = new CompileController($this->scss, $this->variables);
        $json = $compile->compile();
        return $json;
    }

    public function WriteJson($json){
        $file = fopen("scss.json", "w");
        fwrite($file, $json);
        fclose($file);
    }
    public function getScss(){
        return $this->scss;
    }
 }