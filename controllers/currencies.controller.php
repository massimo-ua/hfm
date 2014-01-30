<?php
require_once('controller_base.php');
Class Controller_Currencies Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('Цифр.Код','Символ','Симв.Код','Курс','Домашня');
        }
        function index() {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','symbol','code','rate','home'));
                $this->config['db']->SetTables(array('vcurrencies2'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['__controls'] = array(
                    0 => array('href'=> WR . 'currencies/edit','title'=>'Редагувати','icon' => 'pencil32.png','id' => true),
                    1 => array('href'=> WR . 'currencies/close','title'=>'Закрити','icon' => 'minus32.png','id' => true),
                    100 => array('href'=> WR . 'currencies/create','title'=>'Створити','icon' => 'paperplus32.png','id' => false)
                    );
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['t_fixed'] = 1;
                    $this->vars['__sorting'] = array(1 => 'ASC');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->vars['page_title'] = 'Валюти';
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function create() {
            if(isset($_POST) && !empty($_POST))
            {
                //validate input data and create category
                $ones['_id'] = ($_POST['_id'] && is_numeric($_POST['_id']) && $_POST['_id'] > 0) ? $_POST['_id'] : NULL;
                $ones['symbol'] = ($_POST['symbol']) ? $_POST['symbol'] : NULL;
                $ones['code'] = ($_POST['code']) ? $_POST['code'] : NULL;
                $ones['home'] = ($_POST['home'] == 1) ? 1 : NULL;
                $ones['rate'] = 0;
                if(empty($ones['_id']) || empty($ones['code'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('i');
                    $this->config['db']->SetFields(array('_id','symbol','code','home','rate'));
                    $this->config['db']->SetTables(array('currencies'));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['_id'],addslashes($ones['symbol']),$ones['code'],$ones['home'],$ones['rate']);
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                $model = $this->config['model']->GetModel($this->config['cc']);
                if(empty($model))
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація про валюти!';
                }
                else
                {
                    $this->vars['form'] = $model;
                    $this->vars['page_title'] = 'Валюти > Нова валюта';
                    $this->vars['jq'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => '_id', 'error' => 'Будь ласка, вкажіть Цифровий Код'),
                    1 => array('field' => 'code', 'error' => 'Будь ласка, вкажіть Символьний Код')
                    );
                    $this->display = 'edit_item.ttpl.html';
                }
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
            }
        }
        function edit() {
            if(isset($_POST) && !empty($_POST))
            {
                //validate input data and save account
                $ones['_id'] = ($_POST['_id'] && is_numeric($_POST['_id']) && $_POST['_id'] > 0) ? $_POST['_id'] : NULL;
                $ones['symbol'] = ($_POST['symbol']) ? $_POST['symbol'] : NULL;
                $ones['code'] = ($_POST['code']) ? $_POST['code'] : NULL;
                $ones['home'] = ($_POST['home'] == 1) ? 1 : NULL;
                if(empty($ones['_id']) || empty($ones['code'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('u');
                    $this->config['db']->SetTables(array('currencies'));
                    $this->config['db']->SetFields(array('_id'=>'?','symbol'=>'?','code'=>'?','home'=>'?'));
                    $this->config['db']->SetWhere(array("_id = ?"));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['_id'],addslashes($ones['symbol']),addslashes($ones['code']),$ones['home'],$ones['_id']);
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','symbol','code','home'));
                $this->config['db']->SetTables(array('currencies'));
                $this->config['db']->SetWhere(array('_id = ' . $this->config['args'][0]));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(empty($data)) $model = $this->config['model']->GetModel($this->config['cc']);
                else $model = $this->config['model']->GetModel($this->config['cc'],$data);
                if(empty($model))
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація про валюти!';
                }
                else
                {
                    $this->vars['form'] = $model;
                    $this->vars['page_title'] = 'Валюти > Редагування валюти ' . $this->config['args'][0];
                    $this->vars['jq'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => '_id', 'error' => 'Будь ласка, вкажіть Цифровий Код'),
                    1 => array('field' => 'code', 'error' => 'Будь ласка, вкажіть Символьний Код')
                    );
                    $this->display = 'edit_item.ttpl.html';
                }
                    /**/
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
                }
            }
        }
        function close () {
            if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType('u');
                $this->config['db']->SetTables(array('currencies'));
                $this->config['db']->SetFields(array('close_date'=>'current_date'));
                $this->config['db']->SetWhere(array("_id = ?"));
                $this->config['db']->BuildQuery();
                $this->config['db']->RunQuery($this->config['args'][0]);
                header("Location: " . WR . "{$this->config['cc']}");
        }
        else header("Location: " . WR . "{$this->config['cc']}");
        }
}


?>
