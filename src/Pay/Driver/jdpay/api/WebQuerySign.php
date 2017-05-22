<?php

namespace wepay\join\demo\api;

use wepay\join\demo\common\RSAUtils;
use wepay\join\demo\common\TDESUtil;
use wepay\join\demo\common\ConfigUtil;
use wepay\join\demo\common\HttpUtils;

include '../common/RSAUtils.php';
include '../common/TDESUtil.php';
include '../common/ConfigUtil.php';
include '../common/HttpUtils.php';

/**
 * 交易查询-验签
 *
 * @author wylitu
 *
 */
class WebQuerySign
{

    public function query()
    {
        $params = $this->prepareParms();
        $data = json_encode($params);
        list ($return_code, $return_content) = HttpUtils:: http_post_data(ConfigUtil::get_val_by_key("serverQueryUrl"), $data);
        $return_content = str_replace("\n", '', $return_content);
        $return_data = json_decode($return_content, true);
        // 执行状态 成功
        $_SESSION ['errorMsg'] = null;
        $_SESSION ['queryDatas'] = null;

        if ($return_data['resultCode'] == 0) {
            $mapResult = $return_data['resultData'];
            // 有返回数据
            if (null != $mapResult) {
                $data = $mapResult["data"];
                $sign = $mapResult["sign"];

                // 1.解密签名内容
                $decryptStr = RSAUtils::decryptByPublicKey($sign);

                // 2.对data进行sha256摘要加密
                $sha256SourceSignString = hash("sha256", $data);

                // 3.比对结果
                if ($decryptStr == $sha256SourceSignString) {
                    /**
                     * 验签通过
                     */
                    // 解密data
                    $decrypData = TDESUtil::decrypt4HexStr(base64_decode(ConfigUtil::get_val_by_key("desKey")), $data);
                    // 注意 结果为List集合
                    $decrypData = json_decode($decrypData, true);
                    //var_dump($decrypData);
                    // 错误消息
                    if (count($decrypData) < 1) {
                        $_SESSION ['errorMsg'] = decrypData;
                        $_SESSION ['queryDatas'] = null;
                    } else {
                        $_SESSION ['queryDatas'] = $decrypData;
                    }
                } else {
                    /**
                     * 验签失败 不受信任的响应数据
                     * 终止
                     */
                    $_SESSION ['errorMsg'] = "验签失败!";
                }
            }
        }        // 执行查询 失败
        else {
            $_SESSION ['errorMsg'] = $return_data ['resultMsg'];
            $_SESSION ['queryDatas'] = null;
        }

        header("location:../tpl/queryResult.php");

    }

    public function prepareParms()
    {

        $tradeJsonData = "{\"tradeNum\": \"" . $_POST ["tradeNum"] . "\"}";

        // 1.对交易信息进行3DES加密
        $tradeData = TDESUtil::encrypt2HexStr(base64_decode(ConfigUtil::get_val_by_key("desKey")), $tradeJsonData);

        // 2.对3DES加密的数据进行签名
        $sha256SourceSignString = hash("sha256", $tradeData);
        $sign = RSAUtils::encryptByPrivateKey($sha256SourceSignString);

        $params = array();
        $params ["version"] = $_POST ["version"];
        $params ["merchantNum"] = $_POST ["merchantNum"];
        $params ["merchantSign"] = $sign;
        $params ["data"] = $tradeData;
        return $params;
    }
}

$webQuerySign = new WebQuerySign ();
$webQuerySign->query();

?>