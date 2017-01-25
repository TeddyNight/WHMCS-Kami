<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function kami_config() {
    $configarray = array(
    "name" => "Kami",
    "description" => "此插件可以生成卡密",
    "version" => "1.0",
    "author" => "<a href=\"http://devtan.xyz\" target=\"_blank\" title=\"Dev-Tan\">Dev-Tan</a>",
    "language" => "english",
    "fields" => array(
        #"option1" => array ("FriendlyName" => "用户名", "Type" => "text", "Size" => "25", "Description" => "您WHMCS数据库用户名", "Default" => "root", ),
        #"option3" => array ("FriendlyName" => "数据库名", "Type" => "text", "Size" => "25", "Description" => "您WHMCS数据库的名字", "Default" => "whmcs", ),
        #"option2" => array ("FriendlyName" => "密码", "Type" => "password", "Size" => "25", "Description" => "您WHMCS数据库的密码", ),
        #"option3" => array ("FriendlyName" => "Option3", "Type" => "yesno", "Size" => "25", "Description" => "Sample Check Box", ),
        #"option4" => array ("FriendlyName" => "Option4", "Type" => "dropdown", "Options" => "1,2,3,4,5", "Description" => "Sample Dropdown", "Default" => "3", ),
        #"option5" => array ("FriendlyName" => "Option5", "Type" => "radio", "Options" => "Demo1,Demo2,Demo3", "Description" => "Radio Options Demo", ),
        #"option6" => array ("FriendlyName" => "Option6", "Type" => "textarea", "Rows" => "3", "Cols" => "50", "Description" => "Description goes here", "Default" => "Test", ),
    ));
    return $configarray;
}

function kami_activate() {

    # Create Custom DB Table
    $query = "CREATE TABLE `kami` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `card` text NOT NULL,
  `credit` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;";
    $result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'插件激活成功。');
    #return array('status'=>'error','description'=>'You can use the error status return to indicate there was a problem activating the module');
    #return array('status'=>'info','description'=>'You can use the info status return to display a message to the user');

}

function kami_deactivate() {

    # Remove Custom DB Table
    $query = "DROP TABLE `kami`;";
    $result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'插件停用成功。');
    #return array('status'=>'error','description'=>'If an error occurs you can return an error message for display here');
    #return array('status'=>'info','description'=>'If you want to give an info message to a user you can return it here');

}

function kami_output($vars) {
     echo '﻿<form id="form1" name="form1" method="post" action="?module=kami&action=sc">
  <p>
    <label for="sl"></label>
  生成数量：
  <input type="text" name="sl" id="sl" />
  </p>
  <p>每张卡密金额：
    <label for="value"></label>
    <input type="text" name="value" id="value" />
  </p>
  <p>
    <input type="submit" name="button" id="button" value="生成" />
  </p>
</form>' ;
if ($_GET["action"]=="sc"){
echo "Get Card Successful.<br>";
$sl=$_POST["sl"];
$value=$_POST["value"];
for ($i=1;$i<=$sl;$i++){
	for ($j=1;$j<=32;$j++){
		$kami1=$kami1.chr(rand(65,90));
	}
	$kami=rand(10000,99999)."_".$kami1;
	echo $kami."<br>";
	mysql_query("INSERT INTO kami (card,credit) VALUE ('$kami',$value)");
	$kami="";
	$kami1="";
	}
} 
}

function kami_clientarea($vars) {
if($_POST['card']) {
$uid=$_SESSION["uid"];
$card=$_POST["card"];
$get_credit=mysql_query("SELECT credit FROM kami WHERE card='$card'");
$_get_credit=mysql_fetch_array($get_credit);
if ($_get_credit==""){$kamireturn = "<div class=\"alert alert-danger\" role=\"alert\">您输入的卡密有误，请检查修改后再次尝试提交。</div>";} else {
$delete_mysql=mysql_query("DELETE FROM kami WHERE card='$card'");
$date=date("Y-m-d");
//echo "充值uid:".$uid."<br>";
//echo "充值日期:".$date."<br>";
//echo "充值金额:".$_get_credit[0]."<br>";
$insert_credit_info=mysql_query("INSERT INTO tblcredit (clientid,date,description,amount,relid) VALUES($uid,$date,'卡密充值',$_get_credit[0],0)");
$insert_credit=mysql_query("UPDATE tblclients SET credit=credit+$_get_credit[0] WHERE id=$uid");
if ($insert_credit_info){$kamireturn = "<div class=\"alert alert-success\" role=\"alert\">已成功充值</div>";}else{$kamireturn = "<div class=\"alert alert-danger\" role=\"alert\">充值失败,请重试,如有任何问题请发工单。</div>";}
}}
    return array(
        'pagetitle' => '卡密充值/卡密支付系统',
        'breadcrumb' => array('index.php?m=kami'=>'Kami'),
        'templatefile' => 'clienthome',
        'requirelogin' => true, # or false
        'vars' => array(
             'kamireturn' => $kamireturn,
        ),
    );
 
}
?>
