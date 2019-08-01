<?PHP
    namespace submail;
    use submail\message;

    class MESSAGEXsend
    {

    protected $appid = '';

    protected $appkey = '';

    protected $sign_type = '';

    protected $To = array();

    protected $Addressbook = array();

    protected $Project = '';

    protected $Vars = array();

    public function __construct()
    {
            $this->appid = "38879";
            $this->appkey = "e7d2a68ba597d21036a1659c44576fe7";
            $this->sign_type = 'normal';
    }

    public function SetTo($address)
    {
        $this->To = trim($address);
    }

    public function AddAddressbook($addressbook)
    {
        array_push($this->Addressbook, $addressbook);
    }

    public function SetProject($project)
    {
        $this->Project = $project;
    }

    public function AddVar($key, $val)
    {
        $this->Vars[$key] = $val;
    }

    public function buildRequest()
    {
        $request = array();
        $request['to'] = $this->To;
        if (!empty($this->Addressbook)) {
            $request['addressbook'] = '';
            foreach ($this->Addressbook as $tmp) {
                $request['addressbook'] .= $tmp . ',';
            }
            $request['addressbook'] = substr($request['addressbook'], 0, count($request['addressbook']) - 2);
        }

        $request['project'] = $this->Project;
        if (!empty($this->Vars)) {
            $request['vars'] = json_encode($this->Vars);
        }
        return $request;
    }
    public function xsend()
    {
        $message_configs['appid'] = $this->appid;
        $message_configs['appkey'] = $this->appkey;
        if ($this->sign_type != '') {
            $message_configs['sign_type'] = $this->sign_type;
        }
        $message = new message($message_configs);
        return $message->xsend($this->buildRequest());
    }

}
