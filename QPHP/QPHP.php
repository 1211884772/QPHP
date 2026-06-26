<?php
namespace QPHP;

use Exception;
use QPHP\core\cache\memcache\MmCache;
use QPHP\core\cache\redis\QRedis;
use QPHP\core\config\Config;
use QPHP\core\lang\Lang;
use QPHP\core\logger\error\UserError;
use QPHP\core\logger\exception\ExceptionError;
use QPHP\core\model\intf\IModelFactory;
use QPHP\core\model\ModelFactory;
use QPHP\core\route\Route;
use QPHP\core\container\Container;

class QPHP
{
    private $user_error = null;
    private $exception_error =null;
    private static $ins=null;

    public static function instance(): ?QPHP
    {
        if(!self::$ins||!(self::$ins instanceof self)){
            self::$ins = new self();
        }
        return self::$ins;
    }


    private function __construct()
    {
        //加载App/util/lib
        //加载Action|model|
        spl_autoload_register(array($this,'preLoad'));
        spl_autoload_register(array($this,'load'));
        //set_error_handler() 函数设置用户自定义的错误处理函数。
        set_error_handler(array($this,'AppError'));
        //set_exception_handler — 设置用户自定义的异常处理函数
        set_exception_handler(array($this,'AppException'));
        // 1. 获取全局容器实例
        $container = $this->initContainer();

        // 2. 绑定全局单例服务（工厂、日志）
        $this->user_error =$container->make(UserError::class);
        $this->exception_error=$container->make(ExceptionError::class);

    }

    /**
     * 初始化DI容器
     * @return Container
     */
    private function initContainer(): Container
    {
        // 1. 获取全局容器实例
        $container = Container::getInstance();
        // 2. 绑定全局单例服务（工厂、日志）
        $container->singleton(UserError::class, UserError::class);
        $container->singleton(ExceptionError::class, ExceptionError::class);
        $container->singleton(QRedis::class, QRedis::class);
        $container->singleton(MmCache::class, MmCache::class);
        $container->singleton(IModelFactory::class, ModelFactory::class);

        return $container;
    }

    //框架的运行方法

    /**
     * @throws Exception
     */
    public function run(): void
    {
        //===================================
        global $RESOURCE;//定义我们的项目资源目录常量
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
        global $MIDDLEWARE;//中间件数组

        //导入全局所有配置
        try {
            $this->requireConfig(Config::instance());
        } catch (Exception $e) {
            throw new Exception("Get the global configuration error");
        }
        /**
         * 总配置文件
         */
        if (!defined('QPHP_CONFIG')){
            throw new Exception("The global configuration file does not exist");
        }
        define('RPC_RUN',QPHP_CONFIG['RPC_RUN'] ?? false);//是否开启rpc
        define('ROUTE_PATH',QPHP_CONFIG['ROUTE_PATH'] ?? true);//是否开启路由模式
        define('APP_DEBUG',QPHP_CONFIG['APP_DEBUG'] ?? false);
        //var_dump(QPHP_CONFIG);
       // define('APP_LANG',QPHP_CONFIG['APP_LANG'] ?? false);
        if(ROUTE_PATH){
            //路由请求方式
            try {
                $this->routeRequestMode(Route::instance());
            } catch (Exception $e) {
                throw new Exception("Route request mode error");
            }

            if(empty($MODULE)||empty($ACTION)||empty($MOD)){
                //默认请求方式
                //var_dump($ACTION);
                $this->defaultRequestMode();
            }
        }elseif (RPC_RUN){
            //rpc请求方式
            $this->rpcRunRequestMode();
        }else{
            //默认请求方式
            $this->defaultRequestMode();
        }
        $RESOURCE = APP_PATH . 'application/'.$MODULE.'/Resource';
        //调用配置文件
        try {
            $this->initConfig();
        } catch (Exception $e) {
            throw new Exception("Get configuration error");
        }

        //调用中间件方法
        //var_dump($MIDDLEWARE);
        self::runMiddleware($MIDDLEWARE);
        //调用app控制器方法
        $action=$MODULE.'\\Action\\'.$ACTION;
        //var_dump($action) ;
        //var_dump ($MOD);
        // $actionObj = new $action;//UserAction

        // $actionObj->call($actionObj,$MOD);
        // 1. 获取全局容器实例
        $container = Container::getInstance();

        // 2. 绑定全局单例服务（工厂、日志）
//        $container->singleton(UserError::class, UserError::class);
//        $container->singleton(ExceptionError::class, ExceptionError::class);

//        $container->singleton(MysqlFactory::class, MysqlFactory::class);
//        $container->singleton(OracleFactory::class, OracleFactory::class);
//        $container->singleton(Log::class, Log::class);
//        $container->singleton(ExceptionLog::class, ExceptionLog::class);

        // ========== 核心：容器创建控制器实例，自动注入所有构造依赖 ==========
        // 不需要手动 new $ctrlClass，容器自动解析构造函数参数
        $controller = $container->make($action);
        // 3. 调用控制器方法执行业务
        //$controller->$MOD();
        $controller->call($controller,$MOD);
        //删除允许跨域
        Route::instance()->prohibitCrossDomain();
    }

