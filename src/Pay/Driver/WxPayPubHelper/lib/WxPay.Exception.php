<?php
namespace phpkit\pay\Pay\Driver\WxPayPubHelper\lib;
/**
 *
 * 微信支付API异常类
 * @author widyhu
 *
 */
class WxPayException extends \Exception {
	public function errorMessage() {
		return $this->getMessage();
	}
}
