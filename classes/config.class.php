<?php
Class Config extends Singleton Implements ArrayAccess {
    private $vars=array();
    public function set($key,$var,$overwrite=false) {
        if (isset($this->vars[$key])) {
                if($overwrite == true) $this->vars[$key] = $var;
                else {
                    throw new Exception('Unable to set variable "' . $key . '". Variable is Already set and OVERWRITE is not allowed.');
                    return false;
                }
        }
        else {
            $this->vars[$key] = $var;
            return true;
        }
    }
    public function get($key) {
            if (isset($this->vars[$key])) return $this->vars[$key];
            else return false;
    }
    public function remove($key) {
        if(isset($this->vars[$key])) {
            unset($this->vars[$key]);
            return true;
        }
        else {
            throw new Exception('Unable to remove variable "' . $key . '". Variable is not set.');
            return false;
        }
    }
    function offsetExists($offset) {
        return isset($this->vars[$offset]);
    }

    function offsetGet($offset) {
        return $this->get($offset);
    }
    
    function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }
    
    function offsetUnset($offset) {
        $this->remove($offset);
    }
    public function ListControllers() {
        $files = glob(SP . 'controllers' . DS .  '*.controller.php');
        $ucl = explode(',',UCONTROLLERS);
        $c = array();
        if(file_exists(SP . 'languages' . DS . 'controllers.ini')) {
            $lang = parse_ini_file(SP . 'languages' . DS . 'controllers.ini');
        }
        foreach($files as $f) {
            $a = strtolower(basename($f));
            $a = explode('.',$a);
            $key = array_search($a[0],$ucl,true);
            if($key !== false) {
                if(isset($lang) && !empty($lang)) {
                    if(array_key_exists($a[0],$lang)) $a[1] = $lang[$a[0]];
                }
                $c["{$key}"]['text'] = "{$a[1]}";
                $c["{$key}"]['value'] = "{$a[0]}";
            }
        }
        if(empty($c)) return false;
        else {
            ksort($c);
            return $c;
            
            }
    }
}
?>
