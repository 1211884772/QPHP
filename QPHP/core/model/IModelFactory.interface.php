<?php


interface IModelFactory
{
    /**
     * 创建数据库模型
     * @param $dbType 数据库类型类
     * @param $table 表
     * @param $key  表主键
     * @return mixed
     */
    public function createModel($dbType,$table,$key);
}
