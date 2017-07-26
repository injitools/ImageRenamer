<?php
$config = include 'config.php';

spl_autoload_register(function ($class_name) {
    global $config;
    if (isset($config['classMap'][$class_name]) && file_exists($config['classMap'][$class_name])) {
        include_once $config['classMap'][$class_name];
    }
});

$utilityName = $config['defaultUtility'];

if (empty($config['utilities'][$utilityName])) {
    exit('Utility not found');
}
include_once $config['utilities'][$utilityName];
$utility = new $utilityName();
$utility->run();