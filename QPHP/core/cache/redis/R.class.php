<?php
namespace QPHP\core\cache\redis;


use Exception;
use QPHP\core\cache\memcache\MmCache;
use QPHP\core\container\Container;

class R
{
    private static $ins=null;

    /**
     * 单例
     * @return R|null
     */
    public static function instance(){
        if(is_null(self::$ins)||!(self::$ins instanceof R)){
            self::$ins = new self();
        }
        return self::$ins;
    }

    /**
     * 实例化Qredis
     * @return QRedis
     * @throws Exception
     */
    protected function getRedis(){
//        $redis = new QRedis(REDIS_POOL["redis_0"]["REDIS_HOST"],REDIS_POOL["redis_0"]["REDIS_PORT"]);
//        return $redis;
        return Container::getInstance()->makeWith(QRedis::class, [REDIS_POOL["redis_0"]["REDIS_HOST"],REDIS_POOL["redis_0"]["REDIS_PORT"]]);
    }


    public function __call($method, $parameters)
    {
        return $this->$method(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (self::instance())->$method(...$parameters);
    }
}
