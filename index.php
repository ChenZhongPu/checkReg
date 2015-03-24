<?php
echo '<strong>Hello, SAE!</strong>';
$tex1 = "lean";
$cars  = array("Volvo","BMW");
echo "my car is $cars[0]";

//
$ch = curl_init();
////curl_setopt($ch, CURLOPT_URL, "https://passport.58.com/ajax/checkemail?email=18789482356%40163.com");
curl_setopt($ch, CURLOPT_URL, "https://zhuce.xunlei.com/register?m=check_mail&mail=18789482356&cache=1427173362314&jsoncallback=jsonp1427173334747");
//
//curl_setopt($ch, CURLOPT_URL,"https://login.dangdang.com/p/email_checker.php");
//curl_setopt($ch, CURLOPT_POST, true);
//$data = array('email' => '18789482356@163.com');
//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
////curl_setopt($ch, CURLOPT_HTTPHEADER,array('Host:passport.58.com'));
////curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6');
curl_exec($ch);

