<?php
require_once('controller_base.php');
Class Controller_Accounts Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('№','Назва','Валюта','Баланс','Еквівалент');
                $this->vars['details'] = 'Детальніше ...';
        }
        function index() {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','name item',"concat('accounts/view/',_id) item_url",'currency','current_balance','equivalent'));
                $this->config['db']->SetTables(array('vaccounts2'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['__controls'] = array(
                    0 => array('href'=> WR . 'accounts/edit','title'=>'Редагувати','icon' => 'pencil32.png','id' => true),
                    1 => array('href'=> WR . 'accounts/close','title'=>'Закрити','icon' => 'minus32.png','id' => true),
                    2 => array('href'=> WR . 'accounts/recalculate','title'=>'Обчислити баланс','icon' => 'exchange32.png','id' => true),
                    100 => array('href'=> WR . 'accounts/create','title'=>'Створити','icon' => 'paperplus32.png','id' => false)
                    );
                    $this->vars['page_title'] = 'Рахунки';
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['jtable_totals'] = true;
                    $this->vars['t_fixed'] = 1;
                    $this->vars['__sorting'] = array(5 => 'DESC');
                    $this->vars['__bolder'] = array('current_balance');
                    $this->vars['__hide'] = array('item_url');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->vars['page_title'] = 'Рахунки';
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function view() {
                $a = $this->config['args'];
                if (is_numeric($a[0]) && $a[0] >= 1) {
                $level = (isset($a[1]) && is_numeric($a[1]) && $a[1] >= 1) ? $a[1] : 0;
                $id = $this->config['args'];
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                switch($level) {
                case 0:
                $this->vars['th'] = array('Рік','Валюта','Рахунок','Прихід','Розхід','Баланс');
                $this->vars['__hide'] = array('account_id','item_url');
                if(isset($a[2])) $this->config['db']->SetLimit($a[2]);
                $this->config['db']->SetTables(array('accountbase2'));
                $this->config['db']->SetGroup(array('1','2'));
                $this->config['db']->SetFields(array('year item','account_id',"concat('accounts/view/',account_id,'/',1,'/',year) item_url",'get_currency_name(account_id) currency','get_account_name(account_id) account','round(sum(income)/100.00,2) income','round(sum(expence)/100.00,2) expence',"round(GET_ACCOUNT_CURRENT_BALANCE(account_id, TO_DATE(year::text||'-12-31','YYYY-MM-DD'))/100.00,2) balance"));
                $this->config['db']->SetWhere(array('account_id = ' . addslashes($a[0])));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                break;
                case 1:
                $this->vars['th'] = array('Місяць','Валюта','Рахунок','Прихід','Розхід','Баланс');
                $this->vars['__hide'] = array('account_id','year','item_url','month');
                if(isset($a[2])) {
                    $a[2] = (int) $a[2];
                    if($a[2] < 1900 || $a[2] > 3000) $a[2] = date("Y");
                }
                else $a[2] = date("Y");
                $this->config['db']->SetGroup(array('month'));
                $this->config['db']->SetTables(array('accountbase2'));
                $this->config['db']->SetFields(array("to_char(to_timestamp (month::text, 'MM'), 'TMMonth') item",'year','month',"concat('accounts/view/',account_id,'/',2,'/',year,'/',month) item_url",'account_id','get_currency_name(account_id) currency','get_account_name(account_id) account','round(sum(income)/100.00,2) income','round(sum(expence)/100.00,2) expence',"round(GET_ACCOUNT_CURRENT_BALANCE(account_id, (to_date(year::text||'-'||month::text||'-01','YYYY-MM-DD') + INTERVAL '1 MONTH - 1 day')::date)/100.00,2) balance"));
                $this->config['db']->SetWhere(array('account_id = ' . addslashes($a[0]) . ' AND','year = ' . addslashes($a[2])));
                $this->config['db']->SetGroup(array('2','3','5'));
                $this->config['db']->SetOrder(array('month asc'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                $this->vars['__sorting'] = array(1 => 'ASC');
                break;
                case 2:
                $this->vars['th'] = array('Дата','Валюта','Рахунок','Прихід','Розхід','Баланс');
                $this->vars['__hide'] = array('account_id','item_url');
                if(isset($a[3])) {
                    $a[2] = (int) $a[2];
                    if($a[2] < 1900 || $a[2] > 3000) $a[2] = date("Y");
                }
                if(isset($a[3])) {
                    $a[3] = (int) $a[3];
                    if($a[3] < 1 || $a[3] > 12) $a[3] = date("m");
                }
                else $a[3] = date("m");
                $this->config['db']->SetTables(array('accountbase2'));
                $this->config['db']->SetFields(array('date item','account_id',"concat('accounts/view/',account_id,'/',3,'/',date) item_url",'get_currency_name(account_id) currency','get_account_name(account_id) account','round(sum(income)/100.00,2) income','round(sum(expence)/100.00,2) expence',"round(GET_ACCOUNT_CURRENT_BALANCE(account_id, date)/100.00,2) balance"));
                $this->config['db']->SetWhere(array('account_id = ' . addslashes($a[0]) . ' AND','year = ' . addslashes($a[2]) . ' AND','month = ' . addslashes($a[3])));
                $this->config['db']->SetGroup(array('1','2'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                break;
                case 3:
                $this->vars['th'] = array('Дата','Валюта','Рахунок','Сума');
                if(date('Y-m-d', strtotime($a[2])) != $a[2]) $a[2] = date('Y-m-d');
                $this->config['db']->SetTables(array('vtransactions2'));
                $this->config['db']->SetFields(array('date "item"','currency','account','amount'));
                $this->config['db']->SetWhere(array('account_id = ' . addslashes($a[0]) . ' AND',"date = '" . addslashes($a[2]) . "'"));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                break;
                }
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['jq'] = 1;
                    $this->vars['jtable'] = 1;
                    $this->vars['t_fixed'] = 1;
                    if(!isset($this->vars['__sorting'])) $this->vars['__sorting'] = array(0 => 'DESC');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function create() {
            if(isset($_POST) && !empty($_POST))
            {
                //validate input data and create account
                $ones['currency_id'] = ($_POST['currency_id'] && is_numeric($_POST['currency_id']) && strlen($_POST['currency_id'])==3 && $_POST['currency_id'] > 0) ? $_POST['currency_id'] : NULL;
                $ones['name'] = ($_POST['name']) ? $_POST['name'] : NULL;
                $ones['opening_balance'] = ($_POST['opening_balance']) ? preg_replace('/[^0-9]/i', '', number_format(round($_POST['opening_balance'],2),2,'.','')) : 0;
                $ones['current_balance'] = (!empty($ones['opening_balance'])) ? $ones['opening_balance'] : 0;
                if(empty($ones['currency_id']) || empty($ones['name'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('i');
                    $this->config['db']->SetFields(array('currency_id','name','opening_balance','current_balance'));
                    $this->config['db']->SetTables(array('accounts'));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['currency_id'],addslashes($ones['name']),$ones['opening_balance'],$ones['current_balance']);
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
                    $this->vars['page_title'] = 'Рахунки > Новий рахунок';
                    $this->vars['jq'] = 1;
                    $this->vars['calculator'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['datepicker'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'name', 'error' => 'Будь ласка, вкажіть Назва'),
                    1 => array('field' => 'currency_id', 'error' => 'Будь ласка, вкажіть Валюта'),
                    2 => array('field' => 'opening_balance', 'error' => 'Будь ласка, вкажіть Початковий баланс', 'option' => 'number: true')
                    );
                    //$this->vars['template'] = 'basic-table';
                    $this->display = 'edit_item.ttpl.html';
                }
                    /**/
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
            }
        }
        function edit() {
            if(isset($_POST) && !empty($_POST))
            {
                //validate input data and save account
                $ones['_id'] = ($_POST['_id'] && is_numeric($_POST['_id']) && $_POST['_id'] > 0) ? $_POST['_id'] : NULL;
                $ones['currency_id'] = ($_POST['currency_id'] && is_numeric($_POST['currency_id']) && $_POST['currency_id'] > 0) ? $_POST['currency_id'] : NULL;
                $ones['name'] = ($_POST['name']) ? $_POST['name'] : NULL;
                $ones['opening_balance'] = ($_POST['opening_balance']) ? preg_replace('/[^0-9]/i', '', number_format(round($_POST['opening_balance'],2),2,'.','')) : 0;
                if(empty($ones['_id']) || empty($ones['currency_id']) || empty($ones['name'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('u');
                    $this->config['db']->SetTables(array('accounts'));
                    $this->config['db']->SetFields(array('currency_id'=>'?','name'=>'?','opening_balance'=>'?'));
                    $this->config['db']->SetWhere(array("_id = ?"));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['currency_id'],addslashes($ones['name']),$ones['opening_balance'],$ones['_id']);
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','name','currency_id','coalesce(round(opening_balance/100.00,2),0.00) opening_balance'));
                $this->config['db']->SetTables(array('accounts'));
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
                    $this->vars['page_title'] = 'Рахунки > Редагування рахунку №' . $this->config['args'][0];
                    $this->vars['jq'] = 1;
                    $this->vars['calculator'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['datepicker'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'name', 'error' => 'Будь ласка, вкажіть Назва'),
                    1 => array('field' => 'currency_id', 'error' => 'Будь ласка, вкажіть Валюта'),
                    2 => array('field' => 'opening_balance', 'error' => 'Будь ласка, вкажіть Початковий баланс', 'option' => 'number: true')
                    );
                    //$this->vars['template'] = 'basic-table';
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
                $this->config['db']->SetTables(array('accounts'));
                $this->config['db']->SetFields(array('close_date'=>'current_date'));
                $this->config['db']->SetWhere(array("_id = ?"));
                $this->config['db']->BuildQuery();
                $this->config['db']->RunQuery($this->config['args'][0]);
                header("Location: " . WR . "{$this->config['cc']}");
        }
        else header("Location: " . WR . "{$this->config['cc']}");
    }
        function recalculate () {
            if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType('u');
                $this->config['db']->SetTables(array('accounts'));
                $this->config['db']->SetFields(array('current_balance'=>'GET_ACCOUNT_CURRENT_BALANCE(_id,current_date)'));
                $this->config['db']->SetWhere(array("_id = ?"));
                $this->config['db']->BuildQuery();
                $this->config['db']->RunQuery($this->config['args'][0]);
                header("Location: " . WR . "{$this->config['cc']}");
        }
        else header("Location: " . WR . "{$this->config['cc']}");
        }
}


?>
