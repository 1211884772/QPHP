<?php


abstract class BaseModel implements IModel,IModelBase
{
    protected $db =null;
    public $table='';//数据表
    public $key='';//主键


    //链式操作sql
    protected $database='';//数据库
    protected $where='';
    protected $join=array();
    protected $sql ='';
    protected $asTable='';//表别名
    protected $field= ' * ';
    protected $order= '';
    protected $limit = '';
    protected $echo_sql=false;

    protected $dbType = '';

    private $qdb_factory = 'QDbFactory';
    public function __construct($dbType='mysql')
    {
        $this->dbType = $dbType;
        //$this->db = QDbFactory::getDb($dbkey,$dbType);
    }


    public function __destruct()
    {
        $this->db =null;
        $this->table =null;
        $this->key =null;
        //初始化
        $this->free();
    }

    /**
     * 释放链式操作数据
     * @param $field
     * @return $this
     */
    public function free(){
        //初始化
        $this->join = array();
        $this->where='';
        $this->join=array();
        $this->sql ='';
        $this->asTable='';//表别名
        $this->field= ' * ';
        $this->order= '';
        $this->limit = '';
        $this->table='';
        $this->key='';
        $this->database='';
    }

    /**
     * 数据表名称
     * @param $database
     * @return $this|IModel
     * @throws Exception
     */
    public function Db($database){
        //初始化
        $this->free();
        //表名
        $this->database=$database;
        //TODO: 此处不符合，迪米特法则
        //陌生的类QDbPdoPool最好不要以局部变量的形式出现在类的内部
        $this->db = $this->qdb_factory::getDb($this->database,$this->dbType);
        return $this;
    }

    /**
     * 数据表名称
     * @param $table
     * @return $this
     */
    public function table($table){
        //表名
        $this->table=$table;
        return $this;
    }
    public function asTable($asTable){
        $this->asTable=$asTable;
        return $this;
    }

    public function field($field){
        if(!empty($field)){
            $this->field=$field;
        }
        return $this;
    }

    public function leftJoin($join){
        if(!empty($join)) {
            $this->join[]=" left join {$join} ";
        }
        return $this;
    }
    public function rightJoin($join){
        if(!empty($join)) {
            $this->join[]=" right join {$join} ";
        }
        return $this;
    }

    public function innerJoin($join){
        if(!empty($join)) {
            $this->join[]=" inner join {$join} ";
        }
        return $this;
    }
    public function fullOutterJoin($join){
        if(!empty($join)) {
            $this->join[]=" full outter join {$join} ";
        }
        return $this;
    }


    public function where($where=''){
        if(empty($where)){
            $this->where= ' where 1=1 ';
        }
        else{
            $this->where= ' where 1=1 AND '.$where;
        }

        return $this;
    }

    public function order($order=''){
        if(empty($order)){
            $this->order= '  ';
        }
        else{
            $this->order= ' order by '.$order;
        }
        return $this;
    }
    //获取最近一条sql
    public function getLastSql()
    {
        $this->echo_sql = true;
    }

    /**
     * 查询指定条数
     * $num = 0 起始行号
     * $len =10 长度
     * @return array
     */
    abstract public function limit($num=0,$len=10);
    /**
     * 查询一条
     * @return array
     */
    abstract public function find();

    /**
     * 查询多条
     * @return array
     */
    abstract public function select();

    /**
     * 查询总条数
     * @return array
     */
    abstract public function count();

    /**
     * 插入一条数据
     * @param array $arr
     * @return mixed
     */
    abstract public function insert($arr = array());

    /**
     * 插入多条条数据
     * @param array $arr
     * @return mixed
     */
    abstract public function insertAll($arr = array());

    /**
     * 修改数据
     * @param array $arr
     * @return mixed
     */
    abstract public function update($arr = array());

    /**
     * 删除数据
     * @return mixed
     */
    abstract public function delete();
    /**
     * 执行sql
     * @return array
     */
    public function executeSql($execute_fun,$sql){
        if($this->echo_sql){
            echo $sql;
        }
        return $this->db->$execute_fun($sql);
    }


    abstract public function findAll();

}
