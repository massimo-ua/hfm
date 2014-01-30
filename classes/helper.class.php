<?php
Class Helper extends Singleton {
    private $config;
    protected function __construct($config) {
        $this->config = $config;
        if(!isset($_SESSION['rate_is_updated']) || $_SESSION['rate_is_updated'] === false) {
            //echo 'OK'; exit(0);
            $this->InstantCurrencyExRateUpd();
        }
    }
        protected function GetHistUAHCurrencyExRate($currency,$date,$amount=1) {
        if(isset($currency) && isset($date) && is_numeric($amount) && $amount > 0) {
		$currency = urlencode(strtoupper($currency));
		$date = urlencode($date);
        $url = "http://www.xe.com/currencytables/?from=${currency}&date=${date}";
        $ch = curl_init();
		$timeout = 30;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rawdata = curl_exec($ch);
		if(!empty($rawdata)) {
		    curl_close($ch);
		    preg_match('/UAH<\/a><\/td><td>Ukrainian Hryvna<\/td><td class=\"ICTRate\">[0-9\.]+<\/td>/', $rawdata, $matches);
            preg_match('/[0-9\.]+/',$matches[0],$a);
            if(is_numeric($a[0]) && $a[0] > 0) {
                return round($a[0]*$amount,2);
            }
            else return false;
		}
		else return false;
        }
        else return false;
        }
    /*    protected function SaveCurrencyRateHistory($code,$rate,$date) {
    if(isset($code) && isset($rate)) {
        ($code) ? $ones['code'] = mb_convert_case($code, MB_CASE_UPPER, "UTF-8") : $ones['code'] = NULL;
        ($rate) ? $ones['rate'] = preg_replace('/[^0-9]/i', '', number_format(round($rate,2),2,'.','')) : $ones['rate'] = NULL;
        if(!empty($ones['code']) && !empty($ones['rate'])) {
            $sql = ("INSERT INTO currency_rate_history (currency_id,rate,change_date) VALUES(2,{$ones['rate']},'${date}')");
            //print_r($sql);
            echo $date . '<br>';
            $this->DB->Execute($sql);
        }
        else return false;
    }
    else return false;
    }*/
    //once code
    /*protected function GetHomeCurrency($mode=1) {
        if(is_numeric($mode) && in_array($mode,array(1,2))) {
        $h = $this->GetId(1,'currencies','home');
        switch($mode) {
        default:
        return false;
        break;
        case 1:
        if(!empty($h['code'])) return $h['code'];
        else return false;
        break;
        case 2:
        if(!empty($h['_id'])) return $h['_id'];
        else return false;
        break;
        }
        }
        else return false;
    }*/
    protected function InstantCurrencyExRateUpd() {
        $this->config['db']->ResetQuery();
        $this->config['db']->SetType();
        $this->config['db']->SetFields(array('case when exists(select _id from currency_rate_history where change_date = current_date) then 1 else 2 end f'));
        $this->config['db']->SetTables(array('currency_rate_history'));
        $this->config['db']->BuildQuery();
        $data = $this->config['db']->RunQuery();
        if(isset($data[0]['f']) && $data[0]['f'] == 2)
        {
            $this->config['db']->ResetQuery();
            $this->config['db']->SetType();
            $this->config['db']->SetFields(array('(select code from currencies where home=1 and close_date is null limit 1) home','code'));
            $this->config['db']->SetTables(array('currencies'));
            $this->config['db']->SetWhere(array("coalesce(home,0)<>1 ","AND close_date is null"));
            $this->config['db']->BuildQuery();
            $data = $this->config['db']->RunQuery();
            //print_r($data); exit(0);
            if(!empty($data)) {
                $c1=0;
                $c2=0;
                foreach($data as $i) {
                    if(isset($i['home']) && isset($i['code'])) {
                        $c1++;
                        $r = $this->GetGoogleCurrencyExRate($i['code'],$i['home'],1);
                        if($r) {
                            $this->UpdateCurrencyRate($i['code'],$r);
                            $c2++;
                        }
                        else $this->config['logger']->eLog(__METHOD__ . " Неможливо завантажити курс для валюти {$i['code']}!");
                    }
                }
                if($c1==$c2) $_SESSION['rate_is_updated']=true;
            }
            else {
                //create log entry abount incident
                $this->config['logger']->eLog(__METHOD__ . ' Неможливо завантажити перелік валют для оновлення!');
            }
        }
        else
        {
            //create log entry abount incident
            if(!isset($data[0]['f']) || !in_array($data[0]['f'],array(1,2))) $this->config['logger']->eLog(__METHOD__ . ' Неможливо перевірити чи оновлювалися курси валют сьогодні!');
        }
	}
    protected function GetGoogleCurrencyExRate($from_Currency,$to_Currency,$amount=1) {
	//thnx to http://www.dynamicguru.com/php/currency-conversion-using-php-and-google-calculator-api/
	if(isset($from_Currency) && isset($to_Currency) && isset($amount) && is_numeric($amount)) {
		$amount = urlencode($amount);
		$from_Currency = urlencode($from_Currency);
		$to_Currency = urlencode($to_Currency);
		$url = "https://www.google.com/finance?q=$from_Currency$to_Currency";
		$ch = curl_init();
		$timeout = 15;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rawdata = curl_exec($ch);
		if(!empty($rawdata)) {
			curl_close($ch);
		    preg_match("/[0-9\.]+\\s{$to_Currency}/", $rawdata, $matches);
            preg_match('/[0-9\.]+/',$matches[0],$a);
            if(is_numeric($a[0]) && $a[0] > 0) {
                $this->config['logger']->eLog(__METHOD__ . " Отримано курс валюти з Google {$amount} {$from_Currency} = {$a[0]} {$to_Currency}!");
                return round($a[0]*$amount,2);
            }
            else return false;
		}
		else return false;
	}
		else return false;
	}
    protected function UpdateCurrencyRate($code,$rate) {
    if(isset($code) && isset($rate)) {
        ($code) ? $ones['code'] = mb_convert_case($code, MB_CASE_UPPER, "UTF-8") : $ones['code'] = NULL;
        ($rate) ? $ones['rate'] = round($rate*100) : $ones['rate'] = NULL;
        if(!empty($ones['code']) && !empty($ones['rate'])) {
            $this->config['db']->ResetQuery();
            $this->config['db']->SetType('u');
            $this->config['db']->SetTables(array('currencies'));
            $this->config['db']->SetFields(array('rate'=>'?'));
            $this->config['db']->SetWhere(array("code = ?"));
            $this->config['db']->BuildQuery();
            $this->config['db']->RunQuery($ones['rate'],"{$ones['code']}");
        }
        else return false;
    }
    else return false;
    }
}
?>
