<?php
require_once('controller_base.php');
Class Controller_Plans Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('№','Назва','Початок','Закінчення','Валюта','Рахунок','Планова сума','Фактична сума','Різниця');
        }
        function index() {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','name item',"concat('plans/view/',_id) item_url",'sdate','edate','currency','account','coalesce(planamount,0.00) planamount','coalesce(realamount,0.00) realamount','coalesce(realamount-planamount,0.00) difference'));
                $this->config['db']->SetTables(array('vplans'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['__controls'] = array(
                    0 => array('href'=> WR . 'plans/edit','title'=>'Редагувати','icon' => 'pencil32.png','id' => true),
                    1 => array('href'=> WR . 'plans/close','title'=>'Закрити','icon' => 'minus32.png','id' => true),
                    2 => array('href'=> WR . 'plans/recalculate','title'=>'Перерахувати','icon' => 'exchange32.png','id' => true),
                    3 => array('href'=> WR . 'plans/fill','title'=>'Поповнити','icon' => 'risegraph32.png','id' => true),
                    100 => array('href'=> WR . 'plans/create','title'=>'Створити','icon' => 'paperplus32.png','id' => false)
                    );
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['__sorting'] = array(4 => 'ASC');
                    $this->vars['__hide'] = array('item_url');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function view() {
            if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
            {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','name','sdate',"case when edate is null then (sdate + name::integer * interval '1 month')::date else edate end edate", 'account','coalesce(amount,0.00) amount','comment'));
                $this->config['db']->SetTables(array('plandetails2'));
                $this->config['db']->SetWhere(array("_id = {$this->config['args'][0]}"));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['th'] = array('№','Назва|Етап','Початок','Закінчення','Рахунок','Сума','Коментар');
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['__sorting'] = array(0 => 'ASC');
                }
                else
                {
                    $this->vars['__display404'] = true;
                    $this->vars['__404_text'] = 'Відсутня інформація щодо плану!';
                }
            }
            else
            {
                    $this->vars['__display404'] = true;
                    $this->vars['__404_text'] = 'Відсутня інформація щодо плану!';
            }
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function create() {
            if(isset($_POST) && !empty($_POST))
            {
                //validate input data and create plan
                $ones['name'] = ($_POST['name']) ? $_POST['name'] : NULL;
                $ones['currency_id'] = ($_POST['currency_id'] && is_numeric($_POST['currency_id']) && $_POST['currency_id'] > 0) ? $_POST['currency_id'] : NULL;
                $sdate = explode('-',$_POST['sdate']);
                $ones['sdate'] = (isset($sdate) && checkdate($sdate[1],$sdate[2],$sdate[0])) ? $_POST['sdate'] : date('Y-m-d');
                $edate = explode('-',$_POST['edate']);
                $ones['edate'] = (isset($edate) && checkdate($edate[1],$edate[2],$edate[0])) ? $_POST['edate'] : NULL;
                $ones['amount'] = ($_POST['amount']) ? preg_replace('/[^0-9]/i', '', number_format(round($_POST['amount'],2),2,'.','')) : 0;
                $ones['comment'] = ($_POST['comment'] && !empty($_POST['comment'])) ?  addslashes($_POST['comment']) : NULL;
                if(empty($ones['name']) || empty($ones['currency_id']) || empty($ones['edate']) || $ones['amount'] <= 0) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array("create_plan('".addslashes($ones['name'])."','".$ones['sdate']."','".$ones['edate']."',".$ones['currency_id'].",".$ones['amount'].",'".addslashes($ones['comment'])."')"));
                    $this->config['db']->SetTables(array('dual'));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery();
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                $model = $this->config['model']->GetModel($this->config['cc']);
                if(empty($model))
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація щодо планування!';
                }
                else
                {
                    $this->vars['form'] = $model;
                    $this->vars['page_title'] = 'Планування > Новий план';
                    $this->vars['jq'] = 1;
                    $this->vars['calculator'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['datepicker'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'name', 'error' => 'Будь ласка, вкажіть Назва'),
                    1 => array('field' => 'currency_id', 'error' => 'Будь ласка, вкажіть Валюта'),
                    2 => array('field' => 'edate', 'error' => 'Будь ласка, вкажіть Початок (дата)'),
                    3 => array('field' => 'sdate', 'error' => 'Будь ласка, вкажіть Закінчення (дата)'),
                    4 => array('field' => 'amount', 'error' => 'Будь ласка, вкажіть Сума', 'option' => 'min: 0.01')
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
                //validate input data and save plan
                $ones['_id'] = ($_POST['_id'] && is_numeric($_POST['_id']) && $_POST['_id'] > 0) ? $_POST['_id'] : NULL;
                $ones['name'] = ($_POST['name']) ? $_POST['name'] : NULL;
                $edate = explode('-',$_POST['edate']);
                $ones['edate'] = (isset($edate) && checkdate($edate[1],$edate[2],$edate[0])) ? $_POST['edate'] : NULL;
                $ones['amount'] = ($_POST['amount']) ? preg_replace('/[^0-9]/i', '', number_format(round($_POST['amount'],2),2,'.','')) : 0;
                $ones['comment'] = ($_POST['comment'] && !empty($_POST['comment'])) ?  addslashes($_POST['comment']) : NULL;
                if(empty($ones['_id']) || empty($ones['name']) || empty($ones['edate']) || $ones['amount'] <= 0) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('u');
                    $this->config['db']->SetTables(array('plans'));
                    $this->config['db']->SetFields(array('name'=>'?','edate'=>'?','amount'=>'?','comment'=>'?'));
                    $this->config['db']->SetWhere(array("_id = ?"));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['name'],$ones['edate'],$ones['amount'],$ones['comment'],$ones['_id']);
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','name','edate','round(amount/100.00,2) amount','comment'));
                $this->config['db']->SetTables(array('plans'));
                $this->config['db']->SetWhere(array('_id = ' . $this->config['args'][0]));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(empty($data)) $model = $this->config['model']->GetModel('editplan');
                else $model = $this->config['model']->GetModel('editplan',$data);
                if(empty($model))
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація про план!';
                }
                else
                {
                    $this->vars['form'] = $model;
                    $this->vars['page_title'] = 'Планування > Редагування плану №' . $this->config['args'][0];
                    $this->vars['jq'] = 1;
                    $this->vars['calculator'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['datepicker'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'name', 'error' => 'Будь ласка, вкажіть Назва'),
                    1 => array('field' => 'edate', 'error' => 'Будь ласка, вкажіть Початок (дата)'),
                    2 => array('field' => 'amount', 'error' => 'Будь ласка, вкажіть Сума', 'option' => 'min: 0.01')
                    );
                    $this->display = 'edit_item.ttpl.html';
                }
                    /**/
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
                }
            }
        }
        function recalculate () {
            if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetTables(array("dual"));
                $this->config['db']->SetFields(array("simple_recalculate_plan_schedule(".$this->config['args'][0].")"));
                $this->config['db']->BuildQuery();
                $this->config['db']->RunQuery();
                header("Location: " . WR . "{$this->config['cc']}");
        }
        else header("Location: " . WR . "{$this->config['cc']}");
        }
        function close () {
            if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $ones['_id'] = ($this->config['args'][0] > 0) ? $this->config['args'][0] : NULL;
                $ones['cdate'] = date('Y-m-d');
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType('u');
                $this->config['db']->SetTables(array('plans'));
                $this->config['db']->SetFields(array('cdate' => '?'));
                $this->config['db']->SetWhere(array("_id = ?"));
                $this->config['db']->BuildQuery();
                $this->config['db']->RunQuery('"'.$ones['cdate'].'"',$ones['_id']);
                header("Location: " . WR . "{$this->config['cc']}");
        }
        else header("Location: " . WR . "{$this->config['cc']}");
        }
        function fill () {
            
            if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('account_id','coalesce(realamount-planamount,0.00) amount'));
                $this->config['db']->SetTables(array('vplans'));
                $this->config['db']->SetWhere(array('_id = ' . $this->config['args'][0]));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(isset($data[0]['amount']) && isset($data[0]['account_id']) && is_numeric($data[0]['account_id']) && $data[0]['account_id'] > 0 && is_numeric($data[0]['amount']) && $data[0]['amount'] < -0.01) {
                    header("Location: " . WR . "{$this->config['cc']}");
                }
                header("Location: " . WR . "transactions/create/3/" . urlencode($data[0]['account_id']) . "/" . urlencode(abs($data[0]['amount'])) . "/");
        }
        else header("Location: " . WR . "{$this->config['cc']}");
        }
}


?>
