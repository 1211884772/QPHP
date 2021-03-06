<?php

class QPHP
{
    private $user_error = null;
    private $exception_error =null;
    private static $ins=null;

    public static function instance(){
        if(!self::$ins||!(self::$ins instanceof self)){
            self::$ins = new self();
        }
        return self::$ins;
    }


    public function __construct()
    {
        //加载App/util/include
        //加载Action|model|
        spl_autoload_register(array($this,'load'));
        //set_error_handler() 函数设置用户自定义的错误处理函数。
        set_error_handler(array($this,'AppError'));
        //set_exception_handler — 设置用户自定义的异常处理函数
        set_exception_handler(array($this,'AppException'));
        $this->user_error = new UserError();
        $this->exception_error = new ExceptionError();
    }

    //框架的运行方法
    public function run(){
        //===================================
        global $RESOURCE;//定义我们的项目资源目录常量
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
        //导入全局所有配置
        $this->requireConfig(Config::instance());
        /**
         * 总配置文件
         */
        if (!defined('QPHP_CONFIG')){
            throw new Exception("The global configuration file does not exist");
        }
        define('RPC_RUN',isset(QPHP_CONFIG['RPC_RUN'])?QPHP_CONFIG['RPC_RUN']:false);//是否开启rpc
        define('ROUTE_PATH',isset(QPHP_CONFIG['ROUTE_PATH'])?QPHP_CONFIG['ROUTE_PATH']:true);//是否开启路由模式
        define('APP_DEBUG',isset(QPHP_CONFIG['APP_DEBUG'])?QPHP_CONFIG['APP_DEBUG']:true);
        if(ROUTE_PATH){
            //路由请求方式
            $this->routeRequestMode(Route::instance());
        }elseif (RPC_RUN){
            //rpc请求方式
            $this->rpcRunRequestMode();
        }else{
            //默认请求方式
            $this->defaultRequestMode();
        }
        $RESOURCE = APP_PATH . 'application/'.$MODULE.'/Resource';
        //TODO：此处待优化
        $gloabal = APP_PATH.'application/'.$MODULE.'/App/Util/include/global.php';
        require_once $gloabal;
        //调用配置文件
        $this->init_config();
        //调用app控制器方法
        $actionObj = new $ACTION;//UserAction
        $actionObj->call($actionObj,$MOD);
    }
    /**
     * 路由请求方式
     * 加载全局核心路由文件
     * @throws Exception
     */
    private function routeRequestMode(Route $route){
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
        $route->requireRouteFileUrl();
        $route->parsePath();
        $MODULE = $route->module?$route->module:'index';
        $ACTION=$route->action?$route->action:'IndexAction';
        $MOD=$route->mod?$route->mod:'index';
    }
    /**
     * rpc请求方式
     */
    private function rpcRunRequestMode(){
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
        $_REQUEST['argv_rpc'] = isset($action)?$action:'index/index/index';
        $_arr=explode('/',$_REQUEST['argv_rpc']);
        $MODULE= isset($_arr[0])&&!empty($_arr[0])?strtolower($_arr[0]):'index';
        $ACTION=isset($_arr[1])&&!empty($_arr[1])?$_arr[1].'Action':'IndexAction';
        $MOD=isset($_arr[2])&&!empty($_arr[2])?$_arr[2]:'index';
    }
    /**
     * 核心默认请求方式
     */
    private function defaultRequestMode(){
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
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
            if(strpos($url,'?')!==false){
                $url = preg_replace("/\?[\w=&]*/", "", $url);
            }
            $url = preg_replace("/^\//", "", $url);
            $url = preg_replace("/\/$/", "", $url);
            $_arr=explode('/',$url);
            if(isset($_arr[0])&&!empty($_arr[0])){
                $module = $_arr[0];
            }
            if(isset($_arr[1])&&!empty($_arr[1])){
                $action = $_arr[1];
            }
            if(isset($_arr[2])&&!empty($_arr[2])){
                $mod = $_arr[2];
            }
        }
        $module = isset($module)&&!empty($module)?strtolower($module):'index';
        $action = isset($action)&&!empty($action)?ucfirst($action).'Action':'IndexAction';
        $mod = isset($mod)&&!empty($mod)?$mod:'index';
        $MODULE= isset($_REQUEST['module'])&&!empty($_REQUEST['module'])?strtolower($_REQUEST['module']):$module;
        $ACTION=isset($_REQUEST['action'])&&!empty($_REQUEST['action'])?$_REQUEST['action'].'Action':$action;
        $MOD=isset($_REQUEST['mod'])&&!empty($_REQUEST['mod'])?$_REQUEST['mod']:$mod;
    }



    /**
     * 加载全局配置核心文件
     * @throws Exception
     */
    private function requireConfig(Config $conf){
        //导入全局所有配置
        $conf->requireConfigFileUrl(APP_PATH);
    }

    /**
     * 加载模块配置文件
     * 合并全局配置模块配置
     * @param $MODULE
     */
    private function requireConfigModule(Config $conf,$MODULE){
        //加载模块配置文件
        $conf->requireConfigModuleFileUrl(APP_PATH,$MODULE);
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
        require_once $path;
    }


    //输出错误日志
    public function AppError($errno, $errstr, $errfile, $errline){
        global $MODULE;//模块名称
        $module = $MODULE;
        $this->user_error->printError($module,$errno, $errstr, $errfile, $errline);
        return true;
    }

    //输出异常
    public function AppException($exception){
        global $MODULE;
        $module = $MODULE;
        $this->exception_error->printException($module,$exception);
		return true;
    }

    //初始化配置文件
    private function init_config(){
        global $MODULE;
        //项目配置配置文件
        //总配置值和项目配置值的合并
        $this->requireConfigModule(Config::instance(),$MODULE);
        /**
         * 项目配置文件
         */
        $conf = strtoupper('QPHP_CONFIG_'.$MODULE);
        if (!defined($conf)){
            throw new Exception("The module [{$MODULE}] configuration file  does not exist");
        }
        extract(constant($conf));
        $conf_arr =[];
        for ($i=0;$i<100;$i++){
            $db= "mysql_".$i;
            if(isset(${$db})) {
                extract(${$db});
                $conf_arr[$db] =array(
                    'MYSQL_HOST'=>$host,
                    'MYSQL_DB'=>$dbname,
                    'MYSQL_USER'=>$mysql_user,
                    'MYSQL_PWD'=>$mysql_pwd,
                    'MYSQL_PORT'=>$port,
                );
            }
        }
        define('MYSQL_POOL',$conf_arr);
        $conf_arr =[];
        for ($i=0;$i<100;$i++){
            $db= "oracle_".$i;
            if(isset($$db)) {
                extract($$db);
                $conf_arr[$db] =array(
                    'ORACLE_HOST'=>$host,
                    'ORACLE_DB'=>$dbname,
                    'ORACLE_USER'=>$oracle_user,
                    'ORACLE_PWD'=>$oracle_pwd,
                    'ORACLE_PORT'=>$port,
                );
            }
        }
        define('ORACLE_POOL',$conf_arr);
        $conf_arr=[];
        if(isset($mem)){
            extract($mem);
            define('MEM_HOST',$host);
            define('MEM_PORT',$port);
        }
        if(isset($redis)){
            extract($redis);
            define('REDIS_HOST',$host);
            define('REDIS_PORT',$port);
        }
        unset($conf);
    }


    /**
     * 动态加载核心文件
     * @return array
     */
    private static function core_file(){
        global $MODULE;
        $_arr = array(
            'Func'=>Lib.'/core/func/Func.class.php',//公共方法文件
            'Config'=>Lib.'/core/config/Config.class.php',//配置文件
            'Route'=>Lib.'/core/route/Route.class.php',//路由文件
            'IUserError'=>Lib.'/core/error/IUserError.interface.php',
            'UserError'=>Lib.'/core/error/UserError.class.php',
            'IExceptionError'=>Lib.'/core/exception/IExceptionError.interface.php',
            'ExceptionError'=>Lib.'/core/exception/ExceptionError.class.php',
            'Action'=>Lib.'/core/action/Action.class.php',
            'ActionMiddleware'=>APP_PATH."application/".$MODULE."/App/Util/ActionMiddleware.php",
            'Input'=>Lib.'/core/input/Input.class.php',
            'IPdo'=>Lib.'/core/pdo/IPdo.interface.php',
            'QDbPdo'=> Lib.'/core/pdo/QDbPdo.class.php',
            'QDbMysql'=> Lib.'/core/pdo/QDbMysql.class.php',
            'QDbOracle'=> Lib.'/core/pdo/QDbOracle.class.php',
            'IPdoConn'=>Lib.'/core/pdo/IPdoConn.interface.php',
            'QDbPdoOracleConn'=> Lib.'/core/pdo/QDbPdoOracleConn.class.php',
            'QDbPdoMysqlConn'=> Lib.'/core/pdo/QDbPdoMysqlConn.class.php',
            'IPdoPool'=>Lib.'/core/pdo/IPdoPool.interface.php',
            'QDbPdoPool'=> Lib.'/core/pdo/QDbPdoPool.class.php',
            'QDbFactory'=> Lib.'/core/pdo/QDbFactory.class.php',
            'IModel'=>Lib.'/core/model/IModel.interface.php',
            'IModelBase'=>Lib.'/core/model/IModelBase.interface.php',
            'BaseModel'=>Lib.'/core/model/BaseModel.class.php',
            'MysqlM'=>Lib.'/core/model/MysqlM.class.php',
            'OracleM'=>Lib.'/core/model/OracleM.class.php',
            'IModelFactory'=>Lib.'/core/model/IModelFactory.interface.php',
            'ModelFactory'=>Lib.'/core/model/ModelFactory.class.php',
            'Model'=>Lib.'/core/model/Model.class.php',
            'MmCache'=>Lib.'/core/cache/MmCache.class.php',
            'QRedis'=>Lib.'/core/cache/QRedis.class.php',
            'Thread'=>Lib.'/core/thread/Thread.class.php'
        );
        return $_arr;
    }
}
