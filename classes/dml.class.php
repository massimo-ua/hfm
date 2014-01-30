<?php
Class DML Extends DB {
        protected $DB;
        protected $type;
        protected $fields;
        protected $tables;
        protected $where;
        protected $group;
        protected $order;
        protected $limit;
        protected $sql;
        protected $total;
        protected $page;
        protected $paging;
        
        protected function __construct() {
            $this->DB = DB::getInstance()->DB;
            $this->type='SELECT';
            $this->fields='';
            $this->table='';
            $this->where='';
            $this->group='';
            $this->order='';
            $this->limit='';
            $this->sql='';
            $this->total=0;
            $this->page = 1;
            $this->paging = false;
        }
        public function SetType($type=NULL) {
            switch($type) {
                    case "i":
                    $this->type = 'INSERT';
                    break;
                    case "u":
                    $this->type = 'UPDATE';
                    break;
                    case "d":
                    $this->type = 'DELETE';
                    break;
                    case "c":
                    $this->type = 'CALL';
                    break;
                    default:
                    $this->type = 'SELECT';
                    break;
            }
        }
        public function GetLII($var='@out') {
            $s = $this->DB->query('select ' . $var . ' i');
            $r = $s->fetchAll(PDO::FETCH_ASSOC);
            if(empty($r)) return false;
            else return $r;
        }
        public function SetFields(array $fields) {
            switch($this->type) {
                default:
                    if(empty($fields)) $this->fields='*';
                    else $this->fields = implode(',',$fields);
                break;
                case 'INSERT':
                    if(empty($fields)) return false;
                    else
                    {
                        $this->fields = '(' . implode(',',$fields) . ') VALUES (' . substr(str_repeat("?,", count($fields)),0,-1) . ')';
                    }
                break;
                case 'UPDATE':
                    if(empty($fields)) return false;
                    else
                    {
                        $this->fields = '';
                        foreach($fields as $k => $v)
                        {
                            $this->fields .= $k . '=' . $v . ','; 
                        }
                        $this->fields = substr($this->fields,0,-1);
                    }
                break;
            }
        }
        public function SetTables(array $tables) {
            if(empty($tables)) return false;
            else $this->tables = implode(',',$tables);
        }
        public function SetWhere(array $where) {
            if(empty($where)) return false;
            else $this->where = implode(' ',$where);
        }
        public function SetGroup(array $group) {
            if(empty($group)) return false;
            else $this->group = implode(',',$group);
        }
        public function SetOrder(array $order) {
            if(empty($order)) return false;
            else $this->order = implode(',',$order);
        }
        public function SetLimit($page=1) {
            $page = (is_numeric($page) && $page >= 1) ? $page : 1;
            
            $this->limit = 'LIMIT ' . ($page-1) . ', ' . $page*ITEMS_PER_PAGE;
        }
        public function SetPaging($value) {
            if(is_bool($value)) $this->paging = $value;
        }
        public function ResetQuery() {
            $this->type='';
            $this->fields='';
            $this->table='';
            $this->where='';
            $this->group='';
            $this->order='';
            $this->limit='';
            $this->sql='';
            $this->total=0;
            $this->page = 1;
            $this->paging = false;
        }
        public function BuildQuery($raw=false,$sql='') {
            if ($raw == false) {
                if(((empty($this->type) || empty($this->fields) || empty($this->tables)) && $this->type != 'DELETE') || ($this->type == 'CALL' && empty($this->fields)) || ($this->type == 'DELETE' && empty($this->tables))) {
                throw new Exception ('Not enough parameters for CONSTRUCT query: [' . $this->type . '] ' . '[' . $this->fields . '] ' . '[' . $this->tables . '] ');
                return false;
            }
            else {
                
                $this->sql .= $this->type . ' ';
                switch($this->type)
                {
                    default:
                    $this->sql .= $this->fields . ' ';
                    if(!empty($this->tables)) $this->sql .= 'FROM ' . $this->tables . ' ';
                    if(!empty($this->where)) $this->sql .= 'WHERE ' . $this->where . ' ';
                    if(!empty($this->group)) $this->sql .= 'GROUP BY ' . $this->group . ' ';
                    if(!empty($this->order)) $this->sql .= 'ORDER BY ' . $this->order . ' ';
                    if(!empty($this->limit)) $this->sql .= $this->limit;
                    break;
                    case 'INSERT':
                    if(!empty($this->tables)) $this->sql .= 'INTO ' . $this->tables . ' ';
                    if(!empty($this->fields)) $this->sql .= $this->fields . ' ';
                    break;
                    case 'UPDATE':
                    if(!empty($this->tables)) $this->sql .= $this->tables . ' ';
                    if(!empty($this->fields)) $this->sql .= 'SET ' . $this->fields . ' ';
                    if(!empty($this->where)) $this->sql .= 'WHERE ' . $this->where;
                    break;
                    case 'CALL':
                    if(!empty($this->tables)) $this->sql .= $this->tables;
                    if(!empty($this->fields)) $this->sql .= '(' . $this->fields . ')';
                    break;
                }
                $this->sql = trim($this->sql);
                    if(empty($this->sql)) return false;
                    
                    else return true;
                }
                }
                else {
                    if(empty($sql)) return false;
                    else {
                    $this->sql = $sql;
                    return true;
                    }
                }
        }
        public function RunQuery() {
            $a = func_get_args();
            if(empty($this->sql)) return false;
            else {
                switch($this->type) {
                    default:
                    try
                    {
                        $s = $this->DB->query($this->sql);
                    }
                    catch (PDOException $e)
                    {
                        //log exeption
                        return false;
                    }
                    //print_r($this->sql);
                    $r = $s->fetchAll(PDO::FETCH_ASSOC);
                    if(empty($r)) return false;
                    else {
                        if($this->paging === true) {
                            $c = $this->GetNavigation();
                            return array($c,$r);
                        }
                        else return $r;
                    }
                    break;
                    case 'CALL':
                    //print_r($this->sql); die;
                        try
                            {
                                $s = $this->DB->query($this->sql);
                            }
                            catch (PDOException $e)
                            {
                                //log exeption
                                return false;
                            }
                    break;
                    case 'INSERT':
                    case 'UPDATE':
                    $s = $this->DB->prepare($this->sql);
                    //print_r($this->sql);  print_r($a);
                    try
                    {
                        if(empty($a)) $s = $s->execute();
                        else $s->execute($a);
                    }
                    catch (PDOException $e)
                    {
                        print_r($e->errorInfo()); //log exeption
                        return false;
                    }
                    //postgresql reuire sequence name
                    return $this->DB->lastInsertId($this->tables.'_id_seq');
                    break;
                }
            }
        }
        private function GetNavigation() {
            $this->GetRowCount();
            if(isset($this->total) && isset($this->page) && $this->total > 0 && $this->page > 0) {
                $nav = array();
                $nav['page'] = $this->page;
                $nav['totalpages'] = ceil($this->total/ITEMS_PER_PAGE);
                $nav['start'] = ($nav['page'] > NAV_DISPERSION) ? $nav['page']+1-NAV_DISPERSION : 1;
                $nav['loop'] = (($nav['page'] + NAV_DISPERSION) <= $nav['totalpages']) ? $nav['loop'] = $nav['page'] + NAV_DISPERSION : $nav['loop'] = $nav['totalpages']+1;
                if(empty($nav)) return false;
                else return $nav;
            }
            else return false;
        }
        private function GetRowCount() {
            if($this->type == 'SELECT') {
            $this->sql = '';
            $this->order = '';
            $this->limit = '';
            $this->BuildQuery();
            try {
                $s = $this->DB->query($this->sql);
            }
            catch (PDOException $e)
            {
                return false;
            }
            $r = $s->rowCount();
            if($r > 0) $this->total = $r;
            else return false;
            }
            else return false;
        }
}
?>
