<?PHP

    namespace submail;
    class message{
        
        protected $base_url='http://api.mysubmail.com/';
        //protected $base_url='http://api.submail.cn/';
        var $message_configs;
        
        var $signType='normal';
        
        function __construct($message_configs){
            $this->message_configs=$message_configs;
        }
        
        protected function createSignature($request){
            $r="";
            switch($this->signType){
                case 'normal':
                    $r=$this->message_configs['appkey'];
                    break;
                case 'md5':
                    $r=$this->buildSignature($this->argSort($request));
                    break;
                case 'sha1':
                    $r=$this->buildSignature($this->argSort($request));
                    break;
            }
            return $r;
        }
        
        protected function buildSignature($request){
            $arg="";
            $app=$this->message_configs['appid'];
            $appkey=$this->message_configs['appkey'];
            while (list ($key, $val) = each ($request)) {
                $arg.=$key."=".$val."&";
            }
            $arg = substr($arg,0,count($arg)-2);
            if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
            
            if($this->signType=='sha1'){
                $r=sha1($app.$appkey.$arg.$app.$appkey);
            }else{
                $r=md5($app.$appkey.$arg.$app.$appkey);
            }
            return $r;
        }
        
        protected function argSort($request) {
            ksort($request);
            reset($request);
            return $request;
        }
        
        protected function getTimestamp(){
            $api=$this->base_url.'service/timestamp.json';
            $ch = curl_init($api) ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
            $output = curl_exec($ch) ;
            $timestamp=json_decode($output,true);
            
            return $timestamp['timestamp'];
        }
        
        protected function APIHttpRequestCURL($api,$post_data,$method='post'){
            if($method!='get'){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch,CURLOPT_HTTPHEADER,array("X-HTTP-Method-Override: $method"));
                if($method!='post'){
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
                }else{
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                }
            }else{
                $url=$api.'?'.http_build_query($post_data);
                $ch = curl_init($url) ;
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1) ;
            }
            $output = curl_exec($ch);
            curl_close($ch);
            $output = trim($output, "\xEF\xBB\xBF");
            return json_decode($output,true);
        }
        
        public function send($request){
            $api=$this->base_url.'message/send.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $send=$this->APIHttpRequestCURL($api,$request);
            return $send;
        }
        
        public function xsend($request){
            $api=$this->base_url.'message/xsend.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $send=$this->APIHttpRequestCURL($api,$request);
            return $send;
        }
        public function multixsend($request){
            $api=$this->base_url.'message/multixsend.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            
            
            $request['signature']=$this->createSignature($request);
            $send=$this->APIHttpRequestCURL($api,$request);
            return $send;
        }
        
        public function subscribe($request){
            $api=$this->base_url.'addressbook/message/subscribe.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $subscribe=$this->APIHttpRequestCURL($api,$request);
            return $subscribe;
        }
        
        public function unsubscribe($request){
            $api=$this->base_url.'addressbook/message/unsubscribe.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $unsubscribe=$this->APIHttpRequestCURL($api,$request);
            return $unsubscribe;
        }
        public function log($request){
            $api=$this->base_url.'log/message.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $log=$this->APIHttpRequestCURL($api,$request);
            return $log;
        }
        public function getTemplate($request){
            $api=$this->base_url.'message/template.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $templates=$this->APIHttpRequestCURL($api,$request,'get');
            return $templates;
        }
        public function postTemplate($request){
            $api=$this->base_url.'message/template.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $templates=$this->APIHttpRequestCURL($api,$request,'post');
            return $templates;
        }
        public function putTemplate($request){
            $api=$this->base_url.'message/template.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $templates=$this->APIHttpRequestCURL($api,$request,'PUT');
            return $templates;
        }
        public function deleteTemplate($request){
            $api=$this->base_url.'message/template.json';
            $request['appid']=$this->message_configs['appid'];
            $request['timestamp']=$this->getTimestamp();
            if(empty($this->message_configs['sign_type'])
               && $this->message_configs['sign_type']==""
               && $this->message_configs['sign_type']!="normal"
               && $this->message_configs['sign_type']!="md5"
               && $this->message_configs['sign_type']!="sha1"){
                $this->signType='normal';
            }else{
                $this->signType=$this->message_configs['sign_type'];
                $request['sign_type']=$this->message_configs['sign_type'];
            }
            $request['signature']=$this->createSignature($request);
            $templates=$this->APIHttpRequestCURL($api,$request,'DELETE');
            return $templates;
        }
    }
