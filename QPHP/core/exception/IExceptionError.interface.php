<?php


interface IExceptionError
{
    /**
     * @param $MODULE 模块名
     * @param $exception 异常对象
     */
    public function printException($MODULE,$exception);
}
