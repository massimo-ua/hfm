<?php
require_once('controller_base.php');
Class Controller_Users Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
                $this->vars['th'] = array('UID','Логін','П.І.Б.');
        }
        function index() {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','login','name'));
                $this->config['db']->SetTables(array('users'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!empty($data)) {
                    $this->vars['data'] = $data;
                    $this->vars['__controls'] = array(
                    0 => array('href'=> WR . 'users/edit','title'=>'Редагувати','icon' => 'pencil32.png','id' => true),
                    1 => array('href'=> WR . 'users/delete','title'=>'Видалити','icon' => 'minus32.png','id' => true),
                    100 => array('href'=> WR . 'users/create','title'=>'Створити','icon' => 'paperplus32.png','id' => false)
                    );
                    $this->vars['jq'] =1;
                    $this->vars['jtable'] = 1;
                    $this->vars['t_fixed'] = 1;
                    $this->vars['__sorting'] = array(2 => 'ASC');
                }
                else $this->vars['noinfo'] = 'Інформація відсутня!';
                $this->vars['template'] = 'basic-table';
                $this->display = 'basic-table.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
        function login() {
                if(isset($_POST) && $_POST['login'] && $_POST['password'])
                {
                    $this->config['auth']->Login($_POST['login'],$_POST['password']);
                    header( 'Location: ' . WR );
                }
        }
        function logout() {
            $this->config['auth']->Logout();
            header( 'Location: ' . WR );
        }
        function manage() {
            if(isset($_POST) && !empty($_POST))
            {
                //обрабатываем переданные из формы данные
            }
            else
            {
                //отображаем пустую форму 
            }
        }
}


?>
