<?php
Class Logger extends Singleton {
    private $config;
    protected function __construct($config) {
        $this->config = $config;
    }
    public function eLog($array) {
        if(!is_array($array)) $array = array($array);
        //print_r($this->config); die;
        if(empty($array)) return false;
        else {
            $ones['uid'] = (isset($this->config['authorized']) && $this->config['authorized'] === true) ? $_SESSION['uid'] : 0;
            $ones['ip'] = $_SERVER['REMOTE_ADDR'];
            $ones['eventdesc'] = (isset($array[0]) && !empty($array[0])) ? addslashes($array[0]) : NULL;
            if(isset($ones['uid']) && isset($ones['eventdesc'])) {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('i');
                    $this->config['db']->SetFields(array('uid','eventdesc','ip'));
                    $this->config['db']->SetTables(array('log'));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['uid'],$ones['eventdesc'],$ones['ip']);
            }
            else return false;            
        }
    }
}
?>
