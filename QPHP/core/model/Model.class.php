<?php

/**
 * Class Model类
 */
class Model
{
    public $table='';//数据表
    public $key='';//主键

    protected $dbType='mysql';
    protected $model=null;
    //protected $dbKey='mysql_0';
    public function __construct()
    {
        if(!empty($this->dbType)) {
            if($this->dbType==='mysql'){
                $this->model= new MysqlModel($this->table,$this->key);
            }elseif ($this->dbType==='oracle') {
                $this->model= new OracleModel($this->table,$this->key);
            }else{
                throw new Exception("Model type error");
            }
        }else{
            throw new Exception("The model type is empty");
        }
    }


}
