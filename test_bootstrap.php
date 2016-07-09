<?php
require_once("vendor/autoload.php");


$testDatabases['mysql'] = array(
    'db_type' => 'Mysql',
    'db_hostname' => 'localhost',
    'db_port' => '3306',
    'db_username' => 'travis',
    'db_password' => 'travis',
    'db_database' => 'tato',
);


// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
$environment = array_merge($_ENV, $_SERVER);
ksort($environment);
// Database Settings
$dbConfig = array(
    'db_type' => 'Mysql',
       'db_hostname' => 'localhost',
       'db_port' => '3306',
       'db_username' => 'travis',
       'db_password' => 'travis',
       'db_database' => 'tato',
);
date_default_timezone_set("Europe/London");

$database = new \Thru\ActiveRecord\DatabaseLayer($dbConfig);
