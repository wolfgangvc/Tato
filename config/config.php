<?php

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
$environment = array_merge($_ENV, $_SERVER);
ksort($environment);
// Database Settings
if (isset($environment['MYSQL_PORT'])) {
    $host = parse_url($environment['MYSQL_PORT']);
    $dbConfig = array(
        'db_type'     => 'Mysql',
        'db_hostname' => $host['host'],
        'db_port'     => $host['port'],
        'db_username' => $environment['MYSQL_ENV_MYSQL_USER'],
        'db_password' => $environment['MYSQL_ENV_MYSQL_PASSWORD'],
        'db_database' => $environment['MYSQL_ENV_MYSQL_DATABASE'],
    );
} else {
    die("no config");
}
date_default_timezone_set("Europe/London");

$database = new \Thru\ActiveRecord\DatabaseLayer($dbConfig);