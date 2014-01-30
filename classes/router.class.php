<?php
Class Router extends Singleton {
        private $config;
        private $path;
        private $args = array();
        protected function __construct($config) {
                $this->config = $config;
        }
        function setPath($path) {
            $path .= DS;
            if (is_dir($path)) $this->path = $path; 
            else
            {
                throw new Exception ('Invalid controller path: `' . $path . '`');
            }
        }
        function delegate() {
            $this->getController($file, $controller, $action, $args);
            if($this->config['authorized'] === true || ($this->config['authorized'] === false && $controller == 'users' && $action == 'login')) {
                if (is_readable($file)) include_once($file);
                else die ('Error 404 Page Not Found');
                if(!empty($args)) {
                    $this->config->set('args',array_slice($args, 0, AN));
                }
                $this->config->set('cc',$controller,true);
                $class = 'Controller_' . $controller;
                if (class_exists($class)) $controller = new $class($this->config);
                else die ('Error 404 Page Not Found');
                if (is_callable(array($controller, $action))) $controller->$action(); 
                else die ('Error 404 Page Not Found');
            }
        }
        private function getController(&$file, &$controller, &$action, &$args) {
            $route = (empty($_GET['route'])) ? '' : strtolower($_GET['route']);
            if (empty($route)) $route = 'index';
            //$route = trim($route, DS);
            $parts = explode(RS, $route);
            $cmd_path = $this->path;
            foreach ($parts as $part) {
                $fullpath = $cmd_path . $part;
                if (is_dir($fullpath)) {
                        $cmd_path .= $part . DS;
                        array_shift($parts);
                        continue;
                }
                if (is_file($fullpath . '.controller.php')) {
                        $controller = $part;
                        array_shift($parts);
                        break;
                }
            }
            if (empty($controller)) $controller = 'index';
            $action = array_shift($parts);
            if (empty($action)) { $action = 'index'; }
            $file = $cmd_path . $controller . '.controller.php';
            $args = $parts;
       }
}
?>
