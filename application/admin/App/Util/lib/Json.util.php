<?php
namespace admin\Util\lib;

class JsonUtil{

    public static function echoJson($res =true, $code = 1,$msg='success', $data = []) {
        header('content-type:application/json;charset=utf-8');
        $result = ['res'=>$res,'code' => $code, 'msg' => $msg,'data'=>$data];
        echo json_encode($result);
        exit;
    }
}
