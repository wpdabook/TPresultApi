<?php
namespace app\api\controller;

/**
 * 
 */
class User extends Common
{
	/**
     * [用户登陆时接口请求的方法]
     * @return [null]
     */
	public function login()
	{
        //接收参数
		$this->datas = $this->params;

        //检测用户名类型
        $userType = $this->checkUsername($this->datas['user_name']);

        //检测用户名是否存在
        $this->checkExist($this->datas['user_name'],$userType,1);

        //在数据库中查询数据 (用户名和密码匹配)
        $this->SimpleMatchUserAndPwd($userType);
      
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
   /**
     * [简单登陆验证匹配-该方法未使用，仅做参考]
     * @param  [string] $type [用户名类型 phone/email]
     * @return [json]       [登陆返回信息]
     */
    private function SimpleMatchUserAndPwd($type)
    {
        $res = db('user')->where('user_' . $type, $this->datas['user_name'])->where('user_pwd', md5($this->datas['user_pwd']))->find();

        if (!empty($res)) {
            unset($res['user_pwd']);
            $this->return_msg(200, '登陆成功！', $res); 
        } else {
            $this->return_msg(400, '用户名或密码错误！');
        }
    }
    /**
     * [登陆验证匹配]
     * @param  [string] $type [用户名类型 phone/email]
     * @return [json]       [登陆返回信息]
     */
    private function MatchUserAndPwd($type)
    {
       switch ($type) {
           case 'phone':
               $this->checkExist($this->datas['user_name'],'phone',1);
               $res = db('user')
                  ->field('user_id,user_name,user_phone,user_email,user_rtime')
                  ->where('user_phone',$this->datas['user_name'])
                  ->find();
               break;
           
           case 'email':
              $this->checkExist($this->datas['user_name'],'email',1);
               $res = db('user')
                  ->field('user_id,user_name,user_phone,user_email,user_rtime')
                  ->where('user_email',$this->datas['user_name'])
                  ->find();
               break;
       }
       dump($res['user_pwd']);
       dump(md5($this->datas['user_pwd']));
       if($res['user_pwd'] !== md5($this->datas['user_pwd'])){
              $this->return_msg(400,'用户名或者密码不正确！');
       }else{
              $this->return_msg(200,'登录成功！',$res); 
       }
    }
}