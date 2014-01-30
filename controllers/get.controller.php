<?php
require_once('controller_base.php');
Class Controller_Get Extends Controller_Base {
        protected $config;
        private $vars;
        private $display;
        function __construct($config) {
                $this->config = $config;
        }
        function index() {
            $this->vars['__display404'] = true;
            $this->vars['__404_text'] = 'Інформація відсутня!';
            $this->config->set('controller_vars',$this->vars,true);
            $this->config->set('controller_display',$this->display,true);
        }
        function data() {
                //$_POST['t']=1;
                //$_POST['id']=2;
                if(isset($this->config['authorized']) && $this->config['authorized'] === true && $_POST && is_numeric($_POST['t'])) {
                    switch($_POST['t']) {
                    case 1:
                    if(is_numeric($_POST['id']) && $_POST['id'] > 0) {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array('_id','name'));
                    $this->config['db']->SetTables(array('vcategories2'));
                    $this->config['db']->SetWhere(array('type_dig = ' . addslashes($_POST['id'])));
                    $this->config['db']->BuildQuery();
                    $data = $this->config['db']->RunQuery();
                    if(!empty($data)) {
                            echo json_encode($data);
                        }
                    }
                    exit(0);
                    break;
                    case 2:
                    if(is_numeric($_POST['id']) && $_POST['id'] > 0) {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array('_id',"CONCAT(GET_CURRENCY_NAME(_id),' ',name) \"name\""));
                    $this->config['db']->SetTables(array('accounts'));
                    $this->config['db']->SetWhere(array('_id <> ' . addslashes($_POST['id']),'AND currency_id = GET_CURRENCY_ID(' . addslashes($_POST['id']) . ') ','AND close_date is NULL'));
                    $this->config['db']->BuildQuery();
                    $data = $this->config['db']->RunQuery();
                    if(!empty($data)) {
                            echo json_encode($data);
                        }
                    }
                    exit(0);
                    break;
                    case 3:
                    if(is_numeric($_POST['id']) && $_POST['id'] > 0) {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array('_id','name'));
                    $this->config['db']->SetTables(array('vcategories2'));
                    $this->config['db']->SetWhere(array('type_dig = ' . addslashes($_POST['id'])));
                    $this->config['db']->BuildQuery();
                    $data = $this->config['db']->RunQuery();
                    if(!empty($data)) {
                            echo json_encode($data);
                        }
                        }
                        exit(0);
                    break;
                    case 4:
                    if(!empty($_POST['c1']) && !empty($_POST['c1'])) {
                            $r = $this->model->cu->GetGoogleCurrencyExRate($_POST['c1'],$_POST['c2'],1);
                            if(!empty($r) && is_numeric($r)) {
                                $this->model->cu->UpdateCurrencyRate($_POST['c1'],$r);
                                echo $r;
                            }
                            else return false;
                    } else return false;
                    break;
                    }
                }
                else exit(0);
        }
        function fileupload() {
            if(!isset($_FILES['file']) || empty($_FILES['file'])) exit(0);
            else
            {
            $a = $this->config['args'];
            $transaction_id = (isset($a[0]) && is_numeric($a[0]) && $a[0]>0) ? $a[0] : NULL;
            header('Access-Control-Allow-Origin: *');
            $files = $_FILES['file'];
            for($i = 0; $i < count($files['tmp_name']); $i++)
            {
                if ($_FILES['file']["error"][$i] > 0)
                {
                    //echo "Return Code: " . $_FILES["files"]["error"][$i] . "<br>";
                }
                else
                {
                    $filename = explode(".", $_FILES['file']['name'][$i]);
                    $ones[$i]['extension'] = (count($filename) > 1) ? end($filename) : NULL;
                    //$handle = fopen($_FILES['file']['tmp_name'][$i], 'r');
                    //$ones[$i]['filebody'] = fread($handle, filesize($_FILES['file']['tmp_name'][$i]));
                    //$ones[$i]['filebody'] = base64_encode($ones[$i]['filebody']);
                    $ones[$i]['transaction_id'] = $transaction_id;
                    fclose($handle);
                }
            }
            if(empty($ones)) exit(0);
            else
            {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('i');
                    $this->config['db']->SetFields(array('extension','transaction_id'));
                    $this->config['db']->SetTables(array('files'));
                    $this->config['db']->BuildQuery();
                    foreach($ones as $k => $v)
                    {
                        $_id = $this->config['db']->RunQuery($v['extension'],$v['transaction_id']);
                        $name = (empty($v['extension'])) ? str_pad($_id,6,"0",STR_PAD_LEFT) : str_pad($_id,6,"0",STR_PAD_LEFT) . '.' . $v['extension'];
                        move_uploaded_file($_FILES['file']['tmp_name'][$k], UP . $name);
                        echo json_encode(array('files'=>array('file'=>array('_id'=>$_id,'name'=>$name))));
                    }
            }
                exit(0);
            }
    }
    function fileremove() {
        $a = $this->config['args'];
        if(isset($a[0]) && is_numeric($a[0]) && $a[0]>0) {
			        $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetTables(array('files'));
                    $this->config['db']->SetFields(array("concat(lpad(_id::text,6,'0'),'.',extension) \"name\""));
                    $this->config['db']->SetWhere(array('_id = ' . addslashes($a[0])));
                    $this->config['db']->BuildQuery();
                    $data = $this->config['db']->RunQuery();
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('d');
                    $this->config['db']->SetTables(array('files'));
                    $this->config['db']->SetWhere(array('_id = ' . addslashes($a[0])));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery();
                    unlink(UP . $data[0]['name']);
                    
                    exit(0);
        }
        else exit(0);
    }
    function file() {
    $a = $this->config['args'];
        if(isset($a[0]) && is_numeric($a[0]) && $a[0]>0) {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetTables(array('files'));
                    $this->config['db']->SetFields(array("concat(lpad(_id::text,6,'0'),'.',extension) \"name\""));
                    $this->config['db']->SetWhere(array('_id = ' . addslashes($a[0])));
                    $this->config['db']->BuildQuery();
                    $data = $this->config['db']->RunQuery();
                    if(!isset($data) || empty($data)) exit(0);
                    else {
					if (file_exists(UP . $data[0]['name'])) {
                    header("Content-Description: File Transfer");
                    header("Content-type: application/octet-stream");
                    header('Content-Disposition: inline; filename="' . $data[0]['name'] . '"');
                    header('Content-Disposition: attachment; filename="'.$data[0]['name'].'"');
                    header("Content-Transfer-Encoding: binary");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Pragma: public");
                    ob_clean();
                    flush();
                    readfile(UP . $data[0]['name']);
                    exit(0);
					}
                    }
        }
        else exit(0);
    }
}
?>
