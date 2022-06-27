<?php


interface IUserError
{

    /**
     * @param $MODULE 模块名称
     * @param $errno 错误号
     * @param $errstr 错误字符
     * @param $errfile 错误文件
     * @param $errline 错误行
     * @return mixed
     */
    public function printError($MODULE,$errno, $errstr, $errfile, $errline);
}
