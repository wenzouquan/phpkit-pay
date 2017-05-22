<?php
namespace phpkit\pay\Pay\Driver;

class Alipayensure extends \phpkit\pay\Pay\Pay {

	protected $gateway = 'https://mapi.alipay.com/gateway.do';
	protected $verify_url = 'http://notify.alipay.com/trade/notify_query.do';
	protected $config = array(
		'email' => '',
		'key' => '',
		'partner' => '',
	);

	public function check() {
		if (!$this->config['email'] || !$this->config['key'] || !$this->config['partner']) {
			E("支付宝设置有误！");
		}
		return true;
	}

	public function buildRequestForm(\phpkit\pay\Pay\PayVo $vo) {
		/*
			         //物流支付方式
			         //物流费用
			         $logistics_fee = "0.00";
			         //必填，即运费
			         //物流类型
			         $logistics_type = "EXPRESS";
			         //必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
			         //物流支付方式
			         $logistics_payment = "SELLER_PAY";
			         //必填，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
			         //订单描述
		*/
		$getParam = $vo->getParam();
		$param = array(
			"service" => "create_partner_trade_by_buyer",
			"partner" => trim($this->config['partner']),
			"payment_type" => 1,
			"notify_url" => $this->config['notify_url'],
			"return_url" => $this->config['notify_url'],
			"seller_email" => $this->config['email'],
			"out_trade_no" => $vo->getOrderNo(),
			"subject" => $vo->getTitle(),
			"price" => $vo->getFee(),
			"quantity" => 1,
			"logistics_fee" => $getParam['logistics_fee'],
			"logistics_type" => 'EXPRESS',
			"logistics_payment" => 'BUYER_PAY', //
			"body" => $vo->getBody(),
			"_input_charset" => 'utf-8',
		);
		ksort($param);
		reset($param);
		$arg = '';
		foreach ($param as $key => $value) {
			if ($value) {
				$arg .= "$key=$value&";
			}
		}

		$param['sign'] = md5(substr($arg, 0, -1) . $this->config['key']);
		$param['sign_type'] = 'MD5';

		$sHtml = $this->_buildForm($param, $this->gateway, 'get');

		return $sHtml;
	}

	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
	 * @return 签名验证结果
	 */
	protected function getSignVeryfy($param, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$param_filter = array();
		while (list($key, $val) = each($param)) {
			if ($key == "sign" || $key == "sign_type" || $val == "") {
				continue;
			} else {
				$param_filter[$key] = $param[$key];
			}
		}

		ksort($param_filter);
		reset($param_filter);

		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = "";
		while (list($key, $val) = each($param_filter)) {
			$prestr .= $key . "=" . $val . "&";
		}
		//去掉最后一个&字符
		$prestr = substr($prestr, 0, -1);

		$prestr = $prestr . $this->config['key'];
		$mysgin = md5($prestr);

		if ($mysgin == $sign) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 针对notify_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */
	public function verifyNotify($notify) {

		//生成签名结果
		$isSign = $this->getSignVeryfy($notify, $notify["sign"]);
		//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
		$responseTxt = 'true';
		if (!empty($notify["notify_id"])) {
			$responseTxt = $this->getResponse($notify["notify_id"]);
		}

		if (preg_match("/true$/i", $responseTxt) && $isSign) {
			$this->setInfo($notify);
			return true;
		} else {
			return false;
		}
	}

	protected function setInfo($notify) {
		$info = array();
		//支付状态
		$info['status'] = ($notify['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $notify['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS' || $notify['trade_status'] == 'TRADE_FINISHED' || $notify['trade_status'] == 'TRADE_SUCCESS') ? true : false;
		$info['money'] = $notify['total_fee'];
		$info['out_trade_no'] = $notify['out_trade_no'];
		$this->info = $info;
	}

	/**
	 * 获取远程服务器ATN结果,验证返回URL
	 * @param $notify_id 通知校验ID
	 * @return 服务器ATN结果
	 * 验证结果集：
	 * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
	 * true 返回正确信息
	 * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
	 */
	protected function getResponse($notify_id) {
		$partner = $this->config['partner'];
		$veryfy_url = $this->verify_url . "?partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = $this->fsockOpen($veryfy_url);
		return $responseTxt;
	}

}
