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
     * [简单登陆验证匹配]
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
     * [登陆验证匹配-该方法未使用，仅做参考]
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
    /**
     * [用户上传头像接口请求的方法]
     * @return [type] [description]
     */
    public function uploadheadimg()
    {
         //1. 接收参数
         $this->datas = $this->params;

         //2. 上传文件获取路径
         $head_img_path = $this->uploadFiles($this->datas['user_icon'], 'head_img');

         //3. 存入数据库
         $res = db('user')->where('user_id', $this->datas['user_id'])->update(['user_icon' => $head_img_path]);

         //4. 返回结果给客户端
         if (!empty($res)) {
             $this->return_msg(200, '上传头像成功', $head_img_path);
         } else {
             $this->return_msg(400, '上传头像失败');
         }
    }
    /**
     * [用户修改密码接口请求的方法]
     * @return [null]
     */
    public function changepwd()
    {
        //1. 接受参数
        $this->datas = $this->params;

        //2. 确定用户名类型
        $userType = $this->checkUsername($this->datas['user_name']);

        //3. 确定该用户名是否已经存在数据库
        $this->checkExist($this->datas['user_name'], $userType, 1);

        //4. 同时匹配用户名和密码
        $res = db('user')->where(['user_' . $userType => $this->datas['user_name'], 'user_pwd' => md5($this->datas['user_old_pwd'])])->find();

        //5. 匹配成功则将新密码加密后更新该用户密码
        if (!empty($res)) {

            //更新user_pwd字段
            $resu = db('user')->where('user_' . $userType, $this->datas['user_name'])->update(['user_pwd' => md5($this->datas['user_pwd'])]);

            if (!empty($resu)) {
                $this->return_msg(200, '密码修改成功!');
            } else {
                $this->return_msg(400, '密码修改失败!');
            }
        } else {
            $this->return_msg(400, '密码错误!');
        }
    }
}