<?php
require_once('controller_base.php');
Class Controller_Logs Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('№','Дата та час','IP','Користувач','Повідомлення');
        }
        function index() {
				$y = (isset($_GET['y']) && is_numeric($_GET['y']) && $_GET['y'] > 2010) ? floor($_GET['y']) : date("Y");
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','etimestamp','ip','name','eventdesc'));
                $this->config['db']->SetTables(array('vlog'));
                $this->config['db']->SetWhere(array("extract(year from etimestamp::timestamp)=" . $y));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
					$this->vars['dy'] = $y;
                    $this->vars['data'] = $data;
                    //count all year periods
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array('distinct extract(year from etimestamp::timestamp) "year"'));
                    $this->config['db']->SetTables(array('vlog'));
                    $this->config['db']->SetOrder(array('1 desc'));
                    $this->config['db']->BuildQuery();
                    $years = $this->config['db']->RunQuery();
                    if(!empty($years)) {
                    $this->vars['years_title'] = 'Рік: ';
                    $this->vars['years'] = $years;
                    }
                    //
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['__sorting'] = array(0 => 'DESC');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }

}


?>
