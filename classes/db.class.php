<?php
//Класс подключения к базе данных
class DB extends Singleton {
    protected $DB;
    //protected static $instance;  // object instance
        protected function __construct() {
        try {
            $conn = new PDO("pgsql:dbname=".DB_DATABASE.";host=".DB_SERVER,DB_USER,DB_PASSWORD);
        }
        catch(PDOException $e) {
            file_put_contents(SP . 'lib/PDOErrors.txt', $e->getMessage(), FILE_APPEND);
            die('Cannot Connect To Database!');
        }
		$conn->exec("set lc_time to 'uk_UA.UTF-8'");
		$conn->exec("set bytea_output = \"escape\"");
		//$conn->exec('set session character_set_connection="utf8"');
		//$conn->exec('set session character_set_database="utf8"');
		//$conn->exec('set session character_set_results="utf8"');
		//$conn->exec('set session character_set_server="utf8"');
        //$conn->exec("SET lc_time_names = 'uk_UA'");
  		$this->DB=$conn;
        }
}

?>
