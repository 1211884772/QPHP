<?php

class QPHP
{
    //框架的运行方法
    public function run(){
        //===================================
        global $RESOURCE;//定义我们的项目资源目录常量
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称

        if(ROUTE_PATH){
            //导入所有配置
            $this->requireConfig();
            $route = Route::instance();


            $route->parsePath();

            $MODULE = $route->module?$route->module:'index';
            $ACTION=$route->action?$route->action:'IndexAction';
            $MOD=$route->mod?$route->mod:'index';

        }elseif (RPC_RUN){
            $_REQUEST['argv_rpc'] = isset($action)?$action:'index/index/index';
            $_arr=explode('/',$_REQUEST['argv_rpc']);
            $MODULE= isset($_arr[0])&&!empty($_arr[0])?strtolower($_arr[0]):'index';
            $ACTION=isset($_arr[1])&&!empty($_arr[1])?$_arr[1].'Action':'IndexAction';
            $MOD=isset($_arr[2])&&!empty($_arr[2])?$_arr[2]:'index';
        }else{
            //PHP $_REQUEST 用于收集HTML表单提交的数据
            $_REQUEST['module'] = $GLOBALS['argv']['1'];
            $_REQUEST['action'] = $GLOBALS['argv']['2'];
            $_REQUEST['mod'] = $GLOBALS['argv']['3'];
            $module = 'index';
            if(isset($_SERVER['REQUEST_URI'])){
                $url = $_SERVER['REQUEST_URI'];
                if(strpos($url,'.php')!=false){
                    $url = preg_replace("/\/\w*.php/","",$url);
                }
                $url = preg_replace("/^\//", "", $url);
                $url = preg_replace("/\/$/", "", $url);
                $url = preg_replace("/\?[\w=]*/", "", $url);
                $_arr=explode('/',$url);
                if(isset($_arr[0])&&!empty($_arr[0])){
                    $module = $_arr[0];
                    if(strpos($module,'?')!==false){
                        $module = preg_replace("/\?[\w=&]*/","",$module);
                    }
                }
                $module = isset($module)&&!empty($module)?strtolower($module):'index';
                if(isset($_arr[1])&&!empty($_arr[1])){
                    $action = $_arr[1];
                    if(strpos($_arr[1],'?')!==false){
                        $action = preg_replace("/\?[\w=&]*/","",$action);
                    }
                }
                $action = isset($action)&&!empty($action)?ucfirst($action).'Action':'IndexAction';
                if(isset($_arr[2])&&!empty($_arr[2])){
                    $mod = $_arr[2];
                    if(strpos($_arr[2],'?')!==false){
                        $mod = preg_replace("/\?[\w=&]*/","",$mod);
                    }
                }
                $mod = isset($mod)&&!empty($mod)?$mod:'index';
            }
            $MODULE= isset($_REQUEST['module'])&&!empty($_REQUEST['module'])?strtolower($_REQUEST['module']):$module;
            $ACTION=isset($_REQUEST['action'])&&!empty($_REQUEST['action'])?$_REQUEST['action'].'Action':$action;
            $MOD=isset($_REQUEST['mod'])&&!empty($_REQUEST['mod'])?$_REQUEST['mod']:$mod;
        }
        $RESOURCE = APP_PATH . 'application/'.$MODULE.'/Resource';
        $gloabal = APP_PATH.'application/'.$MODULE.'/App/Util/include/global.php';
        require_once $gloabal;


        //调用配置文件
        $this->init_config();
        //加载App/util/include
        //加载Action|model|
        spl_autoload_register(array($this,'load'));

        //set_error_handler() 函数设置用户自定义的错误处理函数。
        set_error_handler(array($this,'AppError'));
        //set_exception_handler — 设置用户自定义的异常处理函数
        set_exception_handler(array($this,'AppException'));
        $actionObj = new $ACTION;//UserAction
        $actionObj->call($actionObj,$MOD);
    }


    /**
     * 加载核心配置
     */
    public function requireConfig(){
        $conf = array(
            'ConfigUrl'=>APP_PATH .'config',//配置文件
            'Route'=>Lib.'/core/route/Route.class.php',//路由文件
            'RouteUrl'=>APP_PATH .'route'//前端路由文件
        );
        foreach ($conf as $k=>$v){
            if(is_file($v)){
                require_once $v;
            }else if (is_dir($v)){
                $this->requireDir($v);
            }
        }
    }

    /**
     * 加载目录下所有文件
     * @param $dir
     */
    function requireDir($dir)
    {
        $handle = opendir($dir);//打开文件夹
        while (false !== ($file = readdir($handle))) {//读取文件
            if ($file != '.' && $file != '..') {
                $filepath = $dir . '/' . $file;//文件路径

                if (filetype($filepath) == 'dir') {//如果是文件夹
                    $this->requireDir($filepath);//继续读
                } else {
                    if(is_file($filepath)){
                        require_once ($filepath);//引入文件
                    }
                }
            }
        }
    }

