<?php
namespace app\api\controller;

/**
 * 
 */
class User extends Common
{
	
	public function login()
	{
		// $data = $this->params;
		// dump($data);
      
	}
	/**
     * [用户注册时接口请求的方法]
     * @return [null]
     */
    public function register()
    {
    	//接收参数
        $data = $this->params;
        // dump($data);

        //检测验证码
        $this->checkCode($data['user_name'], $data['code']);

        //检测用户名
        $user_name_type = $this->checkUsername($data['user_name']);

        switch ($user_name_type) {
        	case 'phone':
        		$this->checkExist($data['user_name'],'phone',0);
        		$data['user_phone'] = $data['user_name'];
        		break;
        	
        	case 'email':
        		$this->checkExist($data['user_name'],'email',0);
        		$data['user_email'] = $data['user_name'];
        		break;
        }

        //将用户信息写入数据库
        unset($data['user_name']);//删除user_name字段
        $data['user_rtime'] = time();

        //往api_user表中插入用户数据
        $res = db('user')->insert($data);

        //返回执行结果
        if (!$res) {
        	$this->return_msg(400, '用户注册失败！');
        } else {
            $this->return_msg(200, '用户注册成功！');
        }
    }
    

}