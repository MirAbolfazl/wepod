<?php
class wepod {
  const VERSION = '0.0.1';
  const NAME = 'wepodphp';
  private $accessToken = '';
  private $keyId = '';
  private $expiresIn = 0;
  public $post = true;
  private $headers = null;
  private  $module = '';
  private $session = '';
  public $url = '';
  private $base_url = 'https://api.wepod.ir/';
  function __construct(string $session=''){
    $this->session = $session;
    $this->settoken();
  }
  function __call(string $method,$arguments){
    return $this->request($this->module.$method,$arguments[0]);
  }
  function __get($name){
    $wepod = $this;
    return new wepodmodule($wepod,$this->module.$name);
  }
  function signup(string $mobileNumber){
    try {
    $this->Auth->signup([]);
    } catch(Throwable $e){
    }
    $this->keyId = $this->headers['request-id'];
    $signup = $this->Auth->signup([
      'deviceId' => md5(microtime()),
      'deviceName' => 'Android Chrome',
      'mobileNumber' => $mobileNumber,
      'latitude' => '0',
      'longitude' => '0',
      'osType' => 'Web_Android',
      'deviceType' => 'Desktop',
      'osVersion' => 'unknown',
      'appVersion' => 'Wepod_web-3.18.0.15.0.Pas',
      'appName' => 'wepod-web',
      'clientIssuer' => true,
    ]);
    file_put_contents(__DIR__.'/wepod/wepod'.$this->session.'.json',json_encode([
      'noverify'=>true,
      'accessToken'=>$this->headers['request-id'],
      'idToken'=>null,
      'expiresIn'=>120,
      'keyId'=>$signup->keyId??'',
      'mobileNumber'=>$mobileNumber,
      ],448));
      return $signup;
  }
  function logout(){
    try {
    if($this->accessToken){
      $this->post = false;
      $result = $this->profile->terminateCurrentSession();
    }
    } catch (Throwable $e) {
    }
    if(file_exists(__DIR__.'/wepod/wepod'.$this->session.'.json')) unlink(__DIR__.'/wepod/wepod'.$this->session.'.json');
    return $result??null;
    
  }
    function verifyotp(int $otpCode){
      $obj = json_decode(file_get_contents(__DIR__.'/wepod/wepod'.$this->session.'.json'));
      try {
    $this->Auth->verifyOtp([]);
    } catch(Throwable $e){
    }
    $this->keyId = $this->headers['request-id'];
      $verify = $this->Auth->verifyOtp([
        'deviceKeyId' => $obj->keyId??'',
        'mobileNumber' => $obj->mobileNumber??'',
        'otpCode' => $otpCode,
        'clientIssuer' => false,
]);
$obj->accessToken = $verify->token->accessToken;
$obj->idToken = $verify->token->idToken;
$obj->expiresIn = 900;
unset($obj->noverify);
file_put_contents(__DIR__.'/wepod/wepod'.$this->session.'.json',json_encode($obj,448));
$this->settoken();
    }
  function settoken(){
    if(!file_exists(__DIR__.'/wepod')) mkdir(__DIR__.'/wepod');
    if(!file_exists(__DIR__.'/wepod/wepod'.$this->session.'.json')) file_put_contents(__DIR__.'/wepod/wepod'.$this->session.'.json','{"noverify": true}');
    $obj = json_decode(file_get_contents(__DIR__.'/wepod/wepod'.$this->session.'.json'));
    if(!$obj){
      file_put_contents(__DIR__.'/wepod/wepod'.$this->session.'.json','{"noverify": true}');
    }
    if(!$obj || isset($obj->noverify)) return;
    $this->accessToken = $obj->accessToken;
    $this->keyId = $obj->keyId;
    $ft = filemtime(__DIR__.'/wepod/wepod'.$this->session.'.json');
    $this->expiresIn = $ft+$obj->expiresIn;
    $t = $ft+$obj->expiresIn;
    if($t<time()) $this->refreshToken();
  }
  function refreshToken(){
    try {
    $data = $this->request('Auth.refreshToken',['accessToken' => $this->accessToken],true);
    }catch (Throwable $e) {
    $this->logout();
  $this->accessToken = '';
  $this->keyId = '';
  $this->expiresIn = 0;
  return;
    }
    try {
    if($data) file_put_contents(__DIR__.'/wepod/wepod'.$this->session.'.json',json_encode($data,448));
    } catch(Throwable $e){
      if($e->getcode() == 429) {
        sleep(30);
        return $this->refreshToken();
      }else{
        throw new exception($e->getmessage(),$e->getcode());
      }
    }
    $this->accessToken = $data->accessToken;
    $this->keyId = $data->keyId;
    $this->expiresIn = time()+$data->expiresIn;
    return $data;
  }
  function request($method,$datas=[],$debug=false){
    
    if($this->expiresIn>0 && $this->expiresIn<time() && !$debug) $this->settoken();
    $url = $this->base_url.(!$this->url?'api/':$this->url);
    $url .= str_replace(['.','_'],'/',$method);
    if(!$this->post && !empty($datas)){
      $url .= '?'.http_build_query($datas);
      
      $datas = [];
    }
    $headers = [
    'sec-ch-ua: "Not-A.Brand";v="99", "Chromium";v="124"',
    'Accept-Language: fa-IR',
    'X-Client-Version: Wepod_web-3.18.0.4.Pas',
    'sec-ch-ua-mobile: ?1',
    'Authorization: '.$this->accessToken,
    'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
    'Content-Type: application/json',
    'Accept: application/json, text/plain, */*',
    'Cache-Control: no-cache',
    'Referer: https://web.wepod.ir/',
    'Request-Id: '.$this->keyId,
    'sec-ch-ua-platform: "Android"'
];
    $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
   if(!empty($datas)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datas));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $this->headers = [];
    $my = $this;
    curl_setopt($ch, CURLOPT_HEADERFUNCTION,
  function($ch, $header) use(&$my)
  {
    $len = strlen($header);
   $ex = explode(':',$header,2);
if(!isset($ex[1])) return $len;
    $my->headers[trim(strtolower($ex[0]))] = trim($ex[1]);
    return $len;
  }
);
    $response = curl_exec($ch);
    $er = curl_error($ch);
    if($response !== false){
      $data = json_decode($response);
      
      if(isset($data->detail)){
        if($data->status == 401){
          $this->settoken();
          return $this->request($method,$datas);
        }
        throw new exception($data->detail,$data->status);
      }
      $this->url = '';
      $this->post = true;
      return $data;
    }
    
  }
}
class wepodmodule {
  private $wepod = null;
  private $module = '';
  function __construct($wepod,string $module){
    $this->wepod = $wepod;
    $this->module = $module.'.';
  }
  function __call(string $method,$arguments){
    return $this->wepod->request($this->module.$method,$arguments[0]??[]);
  }
  function __get($name){
    
    return new wepodmodule($this->wepod,$this->module.$name);
  }
}