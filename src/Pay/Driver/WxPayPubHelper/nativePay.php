
<?php 
$params=array(
 'order_sn'=>$vo->getOrderNo(),
	'order_amount'=>$total_fee / 100,
	'qrcode'=>'http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url2),
);
Widget("BoxPay/Index/index",$params);	
 ?>
 