     private static function runMiddleware(array $middlewareClassName){
        if(count($middlewareClassName)>0){
            $middleObj=call_user_func_array(array("QPHP\core\middleware\implement\Middleware", "newClass"), array());
            $middleObj->add($middlewareClassName);
            $middleObj->run($middleObj->input);
        }
     }

    /**
     * 获取语言配置，默认en
     * @return string
     */
    private static function getRequestLang(): string
    {
       return isset($_REQUEST['lang'])?trim($_REQUEST['lang']):'en';
    }
    /**
     * 路由请求方式
     * 加载全局核心路由文件
     * @throws Exception
     */
    private function routeRequestMode(Route $route): void
    {
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
        global $MIDDLEWARE;//中间件数组
        global $LANG;//语言
        $route->requireRouteFileUrl();
        $route->parsePath();
        $MODULE = $route->module??'';
        $ACTION=$route->action??'';
        $MOD=$route->mod??'';
        $MIDDLEWARE=$route->middleware??[];
        $LANG=self::getRequestLang();
    }
    /**
     * rpc请求方式
     */
    private function rpcRunRequestMode(): void
    {
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
        global $MIDDLEWARE;//中间件数组
        global $LANG;//语言
        $_REQUEST['argv_rpc'] = isset($action)?$action:'index/index/index';
        $_arr=explode('/',$_REQUEST['argv_rpc']);
        $MODULE= isset($_arr[0])&&!empty($_arr[0])?strtolower($_arr[0]):'index';
        $ACTION=isset($_arr[1])&&!empty($_arr[1])?$_arr[1].'Action':'IndexAction';
        $MOD=isset($_arr[2])&&!empty($_arr[2])?$_arr[2]:'index';
        $LANG=self::getRequestLang();
    }

    /**
     * 核心默认请求方式
     */
    private function defaultRequestMode(): void
    {
        global $MODULE;//模块名称
        global $ACTION;//控制器名称
        global $MOD;//方法名称
        global $MIDDLEWARE;//中间件数组
        global $LANG;//方法名称
        //PHP $_REQUEST 用于收集HTML表单提交的数据
        $_REQUEST['module'] = $GLOBALS['argv']['1'] ?? '';
        $_REQUEST['action'] = $GLOBALS['argv']['2'] ?? '';
        $_REQUEST['mod'] = $GLOBALS['argv']['3'] ?? '';

        //var_dump( $_REQUEST['lang']);
        $module = 'index';
        if(isset($_SERVER['REQUEST_URI'])){
            $url = $_SERVER['REQUEST_URI'];
            if(strpos($url, '.php')){
                $url = preg_replace("/\/\w*.php/","",$url);
            }
            if(strpos($url, '?')){
                //$url = preg_replace("/\?[\w=&]*/", "", $url);
                preg_match('/^([^?]*).*$/', $url, $matches);
                $url = $matches[1];
            }
            $url = preg_replace("/^\//", "", $url);
            $url = preg_replace("/\/$/", "", $url);
            $_arr=explode('/',$url);


            if(!empty($_arr[0])){
                $module = $_arr[0];
            }
            if(!empty($_arr[1])){
                $action = $_arr[1];
            }
            if(!empty($_arr[2])){
                $mod = $_arr[2];
            }
        }
        $module = !empty($module) ?strtolower($module):'index';
        $action = !empty($action) ?ucfirst($action).'Action':'IndexAction';
        $mod = !empty($mod) ?$mod:'index';
        $MODULE= !empty($_REQUEST['module']) ?strtolower($_REQUEST['module']):$module;
        $ACTION= !empty($_REQUEST['action']) ?$_REQUEST['action'].'Action':$action;
        $MOD= !empty($_REQUEST['mod']) ?$_REQUEST['mod']:$mod;
        $LANG=self::getRequestLang();
    }


