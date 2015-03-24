<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>注册了吗</title>
    <link rel="stylesheet" type="text/css" href="css/semantic.min.css">
    <link rel="icon" href="image/icon.ico">
</head>
<body>

<?php
/**
 * Created by PhpStorm.
 * User: chenzhongpu
 * Date: 23/3/15
 * Time: 11:26 PM
 */

class QueryUtil2 {

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
                if ($entry != "." && $entry != ".."){
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
?>

<?php

$responseResult = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $query = new QueryUtil2($_POST['phoneOrMail']);

    $responseResult = $query ->check();

}else{
    echo "服务器禁止非法访问";
    exit();
}
?>

<div style="padding: 2rem 4rem 0rem 4rem" class="ui segment">

    <h2 class="ui header">

        <div class="content">
            <a href="index.html">返回首页</a>
        </div>
    </h2>
    <div class="ui two column centered grid">
        <div class="column">
            <div class="ui info message">
                <div class="header">
                    免责声明
                </div>
                我们不保证搜索结果的正确性
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                <div class="ui fluid action input">
                    <input type="text" placeholder="手机号或者邮件地址" name="phoneOrMail" id="phoneOrMail" value="">
                    <div class="ui teal button" id="searchBtn">搜索</div>
                </div>
            </form>
            <div class="ui negative message" id="message" style="display: none">
                <div class="header">
                    请输入正确的手机号或邮件地址
                </div>
                <p>手机号(必须以数字1开头且长度为11)或邮件地址不合法
                </p></div>
        </div>
    </div>
        <div class="ui link cards">

            <?php
             if (count($responseResult) == 0){

                 echo '<div class="card">
                <div class="image">
                    <img src="image/empty.png">
                </div>
                <div class="content">
                    <div class="header">没有结果</div>
                    <div class="meta">
                        <a>Not Found</a>
                    </div>
                    <div class="description">
                         我们搜尽了互联网的海洋也没找到您的注册信息
                    </div>
                </div>
                <div class="extra content">
                     <span class="right floated">
                       Since 2015
                     </span>
                     <span>
                    <i class="user icon"></i>
                         404
                     </span>
                </div>
            </div>';
             }
            else{

                foreach ($responseResult as $webSite){

                    $icon = $webSite['icon'];
                    $name = $webSite['name'];
                    $description = $webSite['description'];
                    $url = $webSite['url'];
                    $date = $webSite['date'];
                    $scale = $webSite['scale'];
                    echo "<div class='card'>
                <div class='image'>
                    <img src='$icon'>
                </div>
                <div class='content'>
                    <div class='header'>$name</div>
                    <div class='meta'>
                        <a>$url</a>
                    </div>
                    <div class='description'>
                      $description
                    </div>
                </div>
                <div class='extra content'>
                     <span class='right floated'>
                       Since $date
                     </span>
                     <span>
                    <i class='user icon'></i>
                        $scale
                     </span>
                </div>
            </div>";
                }
            }

            ?>

        </div>
</div>


<div class="ui inverted vertical segment" style="padding: 5rem 5rem 5rem 5rem">
    <div class="ui stackable center aligned page grid">
        <div class="ten wide column">
            <div class="ui three column center aligned stackable grid">
                <div class="column">
                    <h5 class="ui inverted header">特别感谢</h5>
                    <div class="ui inverted animated list">
                        <a class="item" href="https://github.com/ff0000team/Sreg">Sreg项目</a>
                    </div>
                </div>
                <div class="column">
                    <h5 class="ui inverted header">版权所有</h5>
                    <div class="ui inverted animated list">
                        <a class="item">CopyRight&copy;ChenZhongPu</a>
                    </div>
                </div>
                <div class="column">
                    <h5 class="ui inverted header">隐私声明</h5>
                    <div class="ui inverted animated list">
                        <a class="item">我们不记录您的任何信息</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="six wide column">
            <h5 class="ui inverted header">联系我们</h5>
            <div class="ui inverted animated list">
                <a href="mailto:18789482356@163.com" class="item">18789482356@163.com</a>
            </div>
        </div>
    </div>
</div>


<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.js"></script>
<script src="js/semantic.min.js"></script>
<script>
    $(document).ready(function(){
        $("#searchBtn").click(function(){
           $("form").submit()
        });
    });

    $("#phoneOrMail").keyup(function(){
        // 检测有效输入
        var mail =  /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        var phone = /^1\d{10}$/
        if (mail.test($("#phoneOrMail").val()) || phone.test($("#phoneOrMail").val())){
            console.log("valid")
            $("#message").css("display","none")
        }else{
            console.log("invalid")
            $("#message").css("display","block")
        }

    });
</script>
</body>
</html>
