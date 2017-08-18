<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;

$host = "http://yaf-api.dev/";
$curl = new Curl();
$username = 'apitest_uname_' . rand();
$pwd = 'apitest_pwd_' . rand();

//注册接口验证
$curl->post($host . 'user/register', [
    'uname' => $username,
    'pwd' => $pwd
]);
if ($curl->error) {
    die("Error:" . $curl->error_code . ":" . $curl->error_message . "\n");
} else {
    $rep = json_decode($curl->response, true);
    if ($rep['errno'] != 0) {
        die("Error:注册用户失败，" . $rep['errno'] . ":" . $rep['errmsg']);
    }
    echo "注册用户接口测试成功，注册新用户：" . $username . "\n";
}

//登录接口验证
$curl->post($host . 'user/login?submit=1', [
    'uname' => $username,
    'pwd' => $pwd
]);
if ($curl->error) {
    die("Error:" . $curl->error_code . ":" . $curl->error_message . "\n");
} else {
    $rep = json_decode($curl->response, true);
    if ($rep['errno'] != 0) {
        die("Error:用户登录失败，" . $rep['errno'] . ":" . $rep['errmsg']);
    }
    echo "用户登录接口测试成功，注册新用户：" . $username . "\n";
}

echo 'check done.' . "\n";
