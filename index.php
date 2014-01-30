<?php
session_start();
error_reporting (E_ALL);
if (version_compare(phpversion(), '5.3.0', '<') == true) { die ('Sorry! But PHP 5.1 and older only!'); }
include_once('include/init.php');
?>
