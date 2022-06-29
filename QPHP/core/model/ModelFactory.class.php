<?php


class ModelFactory implements IModelFactory
{
    public function createModel($dbType,$table,$key){
        if(!empty($dbType)) {
            try{
                $db_type = ucfirst($dbType);
                $model_class = $db_type."M";
                if(class_exists($model_class)){
                    return new $model_class($table,$key);
                }
            }catch (Exception $e){
                throw $e;
            }
            /* if($this->dbType==='mysql'){
                 $this->interface_model=new MysqlModel($this->table,$this->key);
             }elseif ($this->dbType==='oracle') {
                 $this->interface_model=new OracleModel($this->table,$this->key);
             }else{
                 throw new Exception("Model type error");
             }*/
            return null;
        }else{
            throw new Exception("The model type is empty");
        }
    }
}
