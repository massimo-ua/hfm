<?php
Class Template extends Singleton {
        private $config;
        private $vars;
        private $display;
        private $twig;
        protected function __construct($config) {
                $this->config = $config;
                require_once(SP . 'lib/Twig/Autoloader.php');
                if (class_exists('Twig_Autoloader')) {
                    Twig_Autoloader::register();
                    $loader = new Twig_Loader_Filesystem(template_dir);
                    $this->twig = new Twig_Environment($loader, array(
                        'cache' => compile_dir,
                        'auto_reload' => compile_check,
                        'debug' => debugging
                    ));
                }
                else die ('Error Templates Engine Cannot Be Initialized!');
                if(isset($this->config['authorized']) && $this->config['authorized'] === true)
                {
                $this->vars['session'] = $_SESSION;
                $this->vars["hi"] = 'Привіт';
                $this->vars["logout"] = 'Вихід';
                //
                $this->vars["current_controller"] = $this->config->get('cc');
                //
                $c = $this->config->ListControllers();
                $this->vars["mainmenu"] = $c;
                //datepicker
                $this->vars["months"] = array('Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень');
                $this->vars["days_n"] = array('Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб');
                $this->vars["first_d"] = 1;
                //items
                $this->vars["ch_type"] = 'Оберіть тип';
                $this->vars["ch_category"] = 'Оберіть категорію';
                $this->vars["ch_account"] = 'Оберіть рахунок';
                $this->vars["ch_currency"] = 'Оберіть валюту';
                //page navigation
                $this->vars["first"] = 'Перша';
                $this->vars["last"] = 'Остання';
                $this->vars["forward"] = 'Вперед';
                $this->vars["back"] = 'Назад';
                //common tags
                $this->vars['total_text'] = 'Разом';
                $this->vars['submit'] = 'Виконати';
                $this->vars['choose'] = 'Обрати';
                $this->vars['remove'] = 'Видалити';
                //
                $vars = $this->config->get('controller_vars');
                if(isset($vars) && !empty($vars)) {
                    $this->vars = array_merge($this->vars, $vars);
                    $this->config->remove('controller_vars');
                    unset($vars);
                }
                $display = $this->config->get('controller_display');
                if(isset($display) && !empty($display)) {
                    $this->display = $display;
                    $this->config->remove('controller_display');
                    unset($display);
                }
                }
                else
                {
                    $this->vars["page_title"] = 'Авторизація';
                    $this->vars["h1"] = 'Авторизація';
                    $this->vars["d1"] = 'Логін';
                    $this->vars["d2"] = 'Пароль';
                    $this->vars["submit"] = 'Увійти';
                    $this->display = 'login.ttpl.html';
                }

        }
        function Show() {
            if(isset($this->vars['__display404']) && $this->vars['__display404'] === true) $this->Show_404();
            else
            {
                if(isset($this->display) && !empty($this->display))
                {
                    if(isset($this->vars) && !empty($this->vars)) {
                            $this->twig->display("{$this->display}",$this->vars);
                    }
                    else $this->twig->display("{$this->display}");
                }
                else
                {
                    throw new Exception('Unable to display Template, template name does not set!');
                    return false;
                }
            }
        }
        function Show_404() {
            $this->twig->display("404.ttpl.html",$this->vars);
        }
}
?>
