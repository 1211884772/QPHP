<?php
namespace QPHP\core\model;

use Exception;
use QPHP\core\model\intf\IModelFactory;
use QPHP\core\model\mysql\MysqlM;
use QPHP\core\model\oracle\OracleM;
use QPHP\core\container\Container;

class ModelFactory implements IModelFactory
{
    /**
     * @throws Exception
     */
    public function createModel($dbType, $table, $key):MysqlM|OracleM
    {
        if(!empty($dbType)) {
            $className="QPHP\core\model\\".strtolower($dbType)."\\".ucfirst($dbType)."M";
            //$model=$className::newClass($dbType,$table,$key);
            //return call_user_func_array([$className, "newClass"], [$dbType,$table,$key]);
            return Container::getInstance()->makeByFactory($className,"newClass" ,[$dbType, $table, $key]);
        }else{
            throw new Exception("The model type is empty");
        }
    }
}
