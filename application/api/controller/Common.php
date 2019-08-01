<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\Validate;

/**
 * 接口-公共类
 */
class Common extends Controller
{
	
	protected $request;//用来处理参数
	protected $validater;//用来验证数据/参数
	protected $params; //过滤后符合要求的参数（不包括time、token）
	protected $rules = array(
                 'User'=>array(
                      'login'=>array(
                        'user_name'=>['require','chsDash','max'=>20],//当自定义正则,正则没有竖杠的时候使用数组方式
                        'user_pwd'=>'require|length:32'
                       ),
                       'register' => array(
                         'user_name' => ['require'],
                         'user_pwd' => ['require', 'max' => 32, 'min' => 8],
                         'code' => ['require', 'number', 'length' => 6],
                       ),
                 ),
                 'Code' => array(
                      'get_code' => array(
                         'username' => 'require',
                         'is_exist' => 'require|number|length:1',
                 ),
        ),

	);
	protected function _initialize(){
		parent::_initialize();
		$this->require = Request::instance();
		// $this->check_time($this->request->only(['time']));
	    // $this->check_token($this->request->param());
	    $this->params = $this->check_params($this->request->except(['time','token']));
	}
	public function check_time($arr){
		if(!isset($arr['time'])||intval($arr['time'])<=1){
             $this->return_msg(400,'时间戳不正确！');
		}
		/**
		 * 验证请求是否超时（time参数验证）
		 */
		if(time()-intval($arr['time'])>60){
              $this->return_msg(400,'请求超时！');
		}
	}
	   /**
		 * api数据返回
		 * @param [int] $code [结果码 200：正常/4**数据问题/5**服务器问题]
		 * @param [string] $msg [接口要返回的提示信息]
		 * @param [array] $data [接口要返回的数据]
		 * @param [string] [最终的JSON数据]
		 */
	public function return_msg($code,$msg='',$data=[]){
		/******************组合数据**************************/
		$return_data['code'] = $code;
		$return_data['msg'] = $msg;
		$return_data['data'] = $data;
		/*******************返回信息并终止脚本**************/
		echo json_encode($return_data);die;
	}
	   /**
		 * 验证token(防止篡改数据)
		 * @param [array] $arr [全部请求参数]
		 * @param [json] [token验证结果]
		 */
	public function check_token($arr){
		/*************api传来的token**********************/
		if(!isset($arr['token'])||empty($arr['token'])){
			$this->return_msg(400,'token不能为空！');
		}
		$app_token = $arr['token'];//api 传过来的token
       /*************服务器端生成token**********************/
        unset($arr['token']);
        $service_token = '';
        foreach ($arr as $key => $value) {
        	$service_token .= md5($value);
        }
        $service_token = md5('api_'.$service_token.'_api');
        //dump($service_token);die;
        /******************对比token，返回的结果*************/
        if($app_token != $service_token){
             $this->return_msg(400,'token值不正确！');
        }

	}
	   /**
		 * 验证参数 参数过滤
		 * @param [array] $arr [除time和token外的所有参数]
		 * @param [return] [合格的参数数组]
		 */
	public function check_params($arr){
        /****************** 获取参数的验证规则 *************/
        //数组是一个小型数据库 下标：【控制器】【方法】；
        $rule = $this->rules[$this->request->controller()][$this->request->action()];
        /****************** 验证参数并返回错误 *************/
        $this->validater = new Validate($rule);
        if(!$this->validater->check($arr)){
             $this->return_msg(400,$this->validater->getError());
        }
        /****************** 如果正常，通过验证 *************/
        return $arr;
	}
	/**
	  * 检测用户名,并且返回用户名类别
	  * @param [string] $username [用户名，可能是邮箱，也可能是手机号]
	  * @param [string] [检测结果]
	  */
    protected function checkUsername($username)
    {
    	/****************** 判断是邮箱 *************/
        $is_email = Validate::is($username, 'email') ? 1 : 0;
        /****************** 判断是手机 *************/
        $is_phone = preg_match('/^1[34578]\d{9}$/', $username) ? 4 : 2;
        /****************** 最终结果 *************/
        $flag = $is_email + $is_phone;

        switch ($flag) {
            case 2:
                //既不是邮箱，也不是手机号
                $this->return_msg(400, '用户名格式错误');
                break;

            case 3:
                //邮箱
                return 'email';
                break;

            case 4:
                //手机号
                return 'phone';
                break;
        }
    }
    /**
     * [检测该字段是否已经存在数据库中]
     * @param  [type] $value [description]
     * @param  [type] $type  [description]
     * @param  [type] $exist [description]
     * @return [type]        [description]
     */
    protected function checkExist($value, $type, $exist)
    {
        $type_num = $type == 'phone' ? 2 : 4;
        $flag = $type_num + $exist;

        $phone_res = db('user')->where('user_phone', $value)->find();
        $email_res = db('user')->where('user_email', $value)->find();

        switch ($flag) {
            case 2:
                if ($phone_res) {
                    $this->return_msg(400, '此手机号已经被占用！');
                }
                break;
            case 3:
                if (empty($phone_res)) {
                    $this->return_msg(400, '此手机号不存在！');
                }
                break;
            case 4:
                if ($email_res) {
                    $this->return_msg(400, '此邮箱已经被注册！');
                }
                break;
            case 5:
                if (empty($email_res)) {
                    $this->return_msg(400, '此邮箱不存在！');
                }
                break;
        }
    }
    /**
     * [检查验证码是否输入正确]
     * @param  [string] $username [用户名(phone/email)]
     * @param  [int] $code     [验证码]
     * @return [json]           [执行返回信息]
     */
    protected function checkCode($username, $code)
    {

        //检测验证码时候输入正确
        $input_code = md5($username . '_' . md5($code));
        $last_code = session($username . '_code');//568648869@qq.com_code
        dump(session($username . '_code')); 
        if ($input_code !== $last_code) {
            $this->return_msg('400', '验证码不正确，请重新输入！');
        }

        //检测是否超时
        $last_time = session($username . '_last_send_time');
        if (time() - $last_time > 600) {
            $this->return_msg('400', '验证码超过600秒，请重新发送！');
        }
        
        //清除验证通过的验证码session
        //session($username . '_code', null);
    }

     
}