    /**
     * 加载全局配置核心文件
     * @throws Exception
     */
    private function requireConfig(Config $conf):void
    {
        //导入全局所有配置
        $conf->requireConfigFileUrl(APP_PATH);
    }

    /**
     * 加载模块配置文件
     * 合并全局配置模块配置
     * @param $MODULE
     */
    private function requireConfigModule(Config $conf,$MODULE):void
    {
        //加载模块配置文件
        $conf->requireConfigModuleFileUrl(APP_PATH,$MODULE);
    }

    //加载类
    private function preLoad($className): void
    {
        //var_dump($className);
        $coreData = self::coreFile();
        $loggerData = self::loggerCoreFile();
        $inputData = self::inputCoreFile();
        $middlewareData = self::middlewareCoreFile();
       // $poolPdoData = self::poolPdoCoreFile();
//        $mysqlData = self::mysqlCoreFile();
//        $oracleData = self::oracleCoreFile();
//        $redisData = self::redisCoreFile();
//        $memcacheData = self::memcacheCoreFile();
//        $languageData = self::languageCoreFile();
        //$data=array_merge($coreData,$loggerData,$inputData,$middlewareData,$mysqlData,$oracleData,$redisData,$memcacheData,$languageData);
       $data=array_merge($coreData,$loggerData,$inputData,$middlewareData);
        //加入命名空间后$className=QPHP\core\error\UserError
        $classNameArr=explode(DIRECTORY_SEPARATOR,$className);
        $className= $classNameArr[count($classNameArr)-1];
        //var_dump($className);
        $path='';
        if(isset($data[$className])){
            $path = $data[$className];
        }else{
            return;
        }
        require_once $path;
    }
    //加载类
    private function load($className): void
    {
        //var_dump(QPHP_CONFIG['MODULE']);
        $mysqlData=array();
        $oracleData=array();
        $redisData=array();
        $memcacheData=array();
        $languageData=array();
        if (QPHP_CONFIG['MODULE']['MYSQL_OPEN']){
            $mysqlData = self::mysqlCoreFile();
        }
        if (QPHP_CONFIG['MODULE']['ORACLE_OPEN']){
            $oracleData = self::oracleCoreFile();
        }
        if (QPHP_CONFIG['MODULE']['REDIS_OPEN']){
            $redisData = self::redisCoreFile();
        }
        if (QPHP_CONFIG['MODULE']['MEMCACHE_OPEN']){
            $memcacheData = self::memcacheCoreFile();
        }
        if (QPHP_CONFIG['MODULE']['LANGUAGE_OPEN']){
            $languageData = self::languageCoreFile();
        }
        //$data=array_merge($coreData,$loggerData,$inputData,$middlewareData,$mysqlData,$oracleData,$redisData,$memcacheData,$languageData);
        $data=array_merge($mysqlData,$oracleData,$redisData,$memcacheData,$languageData);
        //加入命名空间后$className=QPHP\core\error\UserError
        $classNameArr=explode(DIRECTORY_SEPARATOR,$className);
        $className= $classNameArr[count($classNameArr)-1];
        //var_dump($className);
        $path='';
        if(isset($data[$className])){
            $path = $data[$className];
        }elseif (strpos($className, 'Util')){
            $path = $this->appUtilInclude($className);
        }elseif (strpos($className, 'Middleware')){
            //var_dump($className);
            $path = $this->appMiddleware($className);
            //var_dump($path);
        }elseif (strpos($className, 'Action')){
            $path = $this->appAction($className);
            //var_dump($path);
        }elseif (strpos($className, 'Validate')){
            $path = $this->appValidate($className);
        }elseif (strpos($className, 'Model')){
            $path =  $this->appModel($className);
        }else{
            return;
        }
        require_once $path;
    }
    //加载App/Lang
    private function appLang(): void
    {
        global $MODULE;//模块名称
        global $LANG;
        //var_dump($LANG);
        //var_dump($MODULE);
        if ($LANG && $MODULE){
            Lang::getLang($LANG, $MODULE);
         }
    }
    //加载App/Util/lib
    private function appUtilInclude($className): string
    {
        global $MODULE;//模块名称
        $_str = str_replace('Util','',$className);
        $_str = ucfirst($_str);
        return APP_PATH."application/".$MODULE."/App/Util/lib/{$_str}.util.php";
    }
    //加载App/Middleware
    private function appMiddleware($className): string
    {
        global $MODULE;//模块名称
        $_str = str_replace('Middleware','',$className);
        $_str = ucfirst($_str);
        return  APP_PATH."application/".$MODULE."/App/Middleware/{$_str}.middleware.php";
    }

