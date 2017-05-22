<?php
namespace phpkit\pay\Pay\Driver;
require_once COMMON_PATH . "/Org/Pay/Driver/wapalipay/lib/alipay_core.function.php";
require_once COMMON_PATH . "/Org/Pay/Driver/wapalipay/lib/alipay_rsa.function.php";
require_once COMMON_PATH . "/Org/Pay/Driver/wapalipay/lib/alipay_md5.function.php";

class Wapalipay extends \phpkit\pay\Pay\Pay {
	protected $gateway = 'http://wappaygw.alipay.com/service/rest.htm?';
	var $alipay_gateway_new = 'http://wappaygw.alipay.com/service/rest.htm?';
	protected $config = array(
		'email' => '',
		'key' => '',
		'partner' => '',
	);
	/**
	 * HTTPS形式消息验证地址
	 */
	var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
	 * HTTP形式消息验证地址
	 */
	var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
	var $alipay_config;

	function __construct($alipay_config = array()) {
		$alipay_config = array_merge($this->config, $alipay_config);
		$alipay_config['private_key_path'] = APP_PATH . 'Common/Org/Pay/Driver/wapalipay/key/rsa_private_key.pem';
		$alipay_config['ali_public_key_path'] = APP_PATH . 'Common/Org/Pay/Driver/wapalipay/key/alipay_public_key.pem';
		$alipay_config['input_charset'] = 'utf-8';
		$alipay_config['sign_type'] = 'MD5';
		$alipay_config['transport'] = 'http';
		//$alipay_config['cacert']    = APP_PATH.'http';
		$this->alipay_config = $alipay_config;
	}

	function AlipayNotify($alipay_config) {
		$alipay_config = $this->config;
		$alipay_config['private_key_path'] = APP_PATH . 'Common/Org/Pay/Driver/wapalipay/key/rsa_private_key.pem';
		$alipay_config['ali_public_key_path'] = APP_PATH . 'Common/Org/Pay/Driver/wapalipay/key/alipay_public_key.pem';
		$alipay_config['input_charset'] = 'utf-8';
		$alipay_config['sign_type'] = 'MD5';
		$alipay_config['transport'] = 'http';
		$this->__construct($alipay_config);
	}

	public function check() {
		if (!$this->config['email'] || !$this->config['key'] || !$this->config['partner']) {
			E("支付宝设置有误！");
		}
		return true;
	}

