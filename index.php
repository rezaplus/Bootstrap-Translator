<?php

// error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'src/Controller/CompileController.php';
require_once 'src/Model/ScssToJson.php';

use scsstojson\Model\ScssToJson;

// defined('BASEPATH') OR exit('No direct script access allowed');

// defined('INDEX_PATH') OR define('INDEX_PATH', __DIR__);


$scssToJson = new ScssToJson();
$scssToJson->scss = file_get_contents('src/includes/scss/_utilities.scss');
$scssToJson->variables = file_get_contents('src/includes/scss/_variables.scss');

echo '<pre>'; 
    print_r($scssToJson->run());
echo '</pre>';