<?php
//constants
define ('DS', DIRECTORY_SEPARATOR);
define ('SP', realpath(dirname(__FILE__) . DS . '..' . DS) . DS);
define ('WR', 'http://192.168.33.222/hfm/');
define ('RS', '/');
define ('PIC_PATH', WR . 'pics' . DS );
define ('UP', SP . 'files' . DS);
define ('AN',5); //number of controller arguments except controller_id and method_id
// Database
define('DB_SERVER', '192.168.33.222');
define('DB_USER', 'mdb3');
define('DB_PASSWORD', '12345678');
define('DB_DATABASE', 'mdb3');
//Smarty
define('compile_check',true);
define('debugging',false);
define('template_dir', SP . 'templates');
define('compile_dir', SP . 'templates_c');
define('cache_dir', SP . 'cache');
// layout
define('ITEMS_PER_PAGE', 20);
define('NAV_DISPERSION', 10);
define('UCONTROLLERS', 'index,transactions,accounts,categories,currencies,plans,payments,reports,logs,users');
// ini
?>
