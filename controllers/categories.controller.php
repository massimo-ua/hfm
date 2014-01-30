<?php
require_once('controller_base.php');
Class Controller_Categories Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('№','Тип','Назва','Показувати');
        }
        function index() {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','type','name "item"',"'categories/view/'||_id::text \"item_url\"",'visible' ));
                $this->config['db']->SetTables(array('vcategories2'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['__controls'] = array(
                    0 => array('href'=> WR . 'categories/edit','title'=>'Редагувати','icon' => 'pencil32.png','id' => true),
                    1 => array('href'=> WR . 'categories/close','title'=>'Закрити','icon' => 'minus32.png','id' => true),
                    100 => array('href'=> WR . 'categories/create','title'=>'Створити','icon' => 'paperplus32.png','id' => false)
                    );
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['t_fixed'] = 1;
                    $this->vars['__sorting'] = array(3 => 'ASC');
                    $this->vars['__hide'] = array('item_url');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->vars['page_title'] = 'Категорії';
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
                $this->vars['th'] = array('Рік','Валюта','Категорія','Прихід','Розхід');
                $this->vars['__hide'] = array('item_url');
                if(isset($a[2])) $this->config['db']->SetLimit($a[2]);
                $this->config['db']->SetTables(array('categoriesbase'));
                $this->config['db']->SetGroup(array('1','3','4','2'));
                $this->config['db']->SetFields(array('year "item"',"'categories/view/'||category_id::text||'/1/'||year::text \"item_url\"",'category','currency','sum(income) "income"','sum(expence) "expence"'));
                $this->config['db']->SetWhere(array('category_id = ' . addslashes($a[0])));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                break;
                case 1:
                $this->vars['th'] = array('Місяць','Валюта','Категорія','Прихід','Розхід');
                $this->vars['__hide'] = array('year','item_url','month');
                if(isset($a[2])) {
                    $a[2] = (int) $a[2];
                    if($a[2] < 1900 || $a[2] > 3000) $a[2] = date("Y");
                }
                else $a[2] = date("Y");
                $this->config['db']->SetTables(array('categoriesbase'));
                $this->config['db']->SetFields(array("to_char(to_timestamp (month::text, 'MM'), 'TMMonth') item",'year','month',"'categories/view/'||category_id::text||'/2/'||year::text||'/'||month::text \"item_url\"",'category','currency','sum(income) "income"','sum(expence) "expence"'));
                $this->config['db']->SetWhere(array('category_id = ' . addslashes($a[0]) . ' AND','year = ' . addslashes($a[2])));
                $this->config['db']->SetGroup(array('2','3','6','category_id','5'));
                $this->config['db']->SetOrder(array('month asc'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                $this->vars['__sorting'] = array(1 => 'ASC');
                break;
                case 2:
                $this->vars['th'] = array('Дата','Валюта','Категорія','Прихід','Розхід');
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
                $this->config['db']->SetTables(array('categoriesbase'));
                $this->config['db']->SetFields(array('date "item"',"'categories/view/'||category_id::text||'/3/'||date::text \"item_url\"",'category','currency','sum(income) "income"','sum(expence) "expence"'));
                $this->config['db']->SetWhere(array('category_id = ' . addslashes($a[0]) . ' AND','year = ' . addslashes($a[2]) . ' AND','month = ' . addslashes($a[3])));
                $this->config['db']->SetGroup(array('1','4','3','category_id'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                break;
                case 3:
                $this->vars['th'] = array('Дата','Валюта','Категорія','Сума');
                if(date('Y-m-d', strtotime($a[2])) != $a[2]) $a[2] = date('Y-m-d');
                $this->config['db']->SetTables(array('vtransactions2'));
                $this->config['db']->SetFields(array('date "item"','currency',"case when category_id is null and mirror_account_id is not null then 'Трансфер '|| case when type=1 then 'з ' else 'до ' end ||get_account_name(mirror_account_id)::text else get_category_name(category_id) end \"category\"",'amount'));
                $this->config['db']->SetWhere(array('category_id = ' . addslashes($a[0]) . ' AND',"date = '" . addslashes($a[2]) . "'"));
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
                //validate input data and create category
                $ones['type'] = ($_POST['type'] && is_numeric($_POST['type']) && in_array($_POST['type'],array(1,2))) ? $_POST['type'] : NULL;
                $ones['name'] = ($_POST['name']) ? $_POST['name'] : NULL;
                $ones['parent_id'] = ($_POST['parent_id'] && is_numeric($_POST['parent_id']) && $_POST['parent_id'] > 0) ? $_POST['parent_id'] : NULL;
                $ones['visible'] = ($_POST['visible'] == 1) ? true : false;
                if(empty($ones['type']) || empty($ones['name'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('i');
                    $this->config['db']->SetFields(array('type','name','parent_id','visible'));
                    $this->config['db']->SetTables(array('categories'));
                    $this->config['db']->SetWhere(array('close_date is NULL'));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['type'],addslashes($ones['name']),$ones['parent_id'],"{$ones['visible']}");
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
                    $this->vars['page_title'] = 'Категорії > Нова категорія';
                    $this->vars['jq'] = 1;
                    $this->vars['rootcatcallback'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'name', 'error' => 'Будь ласка, вкажіть Назва'),
                    1 => array('field' => 'type', 'error' => 'Будь ласка, вкажіть Тип')
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
                //validate input data and save categories
                $ones['_id'] = ($_POST['_id'] && is_numeric($_POST['_id']) && $_POST['_id'] > 0) ? $_POST['_id'] : NULL;
                $ones['type'] = ($_POST['type'] && is_numeric($_POST['type']) && in_array($_POST['type'],array(1,2))) ? $_POST['type'] : NULL;
                $ones['name'] = ($_POST['name']) ? $_POST['name'] : NULL;
                $ones['parent_id'] = ($_POST['parent_id'] && is_numeric($_POST['parent_id']) && $_POST['parent_id'] > 0) ? $_POST['parent_id'] : NULL;
                $ones['visible'] = ($_POST['visible'] == 1) ? true : false;
                if(empty($ones['_id']) || empty($ones['type']) || empty($ones['name'])) header("Location: " . WR . "/{$this->config['cc']}");
                else {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('u');
                    $this->config['db']->SetTables(array('categories'));
                    $this->config['db']->SetFields(array('type'=>'?','name'=>'?','parent_id'=>'?','visible'=>'?'));
                    $this->config['db']->SetWhere(array("_id = ?"));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($ones['type'],addslashes($ones['name']),$ones['parent_id'],"{$ones['visible']}",$ones['_id']);
                    header("Location: " . WR . "{$this->config['cc']}");
                }
            }
            else
            {
                if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0]))
                {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','name','type','parent_id','visible'));
                $this->config['db']->SetTables(array('categories'));
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
                    $this->vars['page_title'] = 'Рахунки > Редагування категорії №' . $this->config['args'][0];
                    $this->vars['jq'] = 1;
                    $this->vars['calculator'] = 1;
                    $this->vars['validator'] = 1;
                    $this->vars['datepicker'] = 1;
                    $this->vars['validator'] = array(
                    0 => array('field' => 'name', 'error' => 'Будь ласка, вкажіть Назва'),
                    1 => array('field' => 'type', 'error' => 'Будь ласка, вкажіть Тип')
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
                $this->config['db']->SetTables(array('categories'));
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