    //加载类
    private function load($className){
        global $MODULE;//模块名称
        $data = self::core_file();
        if(isset($data[$className])){
            $path = $data[$className];
        }elseif (strpos($className,'Util')!=false){
            $_str = str_replace('Util','',$className);
            $_str = ucfirst($_str);
            $path =  APP_PATH."application/".$MODULE."/App/Util/include/{$_str}.util.php";
        }elseif (strpos($className,'Action')!=false){
            $_str = str_replace('Action','',$className);
            $_str = ucfirst($_str);
            $path =  APP_PATH."application/".$MODULE."/App/Action/{$_str}.action.php";
        }elseif (strpos($className,'Validate')!=false){
            $_str = str_replace('Validate','',$className);
            $_str = ucfirst($_str);
            $path =  APP_PATH."application/".$MODULE."/App/Validate/{$_str}.validate.php";
        }elseif (strpos($className,'Model')!=false){
            $_str = str_replace('Model','',$className);
            $_str = ucfirst($_str);
            $path =  APP_PATH."application/".$MODULE."/App/Model/{$_str}.model.php";
        }else{
            throw new Exception("Class not found {$className}");
        }
        require $path;
    }


    //输出错误日志
    public function AppError($errno, $errstr, $errfile, $errline){
        global $MODULE;//模块名称
		$error_obj = new UserError();
        $module = $MODULE;
		$error_obj->printError($module,$errno, $errstr, $errfile, $errline);
        return true;
    }

    //输出异常
    public function AppException($exception){
        global $MODULE;
		$exception_obj = new ExceptionError();
        $module = $MODULE;
		$exception_obj->printException($module,$exception);
		return true;
    }

    //初始化配置文件
    private function init_config(){
        global $MODULE;
        /**
         * 总配置文件
         */
        $config_path = APP_PATH.'config/config.php';
        if(!file_exists($config_path)){
            die('The configuration file does not exist');
        }
        require_once $config_path;
        if(isset($config['admin'])){
            extract($config['admin']);
        }
        $path = APP_PATH.'application/'.$MODULE.'/Config/config.php';
        if(!file_exists($path)){
            die('The configuration file does not exist');
        }
        require_once $path;
        if(isset($config['mysql'])){
            extract($config['mysql']);
            define('MYSQL_HOST',$host);
            define('MYSQL_DB',$dbname);
            define('MYSQL_USER',$mysql_user);
            define('MYSQL_PWD',$mysql_pwd);
            define('MYSQL_PORT',$port);
        }

        if(isset($config['oracle'])){
            extract($config['oracle']);
            define('ORACLE_HOST',$host);
            define('ORACLE_DB',$dbname);
            define('ORACLE_USER',$oracle_user);
            define('ORACLE_PWD',$oracle_pwd);
            define('ORACLE_PORT',$port);
        }

        if(isset($config['mem'])){
            extract($config['mem']);
            define('MEM_HOST',$host);
            define('MEM_PORT',$port);
        }

        if(isset($config['redis'])){
            extract($config['redis']);
            define('REDIS_HOST',$host);
            define('REDIS_PORT',$port);
        }
    }


    /**
     * 动态加载核心文件
     * @return array
     */
    public static function core_file(){
        global $MODULE;
        $_arr = array(
            'UserError'=>Lib.'/core/error/UserError.class.php',
            'ExceptionError'=>Lib.'/core/exception/ExceptionError.class.php',
            'Action'=>Lib.'/core/action/Action.class.php',
            'ActionMiddleware'=>APP_PATH."application/".$MODULE."/App/Util/ActionMiddleware.php",
            'Input'=>Lib.'/core/input/Input.class.php',
            'QDbPdoInterface'=> Lib.'/core/pdo/QDbPdoInterface.interface.php',
            'QDbPdo'=> Lib.'/core/pdo/QDbPdo.class.php',
            'QDbMysql'=> Lib.'/core/pdo/QDbMysql.class.php',
            'QDbOracle'=> Lib.'/core/pdo/QDbOracle.class.php',
            'QDbFactory'=> Lib.'/core/pdo/QDbFactory.class.php',
            'BaseModel'=>Lib.'/core/model/BaseModel.class.php',
            'MysqlModel'=>Lib.'/core/model/MysqlModel.class.php',
            'OracleModel'=>Lib.'/core/model/OracleModel.class.php',
            'Model'=>Lib.'/core/model/Model.class.php',
            'MmCache'=>Lib.'/core/cache/MmCache.class.php',
            'QRedis'=>Lib.'/core/cache/QRedis.class.php'

        );
        return $_arr;
    }
}
