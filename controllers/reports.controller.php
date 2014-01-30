<?php
require_once('controller_base.php');
Class Controller_Reports Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
        }
        function index() {
                $model = $this->config['model']->GetModel($this->config['cc']);
                if(empty($model))
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація про звіти!';
                }
                else
                {
                    $this->vars['form'] = $model;
                    $this->vars['page_title'] = 'Звіти > Параметри побудови';
                    $this->display = 'reports.ttpl.html';
                }
                    /**/
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function show() {
                //validate input variables
                $id = (isset($_GET['id']) && is_numeric($_GET['id']) && in_array($_GET['id'],array(1,2,3))) ? $_GET['id'] : 1;
                $year = (isset($_GET['year']) && preg_match("/^20[0-9]{2}$/",$_GET['year'])) ? $_GET['year'] : date("Y");
                $currency = (isset($_GET['currency']) && strlen($_GET['currency'])==3 && preg_match("/^[A-Z]{3}$/",$_GET['currency'])) ? $_GET['currency'] : NULL;
                $si=(isset($_GET['si']) && $_GET['si'] == 1) ? true : false;
                $type= (isset($_GET['type']) && is_numeric($_GET['type']) && in_array($_GET['type'],array(1,2))) ? $_GET['type'] : 2;
                //initial where setup
                $where = ($si === false) ? array('visible = 1') : array();
                $this->vars['chart']=$id;
                switch($id) {
                default:
                $data = $array();
                break;
                case 1:
                if(!empty($where)) array_push($where," and ");  
                array_push($where," currency = '" . $currency . "'", ' and year = ' . $year );
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetTables(array('report1'));
                $this->config['db']->SetFields(array('month','round(sum(income)/100.00,2) inc','round(sum(expence)/100.00,2) exp'));
                $this->config['db']->SetWhere($where);
                $this->config['db']->SetGroup(array('month'));
                $this->config['db']->SetOrder(array('month'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(empty($data)) 
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація для побудови звіту!';
                }
                else
                {
                    $this->vars['data'] = json_encode($data);
                    $this->vars['page_title'] = 'Річний звіт про прихід/розхід в розрізі місяців та валют';
                    $this->vars['labels']=array($currency,'Прихід','Розхід');
                    $this->vars['report_area_style']='width:1000px;height:500px';
                    $this->vars['jq']=1;
                    $this->vars['amcharts']=1;
                //print_r($data); exit(0);
                }
                $this->display = 'report1.ttpl.html';
                break;
                case 2:
                if(!empty($where)) array_push($where," and ");
                array_push($where," currency = '" . $currency . "'", ' and year = ' . $year . ' and type = ' . $type );
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetTables(array('report2'));
                $this->config['db']->SetFields(array('GET_CATEGORY_NAME(category) "name"',"round(SUM(equivalent)/(SELECT SUM(equivalent) FROM report2 WHERE year = {$year} and currency = '{$currency}' and type = {$type})*100.00,2) \"value\"", "round(SUM(coalesce(equivalent,0))/100.00,2) sum"));
                $this->config['db']->SetWhere($where);
                $this->config['db']->SetGroup(array('category'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(empty($data)) 
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація для побудови звіту!';
                }
                else
                {
                    //print_r($data); exit(0);
                    $this->vars['data'] = $data;
                    $this->vars['page_title'] = 'Річний звіт про масову частку категорій від загальної суми';
                    $this->vars['report_area_style']='width:1200px;height:700px';
                    $this->vars['jq']=1;
                    $this->vars['amcharts']=1;
                //print_r($data); exit(0);
                }
                $this->display = 'report1.ttpl.html';
                break;
                case 3:
                if(!empty($where)) array_push($where," and ");
                array_push($where," currency = '" . $currency . "'", ' and year = ' . $year . ' and type = ' . $type );
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetTables(array('report2'));
                $this->config['db']->SetFields(array('GET_CATEGORY_NAME(category) "name"',"round(SUM(equivalent)/(SELECT SUM(equivalent) FROM report2 WHERE year = {$year} and currency = '{$currency}' and type = {$type})*100.00,2) \"value\""));
                $this->config['db']->SetWhere($where);
                $this->config['db']->SetGroup(array('category'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(empty($data)) 
                {
                        $this->vars['__display404'] = true;
                        $this->vars['__404_text'] = 'Відсутня інформація для побудови звіту!';
                }
                else
                {
                    //print_r($data); exit(0);
                    $this->vars['data'] = $data;
                    $this->vars['page_title'] = 'Звіт про розмір річного доходу в розрізі валют';
                    $this->vars['report_area_style']='width:1200px;height:700px';
                    $this->vars['jq']=1;
                    $this->vars['amcharts']=1;
                //print_r($data); exit(0);
                }
                $this->display = 'report1.ttpl.html';
                break;
                }
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
                /*case 2:
                
                $this->vars['th'] = array('Місяць','Валюта','Рахунок','Прихід','Розхід','Баланс');
                $this->vars['__hide'] = array('account_id','year');
                if(isset($a[2])) {
                    $a[2] = (int) $a[2];
                    if($a[2] < 1900 || $a[2] > 3000) $a[2] = date("Y");
                }
                else $a[2] = date("Y");
                $this->config['db']->SetGroup(array('month'));
                $this->config['db']->SetTables(array('accountbase2'));
                $this->config['db']->SetFields(array('month date','year','account_id','get_currency_name(account_id) currency','get_account_name(account_id) account','round(sum(income)/100,2) income','round(sum(expence)/100,2) expence',"round(GET_ACCOUNT_CURRENT_BALANCE(account_id, LAST_DAY(STR_TO_DATE(CONCAT(year,'-',month,'-1'),'%Y-%m-%d')))/100,2) balance"));
                $this->config['db']->SetWhere(array('account_id = ' . addslashes($a[0]) . ' AND','year = ' . addslashes($a[2])));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                break;
                case 3:
                /*
                $this->vars['th'] = array('Дата','Валюта','Рахунок','Прихід','Розхід','Баланс');
                $this->vars['__hide'] = array('account_id');
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
                $this->config['db']->SetFields(array('date','account_id','get_currency_name(account_id) currency','get_account_name(account_id) account','round(sum(income)/100,2) income','round(sum(expence)/100,2) expence',"round(GET_ACCOUNT_CURRENT_BALANCE(account_id, date)/100,2) balance"));
                $this->config['db']->SetWhere(array('account_id = ' . addslashes($a[0]) . ' AND','year = ' . addslashes($a[2]) . ' AND','month = ' . addslashes($a[3])));
                $this->config['db']->SetGroup(array('date'));
                $this->config['db']->SetLimit();
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                break;
                }
                if(!empty($data)) {
                    $this->vars['data'] = $data[1];
                    $this->vars['pagination'] = $data[0];
                    $this->vars['pagestext'] = 'Сторінка ' . $data[0]['page'] . ' з ' . $data[0]['totalpages'];
                    $this->vars['pages_nav'] = 1;
                    $this->vars['level'] = $level+1;
                    $this->vars['t_fixed'] = 1;
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->vars['template'] = 'account-details';
                $this->display = 'items.tpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);*/
        }
}


?>
