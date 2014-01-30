<?php
require_once('definitions.php');
function __autoload($class_name) {
        $file = SP . 'classes' . DS . strtolower($class_name) . '.class.php';
        if (!file_exists($file)) {
            return false;
        } else {
            include_once ($file);
        }
}
$config = Config::getInstance();
$config->set('logger',Logger::getInstance($config),true);
$config->set('auth',Auth::getInstance($config),true);
$config->set('db',DML::getInstance(),true);
$config->get('auth')->CheckLogin();
$config->set ('router', Router::getInstance($config),true);
$config->get('router')->setPath (SP . 'controllers');
$config->set ('model', Model::getInstance($config),true);
$config->set ('helper', Helper::getInstance($config),true);
$config->get('router')->delegate();
$config->set('view', Template::getInstance($config),true);
$config->get('view')->Show();
?>
