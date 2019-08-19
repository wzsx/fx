<?php
namespace  App\Http\Controllers\User;
use App\Model\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class UserController extends Controller{
    public function reg(Request $request){
        $name=$request->input('name');
        $pass1=$request->input('pass1');
        $pass2=$request->input('pass2');
        if($pass1!=$pass2){
            $response=[
                'errno'=>50002,
                'msg'=>'两次输入的密码不一致'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        $res=UserModel::where(['u_name'=>$name])->first();
        if($res){
            $response=[
                'errno'=>50004,
                'msg'=>'用户已存在'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        //密码加密处理
        $pass=password_hash($pass1,PASSWORD_BCRYPT);
        $data=[
            'u_name'=>$name,
            'u_pass'=>$pass,
            'add_time'=>time()
        ];
        //加密数据
        $json_str=json_encode($data);
        $k=openssl_get_privatekey('file://key/priv.key');
        openssl_private_encrypt($json_str,$enc_data,$k);
        var_dump($enc_data);
        $api_url='http://cl.com/pub';
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$api_url);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$enc_data);
        curl_setopt($ch,CURLOPT_HTTPHEADER,[
            'Content-type:text/plain'
        ]);
        $response=curl_exec($ch);
        //监控错误码
        $err_code=curl_errno($ch);
        //var_dump($err_code);
        if($err_code>0){
            echo "CURL 错误码：".$err_code;exit;
        }
        curl_close($ch);
        $uid=UserModel::insertGetId($data);
        if($uid){
            //TODO
            $response=[
                'errno'=>0,
                'msg'=>'注册成功'
            ];
        }else{
            //TODO
            $response=[
                'errno'=>50003,
                'msg'=>'注册用户失败'
            ];

        }
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
public function login(Request $request){
    $name=$request->input('name');
    $pass=$request->input('pass');
    $data=[
        'name' =>$name,
        'pass'  =>$pass
    ];
    //对称加密数据
    $method ='AES-256-CBC';
    $key='yufsfs';
    //$option= OPENSSL_RAW_DATA;
    $iv ='1234567890asdfgh';
    $dd=json_encode($data);
    $send_data=base64_encode(openssl_encrypt($dd,$method,$key,OPENSSL_RAW_DATA,$iv));
    echo $send_data;echo '<hr>';
    $api_url='http://cl.com/duic';
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$api_url);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$send_data);
    curl_setopt($ch,CURLOPT_HTTPHEADER,[
        'Content-type:text/plain'
    ]);
    $response=curl_exec($ch);
    var_dump($response);
    //监控错误码
    $err_code=curl_errno($ch);
    //var_dump($err_code);
    if($err_code>0){
        echo "CURL 错误码：".$err_code;exit;
    }
    curl_close($ch);
    $res=UserModel::where(['u_name'=>$name])->first();
    if($res){      //用户存在
        if(password_verify($pass,$res->u_pass)){  //验证密码
            //TODO 登录逻辑
            $token=$this->UserToken($res->uid);
            $redis_token_key='user_token:uid:'.$res->uid;
            Redis::set($redis_token_key,$token);
            Redis::expire($redis_token_key,604800);
            //生成token
            $response=[
                'errno'=>0,
                'msg'=>'登录成功',
                'data'=>[
                    'token'=>$token
                ]
            ];
        }else{
            //TODO 登录失败
            $response=[
                'errno'=>50010,
                'msg'=>'密码不正确'
            ];
        }
        die(json_encode($response,JSON_UNESCAPED_UNICODE));
    }else{       //用户不存在
        $response=[
            'errno'=>50011,
            'msg'=>'用户不存在'
        ];
    }
    return (json_encode($response,JSON_UNESCAPED_UNICODE));
}
    protected function UserToken($uid){
        return substr(sha1($uid.time().Str::random(10)),5,15);
    }
//    public function key(){
//        $k=openssl_get_privatekey('file://key/priv.key');
//        echo $k;
//        print_r($k);
////        var_dump($k);
//    }
}