<?php


class UserError implements IUserError
{

	public function printError($MODULE,$errno, $errstr, $errfile, $errline){
	// $errstr may need to be escaped:
        $errstr = htmlspecialchars($errstr);
        $errinfo = '';
        switch ($errno) {
            case E_USER_ERROR:
                $errinfo.=  "<b>My ERROR</b> [$errno] $errstr".PHP_EOL;
                $errinfo.=  "Fatal error on line $errline in file $errfile".PHP_EOL;
                $errinfo.=  ", PHP " . PHP_VERSION . " (" . PHP_OS . ")".PHP_EOL;
                $errinfo.=  "Aborting...".PHP_EOL;
                break;

            case E_USER_WARNING:
                $errinfo.=  "<b>My WARNING</b> [$errno] $errstr".PHP_EOL;
                $errinfo.=  "Warning on line $errline in file $errfile".PHP_EOL;
                break;

            case E_USER_NOTICE:
                $errinfo.=  "<b>My NOTICE</b> [$errno] $errstr".PHP_EOL;
                $errinfo.=  "Notice on line $errline in file $errfile".PHP_EOL;
                break;

            default:
                $errinfo.=  "Unknown error type: [$errno] $errstr".PHP_EOL;
                $errinfo.=  "Unknown error on line $errline in file $errfile".PHP_EOL;
                break;
        }

        $errinfo .="Request the address: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}".PHP_EOL;
        $errinfo .="The wrong time: ".date('Y-m-d H:i:s').PHP_EOL;
        $log = APP_PATH.'application/'.$MODULE.'/Log/'.date('Ym').'/';
        if(!file_exists($log)){
            mkdir($log, 0777,true);
        }
        file_put_contents($log.date('d').'.log',$errinfo,FILE_APPEND);

        //debug是否开启错误显示到浏览器
        if(APP_DEBUG==true){
            die($errinfo);
        }

        switch ($errno) {
            case E_USER_ERROR:
                exit(1);
        }
        /* Don't execute PHP internal error handler */
        return true;
	}

}
