# QPHP

##### 1.qphp是一个轻量级的phpmvc框架
##### 2.支持mysql,oracle,memcache,redis
##### 3.jwt生成token,和验证
##### 4.新增验证器过滤器
###### composer require inhere/php-validate:dev-master
##### 5.项目application\admin,admin为实例代码
##### 5.完成增删改查（CURD）功能
##### 6.新写业务参照admin和index模块
##### 7.请求地址http://www.qphp.com/admin/user/index?id=10
##### github: https://github.com/1211884772/QPHP
##### packagist: https://packagist.org/packages/qphp/qphp
##### 8.增加简单路由功能
```php
在文件route目录index.php,admin.php重复的会覆盖
$route = Route::instance();
$route->get('index/age','index/index/age');
$route->get('index/name','index/index/name');
$route->post('index/name','index/index/addName');
$route->put('index/name','index/index/putName');
$route->delete('index/name','index/index/delName');
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

### 3.新增验证器过滤器
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
