<?php

namespace wepay\join\demo\api;

use wepay\join\demo\common\DesUtils;
use wepay\join\demo\common\ConfigUtil;

include '../common/DesUtils.php';
include '../common/ConfigUtil.php';

/**
 * 接收异步通知控制器
 *
 * @author wylitu
 *
 */
class WebAsynNotificationCtrl
{

    public function xml_to_array($xml)
    {
        $array = (array)(simplexml_load_string($xml));
        foreach ($array as $key => $item) {
            $array[$key] = $this->struct_to_array((array)$item);
        }
        return $array;
    }

    public function struct_to_array($item)
    {
        if (!is_string($item)) {
            $item = (array)$item;
            foreach ($item as $key => $val) {
                $item [$key] = $this->struct_to_array($val);
            }
        }
        return $item;
    }

    /**
     * 签名
     */
    public function generateSign($data, $md5Key)
    {
        $sb = $data ['VERSION'] [0] . $data ['MERCHANT'] [0] . $data ['TERMINAL'] [0] . $data ['DATA'] [0] . $md5Key;

        return md5($sb);
    }

    public function execute($md5Key, $desKey, $resp)
    {
        // 获取通知原始信息
        echo "异步通知原始数据:" . $resp . "\n";
        if (null == $resp) {
            return;
        }

        // 获取配置密钥
        echo "desKey:" . $desKey . "\n";
        echo "md5Key:" . $md5Key . "\n";
        // 解析XML
        $params = $this->xml_to_array(base64_decode($resp));

        $ownSign = $this->generateSign($params, $md5Key);
        $params_json = json_encode($params);
        echo "解析XML得到对象:" . $params_json . '\n';
        echo "根据传输数据生成的签名:" . $ownSign . "\n";

        if ($params ['SIGN'] [0] == $ownSign) {
            // 验签不对
            echo "签名验证正确!" . "\n";
        } else {
            echo "签名验证错误!" . "\n";
            return;
        }
        // 验签成功，业务处理
        // 对Data数据进行解密
        $des = new DesUtils (); // （秘钥向量，混淆向量）
        $decryptArr = $des->decrypt($params ['DATA'] [0], $desKey); // 加密字符串
        echo "对<DATA>进行解密得到的数据:" . $decryptArr . "\n";
        $params ['data'] = $decryptArr;
        echo "最终数据:" . json_encode($params) . '\n';
        echo "**********接收异步通知结束。**********";

        return;
    }
}

$MD5_KEY = ConfigUtil::get_val_by_key("md5Key");
$DES_KEY = ConfigUtil::get_val_by_key("desKey");
$w = new WebAsynNotificationCtrl ();
$w->execute($MD5_KEY, $DES_KEY, $_POST ("resp"));


/**
 * 测试列子
 */
//start
//$testString = "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4NCjxDSElOQUJBTks+DQogICAg\n
PFZFUlNJT04 + MS4yLjA8L1ZFUlNJT04 + DQogICAgPE1FUkNIQU5UPjIyMzEyNzgxPC9NRVJDSEFO\n
            VD4NCiAgICA8VEVSTUlOQUw + MDAwMDAwMDE8L1RFUk1JTkFMPg0KICAgIDxEQVRBPkJiQ2NzeVUx\n
            L3kyMjlIeXZ4RFQ1Rm1SVnVjSFRXUFJqaGhyWmViRW1wTVAvL2xjdW04cjBRdVhGcjZPeDY5NXdN\n
            dEYwblMwWHhjYVYKOVNoMWdrYU16dVJHQXorcytjOWhYREVnMUFXNVNUY3lRM0c3UTZKdzlBajJM\n
            V0ZpelQ5cUNRYXVqT1FQT3RPWjIyRWF2RzZzNVJYLwo4c3AzYlJKa3hKNnpDYlQ3ckw0anNJZm9G\n
            T05BdDBIV1VYUUpiazRBa3NlK1d3emNybU5QUDVzMVcyckRPUnA5Z3cwcVVhVW9DZmdNCk5LSGNY\n
            Ymx2ZVZlVmNZeHlBMHJrRk9xNnFIV0tybEhqRGdqVWl0dFJZQU9CYlA0TDJDSTVvK3dMQzNpV1Z5\n
            NmVpTHd2QnNJeWM3amwKU2NhanNaTkgxeDlPbUhUVitXWFA1ejBlejdYb0U1SGJUaDBmckdaeERZ\n
            U2wwZlQvMnkvUDFpbnlrVUpwQktiVnA3c2w4NVVyZjVTcgpZZGM0VzA0QXdJajI0NnBpOW1KUHU2\n
            d0w2bG5VV24zdXpjT2xDRUxpWkJ6OTJueXI3anlYeXIzR05Ha0VwdFdudXlYenhXV3AzMW8zCmNm\n
            MFkwWTMrMGJjbm5BPT08L0RBVEE + DQogICAgPFNJR04 + YzhhYTc1NGVmZjQ5MzIyNmYzNzU4NTJk\n
            MGFmNTlmMmU8L1NJR04 + DQo8L0NISU5BQkFOSz4NCg0K";

//$webAsynNotificationCtrl = new WebAsynNotificationCtrl();
//$webAsynNotificationCtrl->execute("test","Z8KMT8cT4z5ruu89znxFhRP4DdDBqLUH",$testString);//end


?>




















