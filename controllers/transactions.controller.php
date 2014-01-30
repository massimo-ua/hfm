<?php
require_once('controller_base.php');
Class Controller_Transactions Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('№','Сума','Валюта','Рахунок','Категорія','Дата');
                
        }
        function index() {
				$y = (isset($_GET['y']) && is_numeric($_GET['y']) && $_GET['y'] > 2010) ? floor($_GET['y']) : date("Y");
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','amount','currency','account','category','date'));
                $this->config['db']->SetTables(array('vtransactions2'));
                $this->config['db']->SetWhere(array("extract(year from date::date)=" . $y));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
					$this->vars['dy'] = $y;
                    $this->vars['data'] = $data;
                    //count all year periods
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array('distinct extract(year from date) "year"'));
                    $this->config['db']->SetTables(array('transactions'));
                    $this->config['db']->SetOrder(array('1 desc'));
                    $this->config['db']->BuildQuery();
                    $years = $this->config['db']->RunQuery();
                    if(!empty($years)) {
                    $this->vars['years_title'] = 'Рік: ';
                    $this->vars['years'] = $years;
                    }
                    //
                    $this->vars['__controls'] = array(
                    0 => array('href'=> WR . 'transactions/edit','title'=>'Редагувати','icon' => 'pencil32.png','id' => true),
                    1 => array('href'=> WR . 'transactions/delete','title'=>'Видалити','icon' => 'minus32.png','id' => true),
                    100 => array('href'=> WR . 'transactions/create','title'=>'Створити','icon' => 'paperplus32.png','id' => false)
                    );
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['__sorting'] = array(6 => 'DESC', 1 => 'DESC');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function create() {
            if(isset($_POST) && !empty($_POST))
            {
                //validate input data and create transaction
                $ones['account_id'] = ($_POST['account_id'] && is_numeric($_POST['account_id']) && $_POST['account_id'] > 0) ? $_POST['account_id'] : NULL;
                $ones['amount'] = ($_POST['amount']) ? preg_replace('/[^0-9]/i', '', number_format(round($_POST['amount'],2),2,'.','')) : 0;
                $ones['category_id'] = ($_POST['category_id'] && is_numeric($_POST['category_id']) && $_POST['category_id'] > 0) ? $_POST['category_id'] : 'NULL';
                $ones['notes'] = ($_POST['notes'] && !empty($_POST['notes'])) ? $_POST['notes'] : NULL;
                $ones['mirror_account_id'] = (isset($_POST['mirror_account_id']) && $_POST['mirror_account_id'] && is_numeric($_POST['mirror_account_id']) && $_POST['mirror_account_id'] > 0) ? $_POST['mirror_account_id'] : 'NULL';
                $date = explode('-',$_POST['date']);
                $ones['date'] = (isset($date) && checkdate($date[1],$date[2],$date[0])) ? $_POST['date'] : NULL;
                $ones['type'] = ($_POST['type'] && is_numeric($_POST['type']) && in_array($_POST['type'],array(1,2,3))) ? $_POST['type'] : NULL;
                $ones['status']=1;
                //
                $ones['uid'] = (isset($this->config['authorized']) && $this->config['authorized'] === true) ? $_SESSION['uid'] : 0;
                //
                if(isset($_POST['files']) && !empty($_POST['files'])) {
                    $files = array_filter($_POST['files'],'is_numeric');
                }
                else $files = false;
                //
                if(empty($ones['account_id']) || empty($ones['amount']) || (empty($ones['category_id']) && in_array($ones['type'],array(1,2))) || empty($ones['date']) || empty($ones['type'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                //echo 'OK!'; die;
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array("create_transaction(".$ones['account_id'].",".$ones['amount'].",".$ones['category_id'].",'".$ones['notes']."',NULL,".$ones['mirror_account_id'].",'".$ones['date']."',".$ones['type'].",".$ones['uid'].") i"));
                    $this->config['db']->SetTables(array('dual'));
                    $this->config['db']->BuildQuery();
                    $lii=$this->config['db']->RunQuery();
                    if(isset($lii) && !empty($lii[0]['i'])) {
						foreach(explode(',',$lii[0]['i']) as $i) {
							$this->config['logger']->eLog(__METHOD__ . ' Успішно створено транзакцію ' . $i . ' !');
						}
					}
                    else $this->config['logger']->eLog(__METHOD__ . ' Під час створення транзакції виникла помилка [' . $lii[0]['i'] . '] !');
                    if($files !== false && !empty($lii) && is_numeric($lii[0]['i'])) {
                        $this->config['db']->ResetQuery();
                        $this->config['db']->SetType('u');
                        $this->config['db']->SetTables(array('files'));
                        $this->config['db']->SetFields(array('transaction_id'=>'?'));
                        $this->config['db']->SetWhere(array('_id in (' . implode(',',$files) . ')'));
                        $this->config['db']->BuildQuery();
                        $this->config['db']->RunQuery($lii[0]['i']);
                    }
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                if(isset($this->config['args']) && !empty($this->config['args']))
                {
                    $data = array();
                    if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]) && in_array($this->config['args'][0],array(1,2,3))) $data[0]['type'] = $this->config['args'][0];
                    if(isset($this->config['args'][1]) && is_numeric($this->config['args'][1]) && $this->config['args'][1] > 0) $data[0]['mirror_account_id'] = $this->config['args'][1];
                    if(isset($this->config['args'][2]) && is_numeric($this->config['args'][2]) && $this->config['args'][2] > 0.01) $data[0]['amount'] = $this->config['args'][2];
                } 
                if(empty($data)) $model = $this->config['model']->GetModel($this->config['cc']);
                else 
                {
                    $model = $this->config['model']->GetModel($this->config['cc'] . '2',$data);
                }
                if(empty($model))
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація про валюти!';
                }
                else
                {
                    $this->vars['form'] = $model;
                    $this->vars['page_title'] = 'Транзакції > Нова транзакція';
                    $this->vars['jq'] = 1;
                    $this->vars['calculator'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['datepicker'] = 1;
                    $this->vars['catcallback'] = 1;
                    $this->vars['fileupload'] = 1;
                    //$this->view->assign("item",1);
                    $this->vars['validator'] = array(
                    0 => array('field' => 'account_id', 'error' => 'Будь ласка, вкажіть Рахунок А'),
                    1 => array('field' => 'mirror_account_id', 'error' => 'Будь ласка, вкажіть Рахунок Б'),
                    2 => array('field' => 'category_id', 'error' => 'Будь ласка, вкажіть Категорія'),
                    3 => array('field' => 'amount', 'error' => 'Будь ласка, вкажіть Сума', 'option' => 'min: 0.01'),
                    4 => array('field' => 'type', 'error' => 'Будь ласка, вкажіть Тип'),
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
                //validate input data and update transaction
                $ones['_id'] = (isset($_POST['_id']) && is_numeric($_POST['_id']) && $_POST['_id'] > 0) ? $_POST['_id'] : NULL;
                $ones['account_id'] = (isset($_POST['account_id']) && is_numeric($_POST['account_id']) && $_POST['account_id'] > 0) ? $_POST['account_id'] : NULL;
                $ones['amount'] = (isset($_POST['amount'])) ? preg_replace('/[^0-9]/i', '', number_format(round($_POST['amount'],2),2,'.','')) : 0;
                $ones['category_id'] = (isset($_POST['category_id']) && is_numeric($_POST['category_id']) && $_POST['category_id'] > 0) ? $_POST['category_id'] : NULL;
                $ones['notes'] = (isset($_POST['notes']) && !empty($_POST['notes'])) ? $_POST['notes'] : NULL;
                $ones['mirror_id'] = (isset($_POST['mirror_id']) && is_numeric($_POST['mirror_id']) && $_POST['mirror_id'] > 0) ? $_POST['mirror_id'] : NULL;
                $ones['mirror_account_id'] = (isset($_POST['mirror_account_id']) && is_numeric($_POST['mirror_account_id']) && $_POST['mirror_account_id'] > 0) ? $_POST['mirror_account_id'] : NULL;
                $date = explode('-',$_POST['date']);
                $ones['date'] = (isset($date) && checkdate($date[1],$date[2],$date[0])) ? $_POST['date'] : NULL;
                $ones['type'] = (isset($_POST['type']) && is_numeric($_POST['type']) && in_array($_POST['type'],array(1,2))) ? $_POST['type'] : NULL;
                if(isset($_POST['files']) && !empty($_POST['files'])) {
                    $files = array_filter($_POST['files'],'is_numeric');
                }
                else $files = false;
                //
                if(empty($ones['_id']) || empty($ones['account_id']) || empty($ones['amount']) || empty($ones['category_id']) || empty($ones['date']) || empty($ones['type'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('u');
                    $this->config['db']->SetTables(array('transactions'));
                    $this->config['db']->SetFields(array('account_id'=>'?','amount'=>'?','category_id'=>'?','notes'=>'?','mirror_id'=>'?','mirror_account_id'=>'?','date'=>'?','type'=>'?'));
                    $this->config['db']->SetWhere(array("_id = ?"));
                    $this->config['db']->BuildQuery();
                    $r=$this->config['db']->RunQuery($ones['account_id'],$ones['amount'],$ones['category_id'],$ones['notes'],$ones['mirror_id'],$ones['mirror_account_id'],$ones['date'],$ones['type'],$ones['_id']);
                    if(isset($r)) $this->config['logger']->eLog(__METHOD__ . ' Транзакцію успішно відредаговано ' . $ones['_id'] . ' !');
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','type','account_id','mirror_account_id','category_id','coalesce(round(amount/100.00,2),0.00) amount','date','notes'));
                $this->config['db']->SetTables(array('transactions'));
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
                    $this->vars['page_title'] = 'Транзакції > Редагування транзакції №' . $this->config['args'][0];
                    $this->vars['jq'] = 1;
                    $this->vars['calculator'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['datepicker'] = 1;
                    $this->vars['catcallback'] = 1;
                    $this->vars['fileupload'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'account_id', 'error' => 'Будь ласка, вкажіть Рахунок А'),
                    1 => array('field' => 'mirror_account_id', 'error' => 'Будь ласка, вкажіть Рахунок Б'),
                    2 => array('field' => 'category_id', 'error' => 'Будь ласка, вкажіть Категорія'),
                    3 => array('field' => 'amount', 'error' => 'Будь ласка, вкажіть Сума', 'option' => 'min: 0.01'),
                    4 => array('field' => 'type', 'error' => 'Будь ласка, вкажіть Тип'),
                    );
                    $this->display = 'edit_item.ttpl.html';
                }
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
                }
            }
        }
        function delete() {
                if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('1'));
                $this->config['db']->SetTables(array('transactions'));
                $this->config['db']->SetWhere(array('_id = ' . $this->config['args'][0]));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(empty($data)) 
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Транзакція відсутня!';
				}
                else
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array("delete_transaction(".$this->config['args'][0].") i"));                
                $this->config['db']->SetTables(array('dual'));
                $this->config['db']->BuildQuery();
                $lii = $this->config['db']->RunQuery();
                if(isset($lii) && !empty($lii[0]['i'])) {
						foreach(explode(',',$lii[0]['i']) as $i) {
							$this->config['logger']->eLog(__METHOD__ . ' Успішно видалено транзакції: ' . $i . '!');
						}
				}
                else $this->config['logger']->eLog(__METHOD__ . ' Жодної транзакції не видалено!');
                header("Location: " . WR . "{$this->config['cc']}");
                }
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
                }
                else header("Location: " . WR . "{$this->config['cc']}");
        }
}


?>