	public function buildRequestForm(\phpkit\pay\Pay\PayVo $vo) {
		//返回格式
		$format = "xml";
		//必填，不需要修改
		//返回格式
		$v = "2.0";
		//必填，不需要修改
		//请求号
		$req_id = date('Ymdhis');
		//必填，须保证每次请求都是唯一
		//**req_data详细信息**
		//服务器异步通知页面路径
		$notify_url = $this->alipay_config['notify_url'];
		//需http://格式的完整路径，不允许加?id=123这类自定义参数

		//页面跳转同步通知页面路径
		$call_back_url = $this->alipay_config['return_url'];
		//需http://格式的完整路径，不允许加?id=123这类自定义参数

		//操作中断返回地址
		$merchant_url = "http://mall." . DomainName . "/User/";
		//用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数
		//卖家支付宝帐户
		$seller_email = $this->alipay_config['email'];
		//必填
		//商户订单号
		$out_trade_no = $vo->getOrderNo();
		//商户网站订单系统中唯一订单号，必填
		//订单名称
		$subject = $vo->getTitle();
		//必填

		//付款金额
		$total_fee = $vo->getFee();
		//必填

		//请求业务参数详细
		$req_data = "<direct_trade_create_req><notify_url>{$notify_url}</notify_url><call_back_url>{$call_back_url}</call_back_url><seller_account_name>{$seller_email}</seller_account_name><out_trade_no>{$out_trade_no}</out_trade_no><subject>{$subject}</subject><total_fee>{$total_fee}</total_fee><merchant_url>{$merchant_url}</merchant_url></direct_trade_create_req>";
		//echo($total_fee );exit();
		//构造要请求的参数数组，无需改动
		$para_token = array(
			"service" => "alipay.wap.trade.create.direct",
			"partner" => trim($this->alipay_config['partner']),
			"sec_id" => trim($this->alipay_config['sign_type']),
			"format" => $format,
			"v" => $v,
			"req_id" => $req_id,
			"req_data" => $req_data,
			"_input_charset" => trim(strtolower($this->alipay_config['input_charset'])),
		);

		//建立请求
		$html_text = $this->buildRequestHttp($para_token);

		//URLDECODE返回的信息
		$html_text = urldecode($html_text);

		//解析远程模拟提交后返回的信息
		$para_html_text = $this->parseResponse($html_text);

		//获取request_token
		$request_token = $para_html_text['request_token'];

		//dump($this->alipay_config);exit();
		/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
		//业务详细
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		//必填

		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service" => "alipay.wap.auth.authAndExecute",
			"partner" => trim($this->alipay_config['partner']),
			"sec_id" => trim($this->alipay_config['sign_type']),
			"format" => $format,
			"v" => $v,
			"req_id" => $req_id,
			"req_data" => $req_data,
			"_input_charset" => trim(strtolower($this->alipay_config['input_charset'])),
		);
		$para = $this->buildRequestPara($parameter);
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->gateway . "_input_charset=utf-8' method='get'>";
		while (list($key, $val) = each($para)) {
			$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
		}
		//submit按钮控件请不要含有name属性
		$sHtml = $sHtml . "<input type='submit' value='" . $button_name . "'></form>";
		$sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
		return $sHtml;
	}

	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);

		$mysign = "";
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
		case "MD5":
			$mysign = md5Sign($prestr, $this->alipay_config['key']);
			break;
		case "RSA":
			$mysign = rsaSign($prestr, $this->alipay_config['private_key_path']);
			break;
		case "0001":
			$mysign = rsaSign($prestr, $this->alipay_config['private_key_path']);
			break;
		default:
			$mysign = "";
		}

		return $mysign;
	}

	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组
	 */
	function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);
		//对待签名参数数组排序
		$para_sort = argSort($para_filter);
		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		if ($para_sort['service'] != 'alipay.wap.trade.create.direct' && $para_sort['service'] != 'alipay.wap.auth.authAndExecute') {
			$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
		}

		return $para_sort;
	}

	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组字符串
	 */
	function buildRequestParaToString($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);

		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = createLinkstringUrlencode($para);

		return $request_data;
	}

	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果
	 * @param $para_temp 请求参数数组
	 * @return 支付宝处理结果
	 */
	function buildRequestHttp($para_temp) {
		$sResult = '';

		//待请求参数数组字符串
		$request_data = $this->buildRequestPara($para_temp);

		//远程获取数据
		$sResult = getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'], $request_data, trim(strtolower($this->alipay_config['input_charset'])));
		return $sResult;
	}

	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果，带文件上传功能
	 * @param $para_temp 请求参数数组
	 * @param $file_para_name 文件类型的参数名
	 * @param $file_name 文件完整绝对路径
	 * @return 支付宝返回处理结果
	 */
	function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {

		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$para[$file_para_name] = "@" . $file_name;

		//远程获取数据
		$sResult = getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'], $para, trim(strtolower($this->alipay_config['input_charset'])));

		return $sResult;
	}

	/**
	 * 解析远程模拟提交后返回的信息
	 * @param $str_text 要解析的字符串
	 * @return 解析结果
	 */
	function parseResponse($str_text) {
		//以“&”字符切割字符串
		$para_split = explode('&', $str_text);
		//把切割后的字符串数组变成变量与数值组合的数组
		foreach ($para_split as $item) {
			//获得第一个=字符的位置
			$nPos = strpos($item, '=');
			//获得字符串长度
			$nLen = strlen($item);
			//获得变量名
			$key = substr($item, 0, $nPos);
			//获得数值
			$value = substr($item, $nPos + 1, $nLen - $nPos - 1);
			//放入数组中
			$para_text[$key] = $value;
		}

		if (!empty($para_text['res_data'])) {
			//解析加密部分字符串
			if ($this->alipay_config['sign_type'] == '0001') {
				$para_text['res_data'] = rsaDecrypt($para_text['res_data'], $this->alipay_config['private_key_path']);
			}

			//token从res_data中解析出来（也就是说res_data中已经包含token的内容）
			$doc = new \DOMDocument();
			$doc->loadXML($para_text['res_data']);
			$para_text['request_token'] = $doc->getElementsByTagName("request_token")->item(0)->nodeValue;
		}

		return $para_text;
	}

	/**
	 * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
	 * return 时间戳字符串
	 */
	function query_timestamp() {
		$url = $this->alipay_gateway_new . "service=query_timestamp&partner=" . trim(strtolower($this->alipay_config['partner'])) . "&_input_charset=" . trim(strtolower($this->alipay_config['input_charset']));
		$encrypt_key = "";

		$doc = new \DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

		return $encrypt_key;
	}

	/**
	 * 针对notify_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */

	public function verifyNotify($notify) {
		if ($_POST) {
			// BoxModel("test")->where("id=1")->save(array('test'=>"到类里面来测试了"));//测试可以删除
			//对notify_data解密
			$decrypt_post_para = $notify;
			if ($this->alipay_config['sign_type'] == '0001') {
				$decrypt_post_para['notify_data'] = rsaDecrypt($decrypt_post_para['notify_data'], $this->alipay_config['private_key_path']);
			}

			//notify_id从decrypt_post_para中解析出来（也就是说decrypt_post_para中已经包含notify_id的内容）
			$doc = new \DOMDocument();
			$doc->loadXML($decrypt_post_para['notify_data']);
			$notify_id = $doc->getElementsByTagName("notify_id")->item(0)->nodeValue;
			//BoxModel("test")->where("id=1")->save(array('test'=>"notify_id:{$notify_id}"));//测试可以删除
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (!empty($notify_id)) {
				$responseTxt = $this->getResponse($notify_id);
			}
			//BoxModel("test")->add(array('test'=>"responseTxt:".$responseTxt));//测试可以删除
			//生成签名结果
			$isSign = $this->getSignVeryfy($decrypt_post_para, $notify["sign"], false);
			//BoxModel("test")->add(array('test'=>"isSign:".$isSign));//测试可以删除
			if (preg_match("/true$/i", $responseTxt) && $isSign) {
				$this->setInfo($notify);
				return true;
			} else {
				return false;
			}
		} else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($notify, $notify["sign"], true);
			if ($isSign) {
				$this->setInfo($notify);
				return true;
			} else {
				return false;
			}
		}

	}

	/**
	 * 解密
	 * @param $input_para 要解密数据
	 * @return 解密后结果
	 */
	function decrypt($prestr) {
		return rsaDecrypt($prestr, trim($this->alipay_config['private_key_path']));
	}

	/**
	 * 异步通知时，对参数做固定排序
	 * @param $para 排序前的参数组
	 * @return 排序后的参数组
	 */
	function sortNotifyPara($para) {
		$para_sort['service'] = $para['service'];
		$para_sort['v'] = $para['v'];
		$para_sort['sec_id'] = $para['sec_id'];
		$para_sort['notify_data'] = $para['notify_data'];
		return $para_sort;
	}

	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
	 * @param $isSort 是否对待签名数组排序
	 * @return 签名验证结果
	 */
	function getSignVeryfy($para_temp, $sign, $isSort) {
		//除去待签名参数数组中的空值和签名参数
		$para = paraFilter($para_temp);

		//对待签名参数数组排序
		if ($isSort) {
			$para = argSort($para);
		} else {
			$para = $this->sortNotifyPara($para);
		}

		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para);

		$isSgin = false;
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
		case "MD5":
			$isSgin = md5Verify($prestr, $sign, $this->alipay_config['key']);
			break;
		case "RSA":
			$isSgin = rsaVerify($prestr, trim($this->alipay_config['ali_public_key_path']), $sign);
			break;
		case "0001":
			$isSgin = rsaVerify($prestr, trim($this->alipay_config['ali_public_key_path']), $sign);
			break;
		default:
			$isSgin = false;
		}

		return $isSgin;
	}

	protected function setInfo($notify) {
		// BoxModel("test")->add(array('test'=>"setInfonotify:".json_encode($notify)));//测试可以删除
		/*******支付宝手机网页支付异步回调*****/
		$notify_data = stripslashes(htmlspecialchars_decode($_POST['notify_data']));
		if ($notify_data) {
			$doc = new \DOMDocument();
			$doc->loadXML($notify_data);
			if (!empty($doc->getElementsByTagName("notify")->item(0)->nodeValue)) {
				$notify['out_trade_no'] = $doc->getElementsByTagName("out_trade_no")->item(0)->nodeValue;
				$notify['total_fee'] = $doc->getElementsByTagName("total_fee")->item(0)->nodeValue;
				$notify['trade_status'] = $doc->getElementsByTagName("trade_status")->item(0)->nodeValue;
				$notify['result'] = $doc->getElementsByTagName("result")->item(0)->nodeValue;
			}
			BoxModel("test")->add(array('test' => $out_trade_no)); //测试可以删除
		}
		//支付状态
		$info['status'] = ($notify['result'] == 'success' || $notify['trade_status'] == 'TRADE_FINISHED' || $notify['trade_status'] == 'TRADE_SUCCESS') ? true : false;
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
	function getResponse($notify_id) {
		$transport = strtolower(trim($this->alipay_config['transport']));
		$partner = trim($this->alipay_config['partner']);
		$veryfy_url = '';
		if ($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		} else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);

		return $responseTxt;
	}

}
