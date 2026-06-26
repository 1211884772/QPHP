# QPHP
#### 计划：
#### 1.C语言实现php连接池功能，php扩展形式
#### 2.php多线程的实现
#### 3.压测，线程数为10
![yace01.png](yace01.png)
### 开发功能：
##### 1.qphp是一个轻量级的phpmvc框架，支持多语言，mysql,oracle,memcache,redis
##### 框架执行时间：6毫秒,thinkphp执行时间：19毫秒
##### 2.开发按需加载功能，如：
##### 2.1默认核心，只加载 ,配置模块，错误异常模块
##### 2.2需要开启才加载的模块。语言模块， MySQL模块，oracle模块，redis模块，memcache模块
```php
    //MODULE未测试通过，自动加载不会加载多余的类
    'MODULE'=>array(//开启相应模块 'MYSQL_OPEN'=>true,关闭 'MYSQL_OPEN'=>false,
        'MYSQL_OPEN'=>true,
        'ORACLE_OPEN'=>true,
        'REDIS_OPEN'=>true,
        'MEMCACHE_OPEN'=>true,
        'LANGUAGE_OPEN'=>true,//是否开启多语言
    ),
```
##### 3.jwt生成token,和验证
##### 4.增加路由功能，跨域请求
##### 5.新增命名空间namespace
##### 6.完成增删改查（CURD）功能
##### 7.全局配置功能，模块配置功能 
##### 8.新增分库功能，多库切换操作，分表联查，连接有简单连接池管理
##### 9.新增链式查询功能
##### 10.路由实现跨域和分组
##### 11.优化核心pdo组件链接mysql,oracle组件化配置，可插拔扩展(思想：高内聚，低耦合)
##### 12.memcache,redis加入框架核心，取消在模块中配置缓存
##### 13.升级到php8.2.0验证器组件计划重写
##### 14.新增验证器，修改验证器的设计方案，使其扩展简单
##### 15.验证器支持多语言验证，中英文，支持验证和过滤功能
##### 16.框架也支持多语言，中|英文|等等，全局配置APP_LANG=>TRUE
##### 17.jwt重写生成长短token，无刷新长token获取短token
##### 18.所有接口rsa数据加密解密，数据加签，加签后验证

```php
###### 1.前端所有接口，先加签名，在加密数据和签名
###### 2.后端先解密数据，验证签名，然后接收数据
###### 3.js前端rsa加密加签,后端解密，验签
###### 加签解签
D:\phpstudy_pro\WWW\www.qphp.com\application\admin\Resource\jsrsasign11.1.0
###### 加密解密
D:\phpstudy_pro\WWW\www.qphp.com\application\admin\Resource\jsencrypt3.3.2
```
##### 19.nginx伪静态
```php
快速配置：
if (!-e $request_filename) {
	rewrite  ^[php.ini](..%2F..%2FExtensions%2Fphp%2Fphp8.4.6nts%2Fphp.ini)(.*)$  /index.php?s=/$1  last;
	break;
}

完全配置：
server {
	listen 80;
	server_name  all.bjed.com;
	root   "F:\www\asdata";
	location / {
		index  index.html index.htm index.php;
		#autoindex  on;

		# 新增内容开始
		if (!-e $request_filename) {
			rewrite  ^(.*)$  /index.php?s=/$1  last;
			break;
		}
		# 新增内容结束
	}
}
```
##### 20.美化异常和用户错误提示信息
```php
    /**
     * 测试系统异常和自定义错误
     * 系统异常优先抛出，系统异常解决后，才会抛出用户自定义错误信息
     */
    public function testError(){
        //抛出用户自定义错误信息
        trigger_error("错误信息", E_USER_ERROR);
        //系统异常优先抛出
        $a;
        //$a
    }
```
##### 21.新增中间件，只实现了前置后置功能
```php
<?php
namespace admin\Middleware;

use QPHP\core\middleware\intf\IMiddleware;

class IndexMiddleware implements IMiddleware
{
    /**
     * 中间件助手函数
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public static function handle(array $input,$next)
    {
        // TODO: Implement handle() method.
        print_r('IndexMiddleware before ') ;
        $request =$next($input);
        print_r('IndexMiddleware after ');
        return $request;
    }
}
```
##### 22.单机单库mysql数据库事务支持，分布式数据库事务不支持
```php
    /**
     * 单机数据库mysql事务测试
     * @return bool
     */
    public function getTransactionData(): bool
    {
        echo 66;
       // $this->getLastSql();
       // $this->Db('mysql_0')->startTrans();
        $sql1="insert into mm_user (username,age,address,pwd) values ('mumuww5',18,'西安','123456')";
        $res_1=  $this->Db('mysql_0')->executeSql("insert",$sql1);
        var_dump($res_1);
        if(!$res_1){
            echo 222;
            //$this->Db('mysql_0')->rollback();
            return false;
        }
        $sql2="insert into mm_user_info (user_id,birthday,name,info) values (1,599068800,'mumuww5','wangwei')";
        $res_2 = $this->Db('mysql_0')->executeSql("insert",$sql2);
        var_dump($res_2);
        if(!$res_2) {
            //$this->Db('mysql_0')->rollback();
            echo 333;
            return false;
        }
       // $this->Db('mysql_0')->commit();
        echo 444;
        return true;
    }
```
##### 23.单机单库oracle数据库事务支持，ocid8.分布式数据库事务不支持，
```php

    /**
     * 单机数据库oracle事务测试
     * @return bool
     */
    public function getTransactionData(): bool
    {
        echo 88;
        $this->Db('oracle_0')->startTrans();
        $sql1="insert into \"mm_user\" (\"id\",\"username\",\"age\",\"address\",\"pwd\") values (41,'mumuww5',18,'西安','123456')";
        //$this->getLastSql();
        $res_1=  $this->Db('oracle_0')->executeSql("insert",$sql1);
        var_dump($res_1);
        if(!$res_1){
            echo 222;
            $this->Db('oracle_0')->rollback();
            return false;
        }
        $sql2="insert into \"mm_user_info\" (\"id\",\"user_id\",\"birthday\",\"name\",\"info\") values (41,2014,599068800,'mumuww5','wangwei')";
        $res_2 = $this->Db('oracle_0')->executeSql("insert",$sql2);
        var_dump($res_2);
        if(!$res_2) {
            $this->Db('oracle_0')->rollback();
            echo 333;
            return false;
        }
        $this->Db('oracle_0')->commit();
        echo 444;
        return true;
    }
```