    //加载App/Action
    private function appAction($className): string
    {
        global $MODULE;//模块名称
        $_str = str_replace('Action','',$className);
        $_str = ucfirst($_str);
        return  APP_PATH."application/".$MODULE."/App/Action/{$_str}.action.php";
    }

    //加载App/Validate
    private function appValidate($className): string
    {
        global $MODULE;//模块名称
        $_str = str_replace('Validate','',$className);
        $_str = ucfirst($_str);
        return APP_PATH."application/".$MODULE."/App/Validate/{$_str}.validate.php";
    }

    //加载App/Model
    public function appModel($className): string
    {
        global $MODULE;//模块名称
        $_str = str_replace('Model','',$className);
        $_str = ucfirst($_str);
        return APP_PATH."application/".$MODULE."/App/Model/{$_str}.model.php";
    }


    //输出错误日志
    public function AppError($errno, $errstr, $errfile, $errline): bool
    {
        global $MODULE;//模块名称
        $module = $MODULE;
        $this->user_error->printError($module,$errno, $errstr, $errfile, $errline);
        return true;
    }

    //输出异常
    public function AppException($exception): bool
    {
        global $MODULE;
        $module = $MODULE;
        $this->exception_error->printException($module,$exception);
		return true;
    }

    //初始化配置文件
    private function initConfig(){
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
        //获取语言配置文件
        if(QPHP_CONFIG['MODULE']['LANGUAGE_OPEN']){
            $this->appLang();
        }

        //定义mysql常量配置
        $this->defineMysqlPool($conf);
        //定义oracle常量配置
        $this->defineOraclePool($conf);
        //定义mem常量配置
        $this->defineMemPool($conf);
        //定义redis常量配置
        $this->defineRedisPool($conf);
        unset($conf);
    }

    //生成MYSQL_POOL常量配置
    private function defineMysqlPool($conf): void
    {
        extract(constant($conf));
        unset($conf);
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
        unset($conf_arr);
    }

    //生成ORACLE_POOL常量配置
    private function defineOraclePool($conf): void
    {
        extract(constant($conf));
        unset($conf);
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
        unset($conf_arr);
    }

    //生成MEM_POOL常量配置
    private function defineMemPool($conf): void
    {
        extract(constant($conf));
        unset($conf);
        $conf_arr=[];
        for ($i=0;$i<100;$i++){
            $db= "mem_".$i;
            if(isset($$db)) {
                extract($$db);
                $conf_arr[$db] =array(
                    'MEM_HOST'=>$host,
                    'MEM_PORT'=>$port,
                );
            }
        }
        define('MEM_POOL',$conf_arr);
        unset($conf_arr);
    }

    //生成REDIS_POOL常量配置
    private function defineRedisPool($conf): void
    {
        extract(constant($conf));
        unset($conf);
        $conf_arr=[];
        for ($i=0;$i<100;$i++){
            $db= "redis_".$i;
            if(isset($$db)) {
                extract($$db);
                $conf_arr[$db] =array(
                    'REDIS_HOST'=>$host,
                    'REDIS_PORT'=>$port,
                );
            }
        }
        define('REDIS_POOL',$conf_arr);
        unset($conf_arr);
    }

