<?php
namespace app\api\controller;

/**
 * 
 */
class User extends Common
{
	
	public function login()
	{
		$data = $this->params;
		dump($data);
      
	}
	/**
     * [用户注册时接口请求的方法]
     * @return [null]
     */
    public function register()
    {
    	//接收参数
        $this->data = $this->params;

        //检测验证码
        $this->checkCode($this->data['user_name'], $this->data['code']);

        //检测用户名
        $this->checkRegisterUser();

        //将用户信息写入数据库
        $this->insertDataToDB();
        
    }

    /**
     * [检测用户名类型]
     * @return [null]
     */
    private function checkRegisterUser()
    {
        $user_name_type = $this->checkUsername($this->data['user_name']);
        switch ($user_name_type) {
            case 'phone':
                $this->checkExist($this->data['user_name'],'phone',0);
                $this->data['user_phone'] = $this->data['user_name'];
                break;
            
            case 'email':
                $this->checkExist($this->data['user_name'],'email',0);
                $this->data['user_email'] = $this->data['user_name'];
                break;
        }

    }
    /**
     * [插入用户信息至数据库]
     * @return [json] [注册行为产生的结果]
     */
    private function insertDataToDB()
    {
        unset($this->data['user_name']);//删除user_name字段
        $this->data['user_rtime'] = time();
        $this->data['user_pwd'] = md5($this->data['user_pwd']);

        //往api_user表中插入用户数据
        $res = db('user')->insert($this->data);

        //返回执行结果
        if (!$res) {
            $this->return_msg(400, '用户注册失败！');
        } else {
            $this->return_msg(200, '用户注册成功！');
        }
    }

}