### 组件：
##### 1.新增验证器过滤器
##### 2.新增分布式id
##### 3.新增多线程

### 使用说明：
##### 1.新增验证器过滤器
```php
 注意：此验证器组件是我自研的，因为框架升级3.0V版本后，原来是用别人的组件不能用了
 composer require qphp/php-validate:dev-main
 https://github.com/1211884772/php-validate
```
##### 2.项目application\admin,admin为实例代码
##### 3.新写业务参照admin和index模块
##### 4.请求地址http://www.qphp.com/admin/user/index?id=10
```php
##### github: https://github.com/1211884772/QPHP
##### packagist: https://packagist.org/packages/qphp/qphp
```
##### 5.增加简单路由功能
```php
在文件route目录index.php,admin.php重复的会覆盖

Route::middleware('admin\middleware\LoginMiddleware')->middleware('admin\middleware\IndexMiddleware')->middleware('admin\middleware\UserMiddleware')->get('admin/testMiddleware','admin/index/testMiddleware')->header('Access-Control-Allow-Origin','*')->header('Access-Control-Allow-Credentials', 'true')->allowCrossDomain();
;
//分组1
Route::middleware('admin\middleware\UserMiddleware')->group('admin/',function(){
    Route::get('age','admin/index/age');
//    Route::get('name','admin/index/name');
    Route::get('userAdd','admin/user/add');
    Route::get('userEdit','admin/user/edit');
    Route::get('userDel','admin/user/del');
})->header('Access-Control-Allow-Origin','*')->header('Access-Control-Allow-Credentials', 'true')->allowCrossDomain();
//分组2
Route::middleware('admin\middleware\LoginMiddleware')->group('admin1/',function(){
    Route::get('name','admin/index/name');
})->header('Access-Control-Allow-Origin','*')->header('Access-Control-Allow-Credentials', 'true')->allowCrossDomain();

```
##### 9.全局配置功能，模块配置功能 
###### 9.1.模块配置会自动覆盖全局配置的参数的数据
###### 9.2.跟业务无关的配置只会在全局配置文件生效如:
```php
    'RPC_RUN'=>false,//是否开启rpc
    'ROUTE_PATH'=>true,//是否开启路由模式
    'APP_DEBUG'=> true,//debug//开启错误是否显示到页面
```
##### 10.生成分布式id 
```php
安装：
https://packagist.org/packages/dekuan/dedid
    composer require dekuan/dedid
```
##### 11.新增分库功能，多库切换操作，连接有简单连接池管理
```php
<?php
$config['app'] = array(

    //全局数据库配置
    'mysql_0' => array(
        'host' => '127.0.0.1',
        'dbname' => 'qphp',
        'mysql_user' => 'qphp',
        'mysql_pwd' => '123456',
        'port' => 3306
    ),
    'mysql_1' => array(
        'host' => '127.0.0.1',
        'dbname' => 'qphp_01',
        'mysql_user' => 'root',
        'mysql_pwd' => '123456',
        'port' => 3306
    ),
    'mysql_2' => array(
        'host' => '127.0.0.1',
        'dbname' => 'qphp_02',
        'mysql_user' => 'root',
        'mysql_pwd' => '123456',
        'port' => 3306
    ),
);
```
```php
php模型分表查询，并排序
<?php
     public function getUser(){
        // select id,title from collect where id>=(select id from collect order by id limit 90000,1) limit 10;
 
         $data = [];
         //第二页 id<35 //分页由前端做，一次性返回多条数据
         $sql1="select * from ((select * from q_user_01 )UNION ALL (select * from q_user_02)) as u
 where u.id<35 ORDER BY u.create_time desc limit 10";
         $this->getLastSql();
         $data_1 = $this->Db('mysql_1')->executeSql("getRows",$sql1);
         $sql2="select * from ((select * from q_user_01 )UNION ALL (select * from q_user_02)) as u
 where u.id<35 ORDER BY u.create_time desc limit 10";
         $data_2=$this->Db('mysql_2')->executeSql("getRows",$sql2);
         $sql3="select * from ((select * from q_user_01 )UNION ALL (select * from q_user_02)) as u
 where u.id<35 ORDER BY u.create_time desc limit 10";
         $data_3=$this->Db('mysql_3')->executeSql("getRows",$sql3);
         $data = array_merge($data_1,$data_2,$data_3);
         $sort_c = array_column($data,'create_time');
         $sort_u = array_column($data,'updat_time');
         array_multisort($sort_c, SORT_DESC,SORT_NUMERIC,$sort_u,SORT_DESC,SORT_NUMERIC,$data);
 
         return $data;
     }
```
##### 10.生成分布式id 
# ALGORITHM