    /**
     * 动态加载核心文件
     * @return array
     */
    private static function coreFile(): array
    {
        return array(
            'Func'=>Lib.'/core/func/Func.class.php',//公共方法文件
            'Config'=>Lib.'/core/config/Config.class.php',//配置文件
            'Route'=>Lib.'/core/route/Route.class.php',//路由文件
            'Container'=>Lib.'/core/container/Container.class.php',//DI容器

//            'IUserError'=>Lib.'/core/error/IUserError.interface.php',
//            'UserError'=>Lib.'/core/error/UserError.class.php',
//            'IExceptionError'=>Lib.'/core/exception/IExceptionError.interface.php',
//            'ExceptionError'=>Lib.'/core/exception/ExceptionError.class.php',
//            'Input'=>Lib.'/core/input/Input.class.php',
//            'Action'=>Lib.'/core/action/Action.class.php'
//            'IPdo'=>Lib.'/core/pdo/intf/IPdo.interface.php',
//            'QDbPdo'=> Lib.'/core/pdo/abs/QDbPdo.class.php',
//            'QDbMysql'=> Lib.'/core/pdo/mysql/QDbMysql.class.php',
//            'QDbOracle'=> Lib.'/core/pdo/oracle/QDbOracle.class.php',
//            'IPdoConn'=>Lib.'/core/pdo/intf/IPdoConn.interface.php',
//            'QDbPdoOracleConn'=> Lib.'/core/pdo/oracle/QDbPdoOracleConn.class.php',
//            'QDbPdoMysqlConn'=> Lib.'/core/pdo/mysql/QDbPdoMysqlConn.class.php',
//            'IPdoPool'=>Lib.'/core/pdo/intf/IPdoPool.interface.php',
//            'QDBConf'=>Lib.'/core/pdo/conf/QDbConf.class.php',
//            'QDBPdoMysqlPool'=> Lib.'/core/pdo/mysql/QDbPdoMysqlPool.class.php',
//            'QDBPdoOraclePool'=> Lib.'/core/pdo/oracle/QDbPdoOraclePool.class.php',
//            'QDBPdoPoolFactory'=> Lib.'/core/pdo/QDbPdoPoolFactory.class.php',
//            'QDbFactory'=> Lib.'/core/pdo/QDbFactory.class.php',
//            'IModel'=>Lib.'/core/model/intf/IModel.interface.php',
//            'IModelBase'=>Lib.'/core/model/intf/IModelBase.interface.php',
//            'BaseModel'=>Lib.'/core/model/abs/BaseModel.class.php',
//            'MysqlM'=>Lib.'/core/model/mysql/MysqlM.class.php',
//            'OracleM'=>Lib.'/core/model/oracle/OracleM.class.php',
//            'IModelFactory'=>Lib.'/core/model/intf/IModelFactory.interface.php',
//            'ModelFactory'=>Lib.'/core/model/ModelFactory.class.php',
//            'Model'=>Lib.'/core/model/Model.class.php',
//            'MmCache'=>Lib.'/core/cache/memcache/MmCache.class.php',
//            'QRedis'=>Lib.'/core/cache/redis/QRedis.class.php',
//            'M'=>Lib.'/core/cache/memcache/M.class.php',
//            'R'=>Lib.'/core/cache/redis/R.class.php',
//            'Lang'=>Lib.'/core/Lang/Lang.class.php'
        );
    }

