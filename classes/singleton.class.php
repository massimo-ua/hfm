<?php
Abstract class Singleton {
    private function __construct() {}
    private function __clone() {}
    final public static function getInstance($config=false) {
        static $instance;
        $instance or $instance = new static($config);
        return $instance;
    }
}
?>