### Bit structure
It's a 64 bits bigint.

~~~
0 xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx x xxxxx xxxxx xxxx xxxxxxxx
~~~

### Details

Position  | Length   | Usage	| Remark
----------|----------|----------|----------
0	| 1	| Reserved | Always be 0
1~41	| 41	| Escaped Time (in millisecond) | 0~69 years
42~46	| 5	| Number of data center | 0~31
47~51	| 5	| Number of data node in the data center | 0~31
52~63	| 12	| Random / Hash | 0~4095




# Bit marks

### Center
~~~
0 00000000 00000000 00000000 00000000 00000000 0 11111 00000 0000 00000000

00000000 00000000 00000000 00000000 00000000 00111110 00000000 00000000

00       00       00       00       00       3E       00       00
~~~

### Node
~~~
0 00000000 00000000 00000000 00000000 00000000 0 00000 11111 0000 00000000

00000000 00000000 00000000 00000000 00000000 00000001 11110000 00000000

00       00       00       00       00       01       F0       00
~~~


### Escaped Time
~~~
0 11111111 11111111 11111111 11111111 11111111 1 00000 00000 0000 00000000

01111111 11111111 11111111 11111111 11111111 11000000 00000000 00000000

7F       FF       FF       FF       FF       C0       00       00
~~~


### Random or Hash value
~~~
0 00000000 00000000 00000000 00000000 00000000 0 00000 00000 1111 11111111

00000000 00000000 00000000 00000000 00000000 00000000 00001111 11111111

00       00       00       00       00       00       0F       FF
~~~


# HOW TO USE

### Create an new id normally

~~~
$cDId		= CDId::getInstance();
$nCenter	= 0;
$nNode		= 1;

$arrD		= [];
$nNewId	= $cDId->createId( $nCenter, $nNode, null, $arrD );

echo "new id = " . $nNewId . "\r\n";
print_r( $arrD );

~~~

##### output

~~~
new id = 114654484990270790
Array
(
    [center] => 0
    [node] => 1
    [time] => 27335759399
    [rand] => 3398
)
~~~


### Create an new id with crc32 hash value by a specified string

~~~
$cDId		= CDId::getInstance();
$nCenter	= 0;
$nNode		= 15;

$sSrc		= "dekuan";
$arrD		= [];
$nNewId	= $cDId->createId( $nCenter, $nNode, $sSrc, $arrD );

echo "new id = " . $nNewId . "\r\n";
print_r( $arrD );

~~~

##### output

