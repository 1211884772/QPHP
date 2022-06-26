<?php


class OracleModel extends BaseModel
{
    public function __construct($table,$key)
    {
        $this->table=$table;
        $this->key=$key;
        parent::__construct('oracle');
    }
    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this->$name=$value;
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        return $this->$name;
    }

    public function limit($num=0,$len=10){
        $end_num = $len+$num;
        $this->limit = " where \"rn\"<={$end_num} and \"rn\" >={$num} ";
        return $this;
    }
    /**
     * 查询一条
     * @return array
     */
    public function find(){
        $join = '';
        if(!empty($this->join)){
            foreach ($this->join as $v){
                $join .= " {$v} ";
            }
        }
        $this->sql = "select {$this->field} from {$this->table}  {$this->asTable} {$join} {$this->where}";
        $sel_find =  $this->executeSql("getRow",$this->sql);
        //初始化
        $this->free();
        return $sel_find;
    }

    /**
     * 查询多条
     * @return array
     */
    public function select(){
        $join = '';
        if(!empty($this->join)){
            foreach ($this->join as $v){
                $join .= " {$v} ";
            }
        }
        //$this->sql = "select {$this->field} from {$this->table} {$this->asTable} {$join} {$this->where}";
        $this->sql = "select * from (select ROWNUM \"rn\",{$this->field} from {$this->table} {$this->asTable} {$join} {$this->where}{$this->order}){$this->limit}";
        $sel =  $this->executeSql("getRows",$this->sql);
        //初始化
        $this->free();
        return $sel;
    }

    /**
     * 查询总条数
     * @return
     */
    public function count(){
        $join = '';
        if(!empty($this->join)){
            foreach ($this->join as $v){
                $join .= " {$v} ";
            }
        }
        $this->sql = "select count(*) \"qphp_count\" from (select {$this->field} from {$this->table} {$this->asTable} {$join}  {$this->where}) qphp_table";
        $sel_count =  $this->executeSql("getRow",$this->sql);
        //初始化
        $this->free();
        return $sel_count;
    }

    public function findAll(){
        $this->sql  = "select * from \"{$this->table}\"";
        $sel_all= $this->executeSql("getRows",$this->sql);
        //初始化
        $this->free();
        return $sel_all;
    }



    /**
    +----------------------------------------------------------
     * 添加数据(辅助方法)
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string  $table  表名
    +----------------------------------------------------------
     * @param array   $arr    插入的数据(键值对)
    +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function insert($arr = array()) {
        $field = $value = "";
        if (!empty($arr) && is_array($arr)) {
            foreach ($arr as $k => $v) {
                $v = preg_replace("/'/", "\\'", $v);
                $field .= "\"$k\",";
                $value .= "'$v',";
            }
            $field = preg_replace("/,$/", "", $field);
            $value = preg_replace("/,$/", "", $value);
            $sql = "INSERT INTO {$this->table} ($field) VALUES ($value)";
            $this->sql=$sql;
            $add = $this->executeSql("insert",$this->sql);
            //初始化
            $this->free();
            if($add){
                return $this->db->getLastInsertId();//添加的id
            }
            return 0;
        }
    }

    /**
     * 插入多条数据
     * @param $table
     * @param array $arr
     * @return bool
     */
    public function insertAll($data_arr = array()) {
        $field = $value = "";
        if (!empty($data_arr) && is_array($data_arr)) {
            foreach ($data_arr as $key => $arr_val){
                $field = '';
                foreach ($arr_val as $k => $v) {
                    $v = preg_replace("/'/", "\\'", $v);
                    $field .= "\"$k\",";
                    $value .= "'$v',";
                }
                $value = preg_replace("/,$/", "", $value);
                $value .= "($value),";
            }

            $field = preg_replace("/,$/", "", $field);
            $value = preg_replace("/,$/", "", $value);
            $sql = "INSERT INTO {$this->table} ($field) VALUES $value";
            $this->sql=$sql;
            $add_all= $this->executeSql("insertAll",$this->sql);
            //初始化
            $this->free();
            return $add_all;
        }
    }


    /**
    +----------------------------------------------------------
     * 更新数据(辅助方法)
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string  $table  表名
    +----------------------------------------------------------
     * @param array   $arr    更新的数据(键值对)
    +----------------------------------------------------------
     * @param mixed   $where  条件
    +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function update($arr = array()) {
        $field = "";
        $loop = 1;
        $len = count($arr);
        $sql = "UPDATE {$this->table} SET ";
        foreach ($arr as $k => $v) {
            $v = preg_replace("/'/", "\\'", $v);
            $field .= "\"".$k."\"" . "='" . $v . "',";
        }
        $sql .= trim($field, ',');
        if(!empty($this->where)){
            $sql .= ' '.$this->where;
        }else{
            return false;
        }
        $this->sql=$sql;
        $edit= $this->executeSql("update",$this->sql);
        //初始化
        $this->free();
        return $edit;
    }

    /**
    +----------------------------------------------------------
     * 删除数据(辅助方法)
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string  $table  表名
    +----------------------------------------------------------
     * @param mixed   $where  条件
    +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function delete() {
        $sql = "delete from {$this->table} ";
        if (!empty($this->where)) {
            if(!empty($this->where)){
                $sql .= ' '.$this->where;
            }else{
                return false;
            }
            $this->sql=$sql;
            $del =  $this->executeSql("delete",$this->sql);
            //初始化
            $this->free();
            return $del;
        }
    }
}
