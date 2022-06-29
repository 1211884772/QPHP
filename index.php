<?php
header("Content-type:text/html;charset=utf-8");//设置框架编码
ini_set("data.timezone", "Asia/Shanghai");//设置时区
define('APP_PATH', __DIR__ . '/');//定义我们的项目路径常量
define('Lib', APP_PATH.'QPHP');//定义我们框架目录常量
//define('RPC_RUN',false);//是否开启rpc
//define('ROUTE_PATH',true);//是否开启路由模式
//define('APP_DEBUG', TRUE);
ini_set("display_errors", true);//是否抛出错误 上线修改为false
//加载vendor
require_once APP_PATH . 'vendor/autoload.php';
//引入框架核心文件
require_once Lib . '/QPHP.php';

//$pool = new Pool(4);
//$pool->submit(new class extends Threaded {
//    public function run() {
//        $app = new QPHP();
//        $app->run();
//    }
//});
//while ($pool->collect()) continue;
//$pool->shutdown();
QPHP::instance()->run();
$app = null;
