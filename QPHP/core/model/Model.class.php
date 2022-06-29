<?php

/**
 * Class Model类
 */
class Model implements IModel
{
    public $table='';//数据表
    public $key='';//主键

    protected $dbType='mysql';

    private $interface_model = null;
    private $model_factory =null;
    public function __construct()
    {
        $this->setFactory(new ModelFactory());
    }

    private function setFactory(ModelFactory $modelFactory){
        $this->model_factory = $modelFactory;
        $this->interface_model=$this->model_factory->createModel($this->dbType,$this->table,$this->key);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->interface_model =null;
        $this->model_factory =null;
    }


    /**
     * 数据表名称
     * @param $database
     * @return $this|IModel
     * @throws Exception
     */
    public function Db($database){
        $this->interface_model->Db($database);
        return $this;
    }

    /**
     * 数据表名称
     * @param $table
     * @return $this
     */
    public function table($table){
        $this->interface_model->table($table);
        return $this;
    }
    public function asTable($as_table){
        $this->interface_model->asTable($as_table);
        return $this;
    }

    public function field($field){
        $this->interface_model->field($field);
        return $this;
    }

    public function leftJoin($join){
        $this->interface_model->leftJoin($join);
        return $this;
    }
    public function rightJoin($join){
        $this->interface_model->rightJoin($join);
    }

    public function innerJoin($join){
        $this->interface_model->innerJoin($join);
    }
    public function fullOutterJoin($join){
        $this->interface_model->fullOutterJoin($join);
    }


    public function where($where=''){
        $this->interface_model->where($where);
        return $this;
    }

    public function order($order=''){
        $this->interface_model->order($order);
        return $this;
    }
    //获取最近一条sql
    public function getLastSql()
    {
        $this->interface_model->getLastSql();
    }

    public function limit($num=0,$len=10){
        $this->interface_model->limit($num,$len);
        return $this;
    }


    /**
     * 查询一条
     * @return array
     */
    public function find(){
        return $this->interface_model->find();
    }

    /**
     * 查询多条
     * @return array
     */
    public function select(){
        return $this->interface_model->select();
    }

    /**
     * 查询总条数
     * @return
     */
    public function count(){
        return $this->interface_model->count();
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
        return $this->interface_model->insert($arr);
    }

    /**
     * 插入多条数据
     * @param array $data_arr
     * @return bool
     */
    public function insertAll($data_arr = array()) {
        return $this->interface_model->insertAll($data_arr);
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
        return $this->interface_model->update($arr);
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
        return $this->interface_model->delete();
    }

    /**
     * 执行sql
     * @return array
     */
    public function executeSql($execute_fun,$sql){
        return $this->interface_model->executeSql($execute_fun,$sql);
    }

    public function findAll(){
        return $this->interface_model->findAll();
    }
}
