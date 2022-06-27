<?php


class ExceptionError extends Exception implements IExceptionError
{



    //输出异常
    public function printException($MODULE,$exception){

        $errinfo = "Code: " . $exception->getCode() ." Message: ". $exception->getMessage().PHP_EOL;
        $errinfo.= $exception->__toString().PHP_EOL;

        $log = APP_PATH.'application/'.$MODULE.'/Log/'.date('Ym').'/';
        if(!file_exists($log)){
            mkdir($log, 0777,true);
        }
        file_put_contents($log.date('d').'_exception.log',$errinfo,FILE_APPEND);
        //debug是否开启错误显示到浏览器
        if(APP_DEBUG==true){
            die($errinfo);
        }
    }
}

