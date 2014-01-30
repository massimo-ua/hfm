<?php
require_once('controller_base.php');
Class Controller_Payments Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('№','Періодичність','Категорія','Тип','Статус','Стан');
        }
        function index() {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','periodicity',"category",'type','state','status'));
                $this->config['db']->SetTables(array('vpayments'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['__controls'] = array(
                    0 => array('href'=> WR . 'payments/edit','title'=>'Редагувати','icon' => 'pencil32.png','id' => true),
                    1 => array('href'=> WR . 'payments/close','title'=>'Закрити','icon' => 'minus32.png','id' => true),
                    2 => array('href'=> WR . 'payments/pay','title'=>'Сплатити','icon' => 'risegraph32.png','id' => true),
                    100 => array('href'=> WR . 'payments/create','title'=>'Створити','icon' => 'paperplus32.png','id' => false)
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
        /*function view() {
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
        }*/
        function create() {
            if(isset($_POST) && !empty($_POST))
            {
                //validate input data and create plan
                $ones['periodicity'] = (isset($_POST['periodicity']) && in_array($_POST['periodicity'],array(1,2,3,4,5))) ? $_POST['periodicity'] : NULL;
                $ones['type'] = (isset($_POST['type']) && in_array($_POST['type'],array(1,2))) ? $_POST['type'] : NULL;
                $ones['category_id'] = ($_POST['category_id'] && is_numeric($_POST['category_id']) && $_POST['category_id'] > 0) ? $_POST['category_id'] : NULL;
                $ones['state'] = (isset($_POST['state']) && in_array($_POST['state'],array(1,2))) ? $_POST['state'] : NULL;
                if(empty($ones['periodicity']) || empty($ones['type']) || empty($ones['category_id']) || empty($ones['state'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('i');
                    $this->config['db']->SetFields(array('periodicity', 'category_id', 'type', 'state'));
                    $this->config['db']->SetTables(array('regular_payments'));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['periodicity'],$ones['category_id'],$ones['type'],$ones['state']);
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                $model = $this->config['model']->GetModel($this->config['cc']);
                if(empty($model))
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація щодо платежів!';
                }
                else
                {
                    $this->vars['form'] = $model;
                    $this->vars['page_title'] = 'Платежі > Новий платіж';
                    $this->vars['jq'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['catcallback'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'periodicity', 'error' => 'Будь ласка, вкажіть Періодичність'),
                    1 => array('field' => 'type', 'error' => 'Будь ласка, вкажіть Тип'),
                    2 => array('field' => 'category_id', 'error' => 'Будь ласка, вкажіть Категорія'),
                    3 => array('field' => 'state', 'error' => 'Будь ласка, вкажіть Статус')
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
