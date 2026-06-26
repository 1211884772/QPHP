<?php
namespace QPHP\core\model;

use QPHP\core\cache\redis\QRedis;
use QPHP\core\container\Container;
use QPHP\core\model\intf\IModel;
use QPHP\core\model\intf\IModelFactory;
use QPHP\core\model\oracle\OracleM;
use QPHP\core\model\mysql\MysqlM;

/**
 * Class Model类
 */
class Model implements IModel
{
    protected string $table='';//数据表
    protected string $key='';//主键

    protected string $dbType='mysql';

    private OracleM|MysqlM|null $interface_model = null;
    private IModelFactory $factory;

    public function __construct()
    {
        // 仅注入工厂，不创建数据库实例
        $modelFactory = Container::getInstance()->make(ModelFactory::class);
        $this->factory = $modelFactory;
        $this->getInstance();

    }

    /**
     * 懒加载：真正实例化底层数据库模型
     * @throws Exception
     */
    protected function getInstance(): OracleM|MysqlM
    {
        if ($this->interface_model === null) {
            $this->interface_model = $this->factory->createModel(
                $this->dbType,
                $this->table,
                $this->key
            );
        }
        return $this->interface_model;
    }
//    public function __construct()
//    {
//        $modelFactory=Container::getInstance()->make(ModelFactory::class);
//        //$this->setFactory(new ModelFactory());
//        $this->setFactory($modelFactory);
//    }
//
//    private function setFactory(IModelFactory $modelFactory):void
//    {
//
//        // 从容器取出工厂实例，调用方法
//        $this->interface_model= $modelFactory->createModel($this->dbType,$this->table,$this->key);
//    }

    public function __destruct()
    {
        $this->interface_model=null;
    }

    /**
     * 数据表名称
     * @param $database
     * @return IModel
     * @throws Exception
     */
    public function Db($database):Model
    {
        $this->interface_model->Db($database);
        return $this;
    }

    /**
     * 数据表名称
     * @param $table
     * @return $this
     */
    public function table($table):Model
    {
        $this->interface_model->table($table);
        return $this;
    }
    public function asTable($asTable):Model
    {
        $this->interface_model->asTable($asTable);
        return $this;
    }

    public function field($field):Model
    {
        $this->interface_model->field($field);
        return $this;
    }

    public function leftJoin($join):Model
    {
        $this->interface_model->leftJoin($join);
        return $this;
    }
    public function rightJoin($join):Model
    {
        $this->interface_model->rightJoin($join);
        return $this;
    }

    public function innerJoin($join):Model
    {
        $this->interface_model->innerJoin($join);
        return $this;
    }
    public function fullOutterJoin($join):Model
    {
        $this->interface_model->fullOutterJoin($join);
        return $this;
    }


    public function where(string $where=''):Model
    {
        $this->interface_model->where($where);
        return $this;
    }

    public function order(string $order=''):Model
    {
        $this->interface_model->order($order);
        return $this;
    }

    public function limit($num=0,$len=10):Model
    {
        $this->interface_model->limit($num,$len);
        return $this;
    }

    //获取最近一条sql
    public function getLastSql(): void
    {
        $this->interface_model->getLastSql();
    }


    /**
     * 查询一条
     * @return array
     */
    public function find(): array
    {
        return $this->interface_model->find();
    }

    /**
     * 查询多条
     * @return array
     */
    public function select(): array
    {
        return $this->interface_model->select();
    }

    /**
     * 查询总条数
     * @return
     */
    public function count(): array
    {
        return $this->interface_model->count();
    }


    /**
    * +----------------------------------------------------------
     * 添加数据(辅助方法)
    * +----------------------------------------------------------
     * @access public
    * +----------------------------------------------------------
     * @param string  $table  表名
    * +----------------------------------------------------------
     * @param array $arr 插入的数据(键值对)
    * +----------------------------------------------------------
     * @return mixed
    * +----------------------------------------------------------
     */
    public function insert(array $arr = array()):int
    {
        return $this->interface_model->insert($arr);
    }

    /**
     * 插入多条数据
     * @param array $data_arr
     * @return bool
     */
    public function insertAll(array $arr = array()):int
    {
        return $this->interface_model->insertAll($arr);
    }
    /**
    * +----------------------------------------------------------
     * 更新数据(辅助方法)
    * +----------------------------------------------------------
     * @access public
    * +----------------------------------------------------------
     * @param string  $table  表名
    * +----------------------------------------------------------
     * @param array $arr 更新的数据(键值对)
    * +----------------------------------------------------------
     * @param mixed   $where  条件
    * +----------------------------------------------------------
     * @return mixed
    * +----------------------------------------------------------
     */
    public function update(array $arr = array()):int
    {
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
    public function delete():int
    {
        return $this->interface_model->delete();
    }

    /**
     * 执行sql
     * @return array
     */
    public function executeSql($method,$sql)
    {

        return $this->interface_model->executeSql($method,$sql);
    }

    public function findAll(): mixed
    {
        return $this->interface_model->findAll();
    }

    public function startTrans()
    {
        return $this->interface_model->startTrans();
    }
    public function commit()
    {
        return $this->interface_model->commit();
    }
    public function rollback()
    {
        return $this->interface_model->rollback();
    }

}
