<?php
namespace wepay\join\demo\api;

use wepay\join\demo\common\ConfigUtil;
use wepay\join\demo\common\SignUtil;
use wepay\join\demo\common\DesUtils;

session_start();
include '../common/SignUtil.php';
include '../common/DesUtils.php';
include '../common/ConfigUtil.php';

/**
 * 模拟支付-商户签名
 * @author wylitu
 *
 */
class WebPaySign
{

    public function  execute()
    {

        $param = array();
        $param["currency"] = $_POST["currency"];
        $param["failCallbackUrl"] = $_POST["failCallbackUrl"];
        $param["merchantNum"] = $_POST["merchantNum"];
        $param["merchantRemark"] = $_POST["merchantRemark"];
        $param["notifyUrl"] = $_POST["notifyUrl"];
        $param["successCallbackUrl"] = $_POST["successCallbackUrl"];
        $param["tradeAmount"] = $_POST["tradeAmount"];
        $param["tradeDescription"] = $_POST["tradeDescription"];
        $param["tradeName"] = $_POST["tradeName"];
        $param["tradeNum"] = $_POST["tradeNum"];
        $param["tradeTime"] = $_POST["tradeTime"];
        $param["version"] = $_POST["version"];
        $param["token"] = $_POST["token"];

        $sign = SignUtil::sign($param);
        //print_r($param);exit();
        $param["merchantSign"] = $sign;
        if ($_POST["version"] == "1.0") {
            //敏感信息未加密
        } else if ($_POST["version"] == "2.0") {
            //敏感信息加密
            //获取商户 DESkey
            //对敏感信息进行 DES加密
            $desUtils = new DesUtils();
            $key = ConfigUtil::get_val_by_key("desKey");
            $param["merchantRemark"] = $desUtils->encrypt($param["merchantRemark"], $key);
            $param["tradeNum"] = $desUtils->encrypt($param["tradeNum"], $key);
            $param["tradeName"] = $desUtils->encrypt($param["tradeName"], $key);
            $param["tradeDescription"] = $desUtils->encrypt($param["tradeDescription"], $key);
            $param["tradeTime"] = $desUtils->encrypt($param["tradeTime"], $key);
            $param["tradeAmount"] = $desUtils->encrypt($param["tradeAmount"], $key);
            $param["currency"] = $desUtils->encrypt($param["currency"], $key);
            $param["notifyUrl"] = $desUtils->encrypt($param["notifyUrl"], $key);
            $param["successCallbackUrl"] = $desUtils->encrypt($param["successCallbackUrl"], $key);
            $param["failCallbackUrl"] = $desUtils->encrypt($param["failCallbackUrl"], $key);
        }
        $_SESSION['tradeAmount'] = $_POST["tradeAmount"];
        $_SESSION['tradeName'] = $_POST["tradeName"];
        $_SESSION['tradeInfo'] = $param;
        header("location:../tpl/paySubmit.php");
    }
}

$webPaySign = new WebPaySign();
$webPaySign->execute();


?>