~~~
new id = 114654631304370386
Array
(
    [center] => 0
    [node] => 1
    [time] => 27335794283
    [rand] => 2258
)
~~~




### Parse an id for getting the details

~~~
$cDId		= CDId::getInstance();
$arrId		= $cDId->parseId( 114654631304370386 );
print_r( $arrId );

~~~

##### output

~~~
Array
(
    [center] => 0
    [node] => 1
    [time] => 27335794283
    [rand] => 2258
)
~~~

### 1.下面是配置的一个案例全局配置，和模块admin,index的配置
```php
//全局配置文件加载在config\目录
config.php
/**
 * 总配置文件
 */
$config['app'] = array(
    'RPC_RUN'=>false,//是否开启rpc
    'ROUTE_PATH'=>true,//是否开启路由模式
    'APP_DEBUG'=> true,//debug//开启错误是否显示到页面


    //全局数据库配置
    'mem' => array(
        'host' => '127.0.0.1',
        'port' => '11211'
    ),

    'redis' => array(
        'host' => '127.0.0.1',
        'port' => '6379'
    ),
);

//局部配置文件在application\admin\Config\目录
config.php
<?php
$config['app'] = array(

    //全局数据库配置
    'mysql_0' => array(
        'host' => '127.0.0.1',
        'dbname' => 'qphp',
        'mysql_user' => 'qphp',
        'mysql_pwd' => '123456',
        'port' => 3306
    ),
    'mysql_1' => array(
        'host' => '127.0.0.1',
        'dbname' => 'qphp_01',
        'mysql_user' => 'root',
        'mysql_pwd' => '123456',
        'port' => 3306
    ),
    'mysql_2' => array(
        'host' => '127.0.0.1',
        'dbname' => 'qphp_02',
        'mysql_user' => 'root',
        'mysql_pwd' => '123456',
        'port' => 3306
    ),
);

//局部配置文件在application\index\Config\目录
config.php
<?php
$config['app'] = array(
    //全局数据库配置
    'oracle_0' => array(
        'host' => '192.168.123.101',
        'dbname' => 'QPHP',
        'oracle_user' => 'QPHP',
        'oracle_pwd' => '123456',
        'port' => 1521
    ),
    //全局数据库配置
    'oracle_1' => array(
        'host' => '192.168.123.101',
        'dbname' => 'QPHP_01',
        'oracle_user' => 'QPHP_01',
        'oracle_pwd' => '123456',
        'port' => 1521
    ),
);
```

### 1.新增Model的mysql链式查询
```php
   //查询   
           public function getUsers(){
               $data = $this->Db('mysql_0')->table('mm_user')->asTable('u')
                   ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
                   ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
                   ->leftJoin('mm_address  a on a.user_id =u.id')
                   ->where()
                   ->order('u.id desc')
                   ->limit(0,10)
                   ->select();
               return $data;
           }
       
           public function getCount(){
               $this->getLastSql();
               $data_count = $this->Db('mysql_0')->table('mm_user')->asTable('u')
                   ->field('u.*,ui.birthday,ui.info,a.address_info,a.is_default')
                   ->leftJoin('mm_user_info  ui on ui.user_id = u.id')
                   ->leftJoin('mm_address  a on a.user_id =u.id')
                   ->where()
                   ->count();
               return $data_count;
           }

//插入
        $model = new UserModel();
            $data =array(
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$password,
                'address'=>$address
            );
            $last_id = $model->Db('mysql_0')->table('mm_user')->insert($data);
//修改
        $model = new UserModel();
            $arr =array(
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$pwd,
                'address'=>$address
            );
            $where =" id={$id}";
            $res=$model->Db('mysql_0')->table('mm_user')->where($where)->update($arr);


//删除
            $model = new UserModel();
            $where ="id={$id}";
            $res= $model->Db('mysql_0')->table('mm_user')->where($where)->delete();

```
### 2.新增Model的oracle链式查询
```php
 //查询
    public function getUser(){
        $data = $this->Db("oracle_0")->table('"mm_user"')->asTable('u')
            ->field('u.*,ui."birthday",ui."info",a."address_info",a."is_default"')
            ->leftJoin('"mm_user_info"  ui on ui."user_id" = u."id"')
            ->leftJoin('"mm_address"  a on a."user_id" =u."id"')
            ->where()
            ->order('u."id" desc')
            ->limit(0,2)
            ->select();
        return $data;
    }


    public function getCount(){
        $data_count = $this->Db("oracle_0")->table('"mm_user"')->asTable('u')
            ->field('u.*,ui."birthday",ui."info",a."address_info",a."is_default"')
            ->leftJoin('"mm_user_info"  ui on ui."user_id" = u."id"')
            ->leftJoin('"mm_address"  a on a."user_id" =u."id"')
            ->where()
            ->count();
        return $data_count;
    }

//插入
            $model = new UserModel();
            $data =array(
                'id'=>112,
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$password,
                'address'=>$address
            );
            $last_id = $model->Db('mysql_0')->table('"mm_user"')->insert($data);

//修改
        $model = new UserModel();
            $arr =array(
                'username'=>$username,
                'age'=>$age,
                'pwd'=>$pwd,
                'address'=>$address
            );
            $where ="\"id\"={$id}";
            $res=$model->Db('mysql_0')->table('"mm_user"')->where($where)->update($arr);

//删除
            $model = new UserModel();
            $where ="\"id\"={$id}";
            $res= $model->Db('mysql_0')->table('"mm_user"')->where($where)->delete();


```

