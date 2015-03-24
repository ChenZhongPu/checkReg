<?php
/**
 * Created by PhpStorm.
 * User: chenzhongpu
 * Date: 23/3/15
 * Time: 11:26 PM
 */

class QueryUtil {

    var $phone;
    var $email;

    const mailReg = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
    const phoneReg = '/^1\d{10}$/';

    var $isPhone;

    var $isValid;

    var $output;

    function __construct($phoneOrMail){

        if (preg_match(self::mailReg, $phoneOrMail)){
             $this ->isPhone = false;
             $this ->isValid = true;
             $this ->email = $phoneOrMail;
        }elseif(preg_match(self::phoneReg, $phoneOrMail)){
            $this ->isPhone = true;
            $this ->isValid = true;
            $this ->phone = $phoneOrMail;
        }else{
            $this ->isValid = false;
        }

        $this->output = array();

    }


   public function check(){
        if ($this ->isValid){

           $this ->travelJsonFile();
        }

        return $this ->output;
    }

    function travelJsonFile(){

        if ($handle = opendir('plugins')){
            $ch = curl_init();
            while (false !== ($entry = readdir($handle))){
                if (!is_dir($entry)){
                   // echo "$entry<br>";
                    // 对每个json文件读取并请求
                    $jsonFile = file_get_contents("plugins/".$entry);
                    $obj = json_decode($jsonFile, true);

                    if ($obj['method'] == 'post'){
                       if ($this ->isPhone){
                           // post , phone

                           if ('' == $obj['request']['phoneUrl'])
                               continue;
                           curl_setopt($ch, CURLOPT_URL,$obj['request']['phoneUrl']);
                           curl_setopt($ch, CURLOPT_POST, true);
                           $data = array($obj['postField']['phoneField'] => $this ->phone);
                           curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                           $result = curl_exec($ch);
                           if ($result == $obj['yesCode']['phoneCode']){

                               array_push($this ->output, $obj);
                           }elseif(strlen($obj['yesCode']['phoneCode']) > 5){

                               if (strpos($result,$obj['yesCode']['phoneCode']) !== false){
                                   array_push($this ->output, $obj);
                               }
                           }
                       }else{

                           if ('' == $obj['request']['mailUrl']){
                               continue;
                           }
                          // post, mail
                           curl_setopt($ch,CURLOPT_URL,$obj['request']['mailUrl']);
                           curl_setopt($ch, CURLOPT_POST, true);
                           $data = array($obj['postField']['mailField'] => $this ->email);
                           curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                           $result = curl_exec($ch);
                           if ($result == $obj['yesCode']['mailCode']){
                               array_push($this ->output, $obj);
                           }elseif(strlen($obj['yesCode']['mailCode']) > 5){
                               if(strpos($result,$obj['yesCode']['mailCode']) !== false){
                                   array_push($this ->output, $obj);
                               }
                           }

                       }

                    }elseif($obj['method'] == 'get'){

                        // get, phone
                        if ($this ->isPhone){

                            if ('' == $obj['request']['phoneUrl']){
                                continue;
                            }
                            curl_setopt($ch, CURLOPT_URL, str_replace("{}",$this ->phone, $obj['request']['phoneUrl']));
                            curl_setopt($ch, CURLOPT_POST, false);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            $result = curl_exec($ch);
                            if ($result == $obj['yesCode']['phoneCode']){
                                array_push($this ->output, $obj);
                            }elseif(strlen($obj['yesCode']['phoneCode']) > 5){

                                if (strpos($result,$obj['yesCode']['phoneCode']) !== false){
                                    array_push($this ->output, $obj);
                                }
                            }

                        }else{

                            if ('' == $obj['request']['mailUrl']){
                                continue;
                            }
                            curl_setopt($ch, CURLOPT_URL, str_replace("{}",$this ->email, $obj['request']['mailUrl']));
                            curl_setopt($ch, CURLOPT_POST, false);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            $result = curl_exec($ch);

                            if ($result == $obj['yesCode']['mailCode']){
                                array_push($this ->output, $obj);
                            }elseif(strlen($obj['yesCode']['mailCode']) > 5){

                                if(strpos($result,$obj['yesCode']['mailCode']) !== false){
                                    array_push($this ->output, $obj);

                                }
                            }
                        }

                    }
                }
            }
            curl_close($ch);
            closedir($handle);
        }

    }


}


$query = new QueryUtil("2453085348@qq.com");

echo count($query ->check());

foreach ($query ->output as $value){
    echo $value['name']."<br>";
}