    /**
     * 日志核心文件
     * @return string[]
     */
    private static function loggerCoreFile(): array
    {
        return array(
            'IUserError'=>Lib.'/core/logger/intf/IUserError.interface.php',
            'IExceptionError'=>Lib.'/core/logger/intf/IExceptionError.interface.php',
            'Logger'=>Lib.'/core/logger/abs/Logger.class.php',
            'UserError'=>Lib.'/core/logger/error/UserError.class.php',
            'ExceptionError'=>Lib.'/core/logger/exception/ExceptionError.class.php',
        );
    }
    private static function inputCoreFile(): array
    {
        global $MODULE;
        return array(
            'Input'=>Lib.'/core/input/Input.class.php',
            'Action'=>Lib.'/core/action/Action.class.php',
        );
    }
    /**
     * middleware核心文件
     * @return array
     */
    private static function middlewareCoreFile(): array
    {
        return array(
            'IMiddleware'=>Lib.'/core/middleware/intf/IMiddleware.interface.php',
            'Middleware'=>Lib.'/core/middleware/implement/Middleware.class.php'
        );
    }

//    /**
//     * pdo链接池
//     * @return string[]
//     */
//    private static function poolPdoCoreFile(): array
//    {
//        return array(
//            'QMysqlPoolPdo'=> Lib.'/core/pdo/mysql/QMysqlPoolPdo.class.php',
//        );
//    }
    /**
     * mysql核心文件
     * @return array
     */
    private static function mysqlCoreFile(): array
    {
        return array(
            'IPdo'=>Lib.'/core/pdo/intf/IPdo.interface.php',
            'QDbPdo'=> Lib.'/core/pdo/abs/QDbPdo.class.php',
            'QDbMysql'=> Lib.'/core/pdo/mysql/QDbMysql.class.php',
            'IPdoConn'=>Lib.'/core/pdo/intf/IPdoConn.interface.php',
            'QDbPdoMysqlConn'=> Lib.'/core/pdo/mysql/QDbPdoMysqlConn.class.php',
            'IPdoPool'=>Lib.'/core/pdo/intf/IPdoPool.interface.php',
            'QDbConf'=>Lib.'/core/pdo/conf/QDbConf.class.php',
            'QDbPdoMysqlPool'=> Lib.'/core/pdo/mysql/QDbPdoMysqlPool.class.php',
            'QDbPdoPoolFactory'=> Lib.'/core/pdo/QDbPdoPoolFactory.class.php',
            'QDbFactory'=> Lib.'/core/pdo/QDbFactory.class.php',
            'IModel'=>Lib.'/core/model/intf/IModel.interface.php',
            'IModelBase'=>Lib.'/core/model/intf/IModelBase.interface.php',
            'BaseModel'=>Lib.'/core/model/abs/BaseModel.class.php',
            'MysqlM'=>Lib.'/core/model/mysql/MysqlM.class.php',
            'IModelFactory'=>Lib.'/core/model/intf/IModelFactory.interface.php',
            'ModelFactory'=>Lib.'/core/model/ModelFactory.class.php',
            'Model'=>Lib.'/core/model/Model.class.php'
        );
    }
    /**
     * oracle核心文件
     * @return array
     */
    private static function oracleCoreFile(): array
    {
        return array(
            'IPdo'=>Lib.'/core/pdo/intf/IPdo.interface.php',
            'QDbPdo'=> Lib.'/core/pdo/abs/QDbPdo.class.php',
            'QDbOracle'=> Lib.'/core/pdo/oracle/QDbOracle.class.php',
            'IPdoConn'=>Lib.'/core/pdo/intf/IPdoConn.interface.php',
            'QDbPdoOracleConn'=> Lib.'/core/pdo/oracle/QDbPdoOracleConn.class.php',
            'IPdoPool'=>Lib.'/core/pdo/intf/IPdoPool.interface.php',
            'QDbConf'=>Lib.'/core/pdo/conf/QDbConf.class.php',
            'QDbPdoOraclePool'=> Lib.'/core/pdo/oracle/QDbPdoOraclePool.class.php',
            'QDbPdoPoolFactory'=> Lib.'/core/pdo/QDbPdoPoolFactory.class.php',
            'QDbFactory'=> Lib.'/core/pdo/QDbFactory.class.php',
            'IModel'=>Lib.'/core/model/intf/IModel.interface.php',
            'IModelBase'=>Lib.'/core/model/intf/IModelBase.interface.php',
            'BaseModel'=>Lib.'/core/model/abs/BaseModel.class.php',
            'OracleM'=>Lib.'/core/model/oracle/OracleM.class.php',
            'IModelFactory'=>Lib.'/core/model/intf/IModelFactory.interface.php',
            'ModelFactory'=>Lib.'/core/model/ModelFactory.class.php',
            'Model'=>Lib.'/core/model/Model.class.php'
        );
    }

    /**
     * redis核心文件
     * @return array
     */
    private static function redisCoreFile(): array
    {
        return array(
            'QRedis'=>Lib.'/core/cache/redis/QRedis.class.php',
            'R'=>Lib.'/core/cache/redis/R.class.php'
        );
    }
    /**
     * memcache核心文件
     * @return array
     */
    private static function memcacheCoreFile(): array
    {
        return array(
            'MmCache'=>Lib.'/core/cache/memcache/MmCache.class.php',
            'M'=>Lib.'/core/cache/memcache/M.class.php'
        );
    }
    /**
     * language核心文件
     * @return array
     */
    private static function languageCoreFile(): array
    {
        return array(
            'Lang'=>Lib.'/core/lang/Lang.class.php'
        );
    }
}
