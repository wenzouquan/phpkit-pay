<?php
namespace phpkit\pay\Pay\Driver;

use phpkit\pay\Pay\Driver\jdpay\common\DesUtils;
use phpkit\pay\Pay\Driver\jdpay\common\SignUtil;

include 'jdpay/common/SignUtil.php';
include 'jdpay/common/DesUtils.php';
include 'jdpay/common/ConfigUtil.php';

class Jdpay extends \phpkit\pay\Pay\Pay {

	function __construct($config = array()) {
		$config['successCallbackUrl'] = $config['return_url'];
		$config['failCallbackUrl'] = "http://mall." . DomainName . "/User/";
		$config['notifyUrl'] = $config['notify_url'];
		$config['serverPayUrl'] = "https://m.jdpay.com/wepay/web/pay"; //网银支付服务地址
		$config['md5Key'] = "test";
		$config['serverQueryUrl'] = "https://m.jdpay.com/wepay/query";
		$config['serverRefundUrl'] = "https://m.jdpay.com/wepay/refund";
		$config['currency'] = "CNY";
		$this->config = $config;
	}

	public function buildRequestForm(\phpkit\pay\Pay\PayVo $vo) {

		$out_trade_no = $vo->getOrderNo();
		//商户网站订单系统中唯一订单号，必填
		//订单名称
		$subject = $vo->getTitle();
		//必填
		$getBody = $vo->getBody();
		//付款金额
		$total_fee = $vo->getFee();
		//用户ID
		$getUserID = $vo->getUserID();
		//必填

		$param = array();
		$param["currency"] = $this->config['currency'];
		$param["failCallbackUrl"] = $this->config['failCallbackUrl'];
		$param["merchantNum"] = $this->config['merchantNum'];
		$param["merchantRemark"] = $subject;
		$param["notifyUrl"] = $this->config['notifyUrl'];
		$param["successCallbackUrl"] = $this->config['successCallbackUrl'];
		$param["tradeAmount"] = ($total_fee * 100);
		$param["tradeDescription"] = $getBody;
		$param["tradeName"] = $subject;
		$param["tradeNum"] = $out_trade_no;
		$param["tradeTime"] = date('Y-m-d H:i:s');
		$param["version"] = "2.0";
		$param["token"] = "shengshengman_" . $getUserID;
		$sign = SignUtil::sign($param);
		//print_r($param);exit();
		$param["merchantSign"] = $sign;
		//dump($param);exit();
		if ($param["version"] == "1.0") {
			//敏感信息未加密
		} else if ($param["version"] == "2.0") {
			//敏感信息加密
			//获取商户 DESkey
			//对敏感信息进行 DES加密
			$desUtils = new DesUtils();
			$key = $this->config['desKey'];
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

		//dump($param);
		$sHtml = "<form id='alipaysubmit' action='" . $this->config['serverPayUrl'] . "' method='post'>";
		foreach ($param as $key => $val) {
			$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
		}
		//submit按钮控件请不要含有name属性
		$sHtml = $sHtml . "<input type='submit' ></form>";
		$sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
		return $sHtml;
	}

	/**
	 * 针对notify_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */

	public function verifyNotify($notify) {
		$desKey = $this->config['desKey'];
		$md5Key = $this->config['md5Key'];
		return $this->execute($md5Key, $desKey, $notify);
	}

	public function xml_to_array($xml) {
		$array = (array) (simplexml_load_string($xml));
		foreach ($array as $key => $item) {
			$array[$key] = $this->struct_to_array((array) $item);
		}
		return $array;
	}

	public function struct_to_array($item) {
		if (!is_string($item)) {
			$item = (array) $item;
			foreach ($item as $key => $val) {
				$item[$key] = $this->struct_to_array($val);
			}
		}
		return $item;
	}

	/**
	 * 签名
	 */
	public function generateSign($data, $md5Key) {
		$sb = $data['VERSION'][0] . $data['MERCHANT'][0] . $data['TERMINAL'][0] . $data['DATA'][0] . $md5Key;

		return md5($sb);
	}

	public function execute($md5Key, $desKey, $resp) {

		$resp = $resp['resp'];
		if (null == $resp) {
			return;
		}
		// 解析XML
		//dump(base64_decode ( $resp ));exit();
		$params = $this->xml_to_array(base64_decode($resp));
		$ownSign = $this->generateSign($params, $md5Key);
		$params_json = json_encode($params);

		if ($params['SIGN'][0] != $ownSign) {
			$this->logpay("京东支付签名验证错误!" . "\n");
			return;
		}
		// 验签成功，业务处理
		// 对Data数据进行解密
		$des = new DesUtils(); // （秘钥向量，混淆向量）
		$decryptArr = $des->decrypt($params['DATA'][0], $desKey); // 加密字符串
		//	$this->logpay( "对<DATA>进行解密得到的数据:" . $decryptArr . "\n");
		$params['data'] = $this->xml_to_array($decryptArr);
		//dump($params);exit();
		return $this->setInfo($params['data']);
	}

	function logpay($str) {
		BoxModel("test")->add(array('test' => $str)); //测试可以删除
	}

	protected function setInfo($notify) {
		$info['status'] = ($notify['TRADE']['STATUS'] === '0') ? true : false;
		$info['money'] = $notify['TRADE']['AMOUNT'];
		$info['out_trade_no'] = $notify['TRADE']['ID'];
		$info['order_sn'] = $notify['TRADE']['ID'];
		$info['status'] = 1;
		$this->info = $info;
		return $info['status'];
	}

}
