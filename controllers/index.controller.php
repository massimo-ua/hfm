<?php
require_once('controller_base.php');
Class Controller_Index Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
                $this->vars = array();
                $this->display = '';
        }
        function index() {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('code','round(coalesce(rate,0)/100.00,2) rate','(select code from currencies where home=1 limit 1) home_currency'));
                $this->config['db']->SetTables(array('currencies'));
                $this->config['db']->SetWhere(array('close_date is null ','and home is null'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!isset($data) || empty($data)) $this->vars['noinfo'] = 'Курси валют не встановлено!';
                else $this->vars['data'] = $data;
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id','category',"case when status = 'Виконаний' then 1 else 0 end status"));
                $this->config['db']->SetTables(array('vpayments'));
                $this->config['db']->SetOrder(array('2'));
                $this->config['db']->BuildQuery();
                $data = $this->config['db']->RunQuery();
                if(!isset($data) || empty($data)) $this->vars['noinfo2'] = 'Регулярні платежі не знайдено!';
                else $this->vars['data2'] = $data;
                $this->vars['page_title'] = 'Домашня сторінка';
                $this->vars['burl1'] = 'Нова транзакція';
                $this->vars['burl2'] = 'Нова категорія';
                $this->vars['title1'] = 'Швидкий перехід:';
                $this->vars['title2'] = 'Курси валют:';
                $this->vars['title3'] = 'Регулярні платежі:';
                $this->display = 'index.ttpl.html';
                $this->config->set('controller_vars',$this->vars,true);
                $this->config->set('controller_display',$this->display,true);
        }
}
?>
