<?php
Class Auth extends Singleton {
    private $config;
    protected function __construct($config) {
        $this->config = $config;
        $this->config->set('authorized',false,true);
    }
    public function Login($username,$password) {
        if(empty($username) || empty($password))
        {
            //create log entry
            $this->config['logger']->eLog(__METHOD__ . ' Спроба входу з пустим username чи password!');
            $this->config->set('authorized',false,true);
            return false;
        }
        else
        {
            $username = addslashes($username);
            $password = sha1(base64_encode($password));
            $this->config['db']->ResetQuery();
            $this->config['db']->SetType();
            $this->config['db']->SetFields(array('_id','name','password'));
            $this->config['db']->SetTables(array('users'));
            $this->config['db']->SetWhere(array("login = '{$username}'"));
            $this->config['db']->BuildQuery();
            $data = $this->config['db']->RunQuery();
            if(empty($data))
            {
                //create log entry
                $this->config['logger']->eLog(__METHOD__ . ' Користувач з login = ' . $username . ' не знайдений!');
                $this->config->set('authorized',false,true);
                return false;
            }
            else
            {
                if(isset($data[0]['password']) && $data[0]['password'] == $password)
                {
                    $hash = base64_encode(sha1(uniqid(substr(rand(),4),'TRUE')));
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType('u');
                    $this->config['db']->SetFields(array('hash'=>'?'));
                    $this->config['db']->SetWhere(array("_id = ?"));
                    $this->config['db']->BuildQuery();
                    $this->config['db']->RunQuery($hash,$data[0]['_id']);
                    session_regenerate_id();
                    $_SESSION['uid'] = $data[0]['_id'];
                    $_SESSION['uname'] = $data[0]['name'];
                    $_SESSION['hash'] = $hash;
                    $this->config->set('authorized',true,true);
                    $this->config->set('uid',$data[0]['_id'],true);
                    $this->config['logger']->eLog(__METHOD__ . ' Користувач ' . $_SESSION['uname'] . ' був успішно авторизований!');
                    return true;
                }
                else
                {
                    //create log entry
                    $this->config['logger']->eLog(__METHOD__ . ' Користувач ' . $username . ' вказано невірний пароль!');
                    $this->config->set('authorized',false,true);
                    return false;
                }
            }
        }
    }
    public function Logout() {
        $this->config['logger']->eLog(__METHOD__ . ' Користувач ' . $_SESSION['uname'] . ' вийшов успішно!');
        $this->config->set('authorized',false,true);
        session_destroy();
    }
    public function CheckLogin() {
        if(isset($_SESSION['uid']) && isset($_SESSION['hash']))
        {
                    $this->config['db']->ResetQuery();
                    $this->config['db']->SetType();
                    $this->config['db']->SetFields(array('count(*) n'));
                    $this->config['db']->SetTables(array('users'));
                    $this->config['db']->SetWhere(array("_id = {$_SESSION['uid']} AND", "hash = '{$_SESSION['hash']}'"));
                    $this->config['db']->BuildQuery();
                    $data = $this->config['db']->RunQuery();
                    if(isset($data[0]['n']) && $data[0]['n'] == 1)
                    {
                        if($this->config->get('authorized') != true) $this->config->set('authorized',true,true);
                    }
                    else
                    {
                        //create log entry abount incident
                        $this->config['logger']->eLog(__METHOD__ . ' Невдала спроба перевірки авторизації з uid = ' . $_SESSION['uid'] . ' і hash = ' . $_SESSION['hash'] . '!');
                        if($this->config->get('authorized') != false) $this->config->set('authorized',false,true);
                    }
        }
        else
        {
            if($this->config->get('authorized') != false) $this->config->set('authorized',false,true);
        }
    }
}
?>
