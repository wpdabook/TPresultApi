<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

 use think\Route;

 //1:api.tp5.com ===> www.tp5.com.index.php/api
 Route::domain('api','api');

 //2:api.tp5.com/user/2 ===> www.tp5.com/index.php/api/user/index/id/2
 //Route::rule('user/:id','user/index');

 //3:post api.tp5.com/user user.php login()
 Route::post ('user','user/login');

 //配置验证码请求路径
 Route::get('code/:time/:token/:username/:is_exist', 'code/get_code');

 //配置用户注册的请求路径
 Route::post('user/register', 'user/register');
 
 //配置用户登录的请求路径
 Route::post('user/login', 'user/login');

 //配置用户上传头像请求路径
 Route::post('user/icon', 'user/uploadheadimg');