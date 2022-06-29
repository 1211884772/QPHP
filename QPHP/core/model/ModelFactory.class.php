<?php


class ModelFactory implements IModelFactory
{
    public function createModel($dbType,$table,$key){
        if(!empty($dbType)) {
            try{
                if($dbType==='mysql'){
                    $model=new MysqlM($table,$key);
                }elseif ($this->dbType==='oracle') {
                    $model=new OracleM($table,$key);
                }else{
                    throw new Exception("Model type error");
                }
            }catch (Exception $e){
                throw $e;
            }
            return $model;
        }else{
            throw new Exception("The model type is empty");
        }
    }
}