### 3.新增验证器过滤器,已废弃
###### composer require inhere/php-validate:dev-master
###### 地址：https://packagist.org/packages/inhere/php-validate
##### 方式2: 继承类 Validation
创建一个新的class，并继承 Inhere\Validate\Validation。用于一个（或一系列相关）请求的验证, 相当于 laravel 的 表单请求验证
此方式是最为完整的使用方式，可以配置规则，设置字段翻译，设置自定义的错误消息等
```php
use Inhere\Validate\Validation;

class CommonValidate extends Validation
{
    # 进行验证前处理,返回false则停止验证,但没有错误信息,可以在逻辑中调用 addError 增加错误信息
    public function beforeValidate(): bool
    {
        return true;
    }
    # 进行验证后处理,该干啥干啥
    public function afterValidate(): bool
    {
        return true;
    }
}


class UserValidate extends CommonValidate
{
 public function rules(): array
    {
        return [
            // 字段必须存在且不能为空
            ['tagId,title,userId,freeTime', 'required'],

            // 4<= tagId <=567
            ['tagId', 'size', 'min'=>4, 'max'=>567, 'filter' => 'int'],

            // title length >= 40. 注意只需一个参数的验证，无需加 key, 如这里的 40
            ['title', 'min', 40, 'filter' => 'trim'],

            // 大于 0
            ['freeTime', 'number'],

            // 含有前置条件
            ['tagId', 'number', 'when' => function($data) {
                return isset($data['status']) && $data['status'] > 2;
            }],

            // 在验证前会先过滤转换为 int。并且仅会在指明场景名为 'scene1' 时规则有效
            ['userId', 'number', 'on' => 'scene1', 'filter' => 'int'],
            ['username', 'string', 'on' => 'scene2', 'filter' => 'trim'],

            // 使用自定义正则表达式
            ['username', 'regexp' ,'/^[a-z]\w{2,12}$/'],

            // 自定义验证器，并指定当前规则的消息
            ['title', 'custom', 'msg' => '{attr} error msg!' ],

            // 直接使用闭包验证
            ['status', function($status) {
                if (is_int($status) && $status > 3) {
                    return true;
                }
                return false;
            }],

            // 标记字段是安全可靠的 无需验证
            ['createdAt, updatedAt', 'safe'],
        ];
    }

    // 定义不同场景需要验证的字段。
    // 功能跟规则里的 'on' 类似，两者尽量不要同时使用，以免混淆。
    public function scenarios(): array
    {
        return [
            'create' => ['user', 'pwd', 'code'],
            'update' => ['user', 'pwd'],
        ];
    }

    // 定义字段翻译
    public function translates(): array
    {
        return [
          'userId' => '用户Id',
        ];
    }

    // 自定义验证器的提示消息, 默认消息请看 {@see ErrorMessageTrait::$messages}
    public function messages(): array
    {
        return [
           // 使用验证器名字指定消息
          'required' => '{attr} 是必填项。',
          // 可以直接针对字段的某个规则进行消息定义
          'title.required' => 'O, 标题是必填项。are you known?',
        ];
    }

    // 添加一个验证器。必须返回一个布尔值标明验证失败或成功
    protected function customValidator($title): bool
    {
        // some logic ...
        // $this->getRaw('field'); 访问 data 数据

        return true; // Or false;
    }
}
```
##### 使用

```php
// 验证 POST 数据
$v = UserValidate::check($_POST);

// 验证失败
if ($v->isFail()) {
    var_dump($v->getErrors());
    var_dump($v->firstError());
}

// 验证成功 ...
$safeData = $v->getSafeData(); // 验证通过的安全数据
// $postData = $v->all(); // 原始数据

$db->save($safeData);
```
