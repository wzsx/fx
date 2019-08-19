<?php
namespace App\Http\Controllers\Pub;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class PubController extends Controller{
    public function pub(){
        $enc_str=file_get_contents("php://input");
        echo $enc_str;
        //echo '<hr>';
        //解密
        $pk=openssl_get_publickey('file://key/pub.pem');
        //echo $pk;die;
        openssl_public_decrypt($enc_str,$dnc_data,$pk);
        echo '<hr>';
        echo $dnc_data;
    }

}