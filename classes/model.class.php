<?php
Class Model extends Singleton {
    private $model;
    private $config;
    private $type;
    protected function __construct($config) {
        $this->config = $config;
        $this->type = array(
                    array('value' => 1,'text' => 'Прихід'),
                    array('value' => 2,'text' => 'Розхід')
                    );
    }
    private function SetModel($cc=false) {
        switch($cc)
        {
            default:
                return false;
            break;
            case 'accounts':
                //prepare additional reference data
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id "value"','name "text"'));
                $this->config['db']->SetTables(array('lvcurrencies'));
                $this->config['db']->BuildQuery();
                $cl = $this->config['db']->RunQuery();
                    $this->model = array(
                    '_id' => array(
                    'type' => 'hidden',
                    'name' => '_id'
                    ),
                    'name' => array(
                    'help_text' => 'Назва',
                    'id' => 'name',
                    'type' => 'text',
                    'name' => 'name'
                    ),
                    'currency_id' => array(
                    'help_text' => 'Валюта',
                    'id' => 'currency_id',
                    'type' => 'select',
                    'name' => 'currency_id',
                    'loop' => $cl
                    ),
                    'opening_balance' => array(
                    'help_text' => 'Початковий баланс',
                    'id' => 'amount',
                    'type' => 'text',
                    'name' => 'opening_balance',
                    'value' => '0.00'
                    )
                    );
            break;
            case 'categories':
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id "value"','name "text"'));
                $this->config['db']->SetTables(array('vcategories2'));
                $this->config['db']->SetWhere(array('parent_id IS NULL'));
                $this->config['db']->BuildQuery();
                $cl = $this->config['db']->RunQuery();
                    $this->model = array(
                    '_id' => array(
                    'type' => 'hidden',
                    'name' => '_id'
                    ),
                    'type' => array(
                    'help_text' => 'Тип',
                    'id' => 'type',
                    'type' => 'select',
                    'name' => 'type',
                    'loop' => $this->type
                    ),
                    'name' => array(
                    'help_text' => 'Назва',
                    'id' => 'name',
                    'type' => 'text',
                    'name' => 'name'
                    ),
                    'parent_id' => array(
                    'help_text' => 'Батьківська категорія',
                    'id' => 'parent_id',
                    'type' => 'select',
                    'name' => 'parent_id',
                    'loop' => $cl
                    ),
                    'visible' => array(
                    'help_text' => 'Показувати',
                    'id' => 'visible',
                    'type' => 'checkbox',
                    'name' => 'visible',
                    'value' => '1'
                    )
                    );
            break;
            case 'currencies':
            $this->model = array(
                    '_id' => array(
                    'help_text' => 'Цифровий код',
                    'id' => '_id',
                    'type' => 'text',
                    'name' => '_id'
                    ),
                    'symbol' => array(
                    'help_text' => 'Символ',
                    'id' => 'symbol',
                    'type' => 'text',
                    'name' => 'symbol'
                    ),
                    'code' => array(
                    'help_text' => 'Символьний код',
                    'id' => 'code',
                    'type' => 'text',
                    'name' => 'code'
                    ),
                    'home' => array(
                    'help_text' => 'Домашня',
                    'id' => 'home',
                    'type' => 'checkbox',
                    'name' => 'home',
                    'value' => '0'
                    )
                    );
            break;
            case 'plans':
                //prepare additional reference data
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id "value"','name "text"'));
                $this->config['db']->SetTables(array('lvcurrencies'));
                $this->config['db']->BuildQuery();
                $cl = $this->config['db']->RunQuery();
            $this->model = array(
                    '_id' => array(
                    'type' => 'hidden',
                    'name' => '_id'
                    ),
                    'name' => array(
                    'help_text' => 'Назва',
                    'id' => 'name',
                    'type' => 'text',
                    'name' => 'name'
                    ),
                    'currency_id' => array(
                    'help_text' => 'Валюта',
                    'id' => 'currency_id',
                    'type' => 'select',
                    'name' => 'currency_id',
                    'loop' => $cl
                    ),
                    'sdate' => array(
                    'help_text' => 'Дата початку',
                    'id' => 'date01',
                    'class' => 'date',
                    'type' => 'text',
                    'name' => 'sdate'
                    ),
                    'edate' => array(
                    'help_text' => 'Дата закінчення',
                    'id' => 'date02',
                    'class' => 'date',
                    'type' => 'text',
                    'name' => 'edate'
                    ),
                    'amount' => array(
                    'help_text' => 'Сума',
                    'id' => 'amount',
                    'type' => 'text',
                    'name' => 'amount',
                    'value' => '0.00'
                    ),
                    'comment' => array(
                    'help_text' => 'Коментар',
                    'id' => 'comment',
                    'type' => 'textarea',
                    'name' => 'comment'
                    )
                    );
            break;
            case 'editplan':
            $this->model = array(
                    '_id' => array(
                    'type' => 'hidden',
                    'name' => '_id'
                    ),
                    'name' => array(
                    'help_text' => 'Назва',
                    'id' => 'name',
                    'type' => 'text',
                    'name' => 'name'
                    ),
                    'edate' => array(
                    'help_text' => 'Дата закінчення',
                    'id' => 'date02',
                    'class' => 'date',
                    'type' => 'text',
                    'name' => 'edate'
                    ),
                    'amount' => array(
                    'help_text' => 'Сума',
                    'id' => 'amount',
                    'type' => 'text',
                    'name' => 'amount',
                    'value' => '0.00'
                    ),
                    'comment' => array(
                    'help_text' => 'Коментар',
                    'id' => 'comment',
                    'type' => 'textarea',
                    'name' => 'comment'
                    )
                    );
            break;
            case "transactions":
            case "transactions2":
                //prepare additional reference data
                array_push($this->type,array('value' => 3,'text' => 'Трансфер'));
                //
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id "value"','name "text"'));
                $this->config['db']->SetTables(array('lvaccounts'));
                $this->config['db']->BuildQuery();
                $ac = $this->config['db']->RunQuery();
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id "value"','name "text"'));
                $this->config['db']->SetTables(array('vcategories2'));
                $this->config['db']->SetOrder(array('2 ASC'));
                $this->config['db']->BuildQuery();
                $cl = $this->config['db']->RunQuery();
                if(isset($this->config['args'][0]) && is_numeric($this->config['args'][0])) {
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id "value"','name "text"'));
                $this->config['db']->SetTables(array('vfiles'));
                $this->config['db']->SetWhere(array('transaction_id = ' . $this->config['args'][0]));
                $this->config['db']->BuildQuery();
                $fl = $this->config['db']->RunQuery();
                }
                else $fl = false;
            $this->model = array(
                    '_id' => array(
                    'id' => '_id',
                    'type' => 'hidden',
                    'name' => '_id'
                    ),
                    'type' => array(
                    'help_text' => 'Тип',
                    'id' => 'type',
                    'type' => 'select',
                    'name' => 'type',
                    'loop' => $this->type
                    ),
                    'account_id' => array(
                    'help_text' => 'Рахунок А',
                    'id' => 'account_id',
                    'type' => 'select',
                    'name' => 'account_id',
                    'loop' => $ac
                    ),
                    'mirror_account_id' => array(
                    'help_text' => 'Рахунок Б',
                    'id' => 'mirror_account_id',
                    'type' => 'select',
                    'name' => 'mirror_account_id',
                    'disabled' => true,
                    'loop' => array(array('value'=>"",'text'=>'Оберіть рахунок'))
                    ),
                    'category_id' => array(
                    'help_text' => 'Категорія',
                    'id' => 'category_id',
                    'type' => 'select',
                    'name' => 'category_id',
                    'loop' => $cl
                    ),
                    'amount' => array(
                    'help_text' => 'Сума',
                    'id' => 'amount',
                    'type' => 'text',
                    'name' => 'amount',
                    'value' => '0.00'
                    ),
                    'date' => array(
                    'help_text' => 'Дата',
                    'id' => 'date01',
                    'class' => 'date',
                    'type' => 'text',
                    'name' => 'date'
                    ),
                    'file' => array(
                    'help_text' => 'Файл',
                    'id' => 'fileupload',
                    'type' => 'file',
                    'name' => 'file[]',
                    'method' => 'POST',
                    'multiple' => true,
                    'loop' => $fl
                    ),
                    'notes' => array(
                    'help_text' => 'Примітка',
                    'id' => 'notes',
                    'type' => 'textarea',
                    'name' => 'notes'
                    )
                    );
                    //special model modifications
                    if($cc == 'transactions2') {
                        $this->model['mirror_account_id']['loop']=$ac;
                        $this->model['mirror_account_id']['disabled']=false;
                        $this->model['mirror_account_id']['readonly']=true;
                    }
            break;
            case "payments":
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('_id "value"','name "text"'));
                $this->config['db']->SetTables(array('vcategories2'));
                $this->config['db']->SetOrder(array('2 ASC'));
                $this->config['db']->BuildQuery();
                $cl = $this->config['db']->RunQuery();
				$pr = array(
                    array('value' => 1,'text' => 'Щоденний'),
                    array('value' => 2,'text' => 'Щотижневий'),
                    array('value' => 3,'text' => 'Щомісячний'),
                    array('value' => 5,'text' => 'Щоквартальний'),
                    array('value' => 4,'text' => 'Щорічний')
                    );
                $st = array(
                    array('value' => 1,'text' => 'Активний'),
                    array('value' => 2,'text' => 'Призупинений')
                    );
                $this->model = array(
                    '_id' => array(
                    'type' => 'hidden',
                    'name' => '_id'
                    ),
                    'periodicity' => array(
                    'help_text' => 'Періодичність',
                    'id' => 'periodicity',
                    'type' => 'select',
                    'name' => 'periodicity',
                    'loop' => $pr
                    ),
                    'type' => array(
                    'help_text' => 'Тип',
                    'id' => 'type',
                    'type' => 'select',
                    'name' => 'type',
                    'loop' => $this->type,
                    'value' => 2
                    ),
                    'category_id' => array(
                    'help_text' => 'Категорія',
                    'id' => 'category_id',
                    'type' => 'select',
                    'name' => 'category_id',
                    'loop' => $cl
                    ),
                    'state' => array(
                    'help_text' => 'Статус',
                    'id' => 'state',
                    'type' => 'select',
                    'name' => 'state',
                    'loop' => $st,
                    'value' => 1
                    )
                    );
            break;
            case "reports":
                //prepare additional reference data
                //
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('distinct extract(year from date) "value"','extract(year from date) "text"'));
                $this->config['db']->SetTables(array('transactions'));
                $this->config['db']->SetOrder(array('1 desc'));
                $this->config['db']->BuildQuery();
                $y = $this->config['db']->RunQuery();
                $this->config['db']->ResetQuery();
                $this->config['db']->SetType();
                $this->config['db']->SetFields(array('get_currency_name(account_id) "value"','get_currency_name(account_id) "text"'));
                $this->config['db']->SetTables(array('transactions'));
                $this->config['db']->SetGroup(array('1,2'));
                $this->config['db']->SetOrder(array('2 desc'));
                $this->config['db']->BuildQuery();
                $c = $this->config['db']->RunQuery();
                $si = array(0=>array('value'=>1,'text'=>'Показувати приховані категорії'));
                $this->model = array(
                    1 => array(
                    'name' => 'Річний звіт про прихід/розхід в розрізі місяців та валют',
                    'controls' => array(
                    0=>array(
                    'help_text' => '',
                    'id' => 'currency',
                    'type' => 'radio',
                    'name' => 'currency',
                    'loop' => $c
                    ),
                    1=>array(
                    'help_text' => '',
                    'id' => 'year',
                    'type' => 'radio',
                    'name' => 'year',
                    'loop' => $y
                    ),
                    2=>array(
                    'help_text' => '',
                    'id' => 'si',
                    'type' => 'checkbox',
                    'name' => 'si',
                    'loop' => $si
                    )
                    )
                    ),
                    2 => array(
                    'name' => 'Річний звіт про масову частку категорій від загальної суми',
                    'controls' => array(
                    0=>array(
                    'help_text' => '',
                    'id' => 'currency',
                    'type' => 'radio',
                    'name' => 'currency',
                    'loop' => $c
                    ),
                    1=>array(
                    'help_text' => '',
                    'id' => 'year',
                    'type' => 'radio',
                    'name' => 'year',
                    'loop' => $y
                    ),
                    2=>array(
                    'help_text' => '',
                    'id' => 'type',
                    'type' => 'radio',
                    'name' => 'type',
                    'loop' => $this->type
                    ),
                    3=>array(
                    'help_text' => '',
                    'id' => 'si',
                    'type' => 'checkbox',
                    'name' => 'si',
                    'loop' => $si
                    )
                    )
                    )/*,
                    3 => array(
                    'name' => 'Звіт про розмір річного доходу в розрізі валют',
                    'controls' => array(
                    0=>array(
                    'help_text' => '',
                    'id' => 'si',
                    'type' => 'checkbox',
                    'name' => 'si',
                    'loop' => $si
                    )
                    )
                    )*/
                    );
            break;
        }
    }
    public function GetModel($cc=false,$data=false)
    {
        if($cc)
        {
            if(isset($data[0]))
            {
                if(is_array($data[0])) {
                    $this->SetModel($cc);
                    foreach($data[0] as $dk => $dv) {
                            if(!empty($dv)) {
                                $this->model[$dk]['value'] = $dv;
                            }
                    }
                    return $this->model;
                }
                else {
                    $this->SetModel($cc);
                    return $this->model;
                }
            }
            else
            {
                $this->SetModel($cc);
                return $this->model;
            }
        }
        else return false;
    }
}
?>
