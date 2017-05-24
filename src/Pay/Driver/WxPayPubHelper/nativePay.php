
 <html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <title>微信安全支付</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</head>
<style>
 html {font-size:62.5%;font-family:'helvetica neue',tahoma,arial,'hiragino sans gb','microsoft yahei','Simsun',sans-serif}
body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td,hr {margin:0;padding:0}
body{line-height:1.333;font-size:12px}
h1,h2,h3,h4,h5,h6{font-size:100%;font-family:arial,'hiragino sans gb','microsoft yahei','Simsun',sans-serif}
body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td,hr {margin:0;padding:0}
body{line-height:1.333;font-size:12px}
input,textarea,select,button{font-size:12px;font-weight:normal}
input[type="button"],input[type="submit"],select,button{cursor:pointer}
table {border-collapse:collapse;border-spacing:0}
dt{ font-weight:normal}
address,caption,cite,code,dfn,em,th,var {font-style:normal;font-weight:normal}
li {list-style:none}
caption,th {text-align:left}
q:before,q:after {content:''}
abbr,acronym {border:0;font-variant:normal}
sup {vertical-align:text-top}
sub {vertical-align:text-bottom}
fieldset,img,a img,iframe {border-width:0;border-style:none}
img{-ms-interpolation-mode:bicubic}
textarea{overflow-y:auto}
legend {color:#000}
a:link,a:visited {text-decoration:none}
hr{height:0}
label{cursor:pointer}
a{color:#328CE5}
a:hover{color:#2b8ae8;text-decoration:none}
a.hit{color:#C06C6C}
a:focus {outline:none}
.hit{color:#8DC27E}
.txt_auxiliary{color:#A2A2A2}
.clear {*zoom:1}
.clear:before,.clear:after {content:"";display:table}
.clear:after {clear:both}
body,.body
{background:#f7f7f7;height:100%}
.mod-title
{height:60px;line-height:60px;text-align:center;border-bottom:1px solid #ddd;background:#fff}
.mod-title .ico-wechat
{display:inline-block;width:41px;height:36px;background:url("<?php echo PUBLIC_PATH ?>/Images/wechat-pay.png") 0 -115px no-repeat;vertical-align:middle;margin-right:7px}
.mod-title .text
{font-size:20px;color:#333;font-weight:normal;vertical-align:middle}
.mod-ct
{width:610px;padding:0 135px;margin:0 auto;margin-top:15px;background:#fff url("<?php echo PUBLIC_PATH ?>/Images/wave.png") top center repeat-x;text-align:center;color:#333;border:1px solid #e5e5e5;border-top:none}
.mod-ct .order
{font-size:20px;padding-top:30px}
.mod-ct .amount
{font-size:48px;margin-top:20px}
.mod-ct .qr-image
{margin-top:30px}
.mod-ct .qr-image img
{width:230px;height:230px}
.mod-ct .detail
{margin-top:60px;padding-top:25px}
.mod-ct .detail .arrow .ico-arrow
{display:inline-block;width:20px;height:11px;background:url("<?php echo PUBLIC_PATH ?>/Images/wechat-pay.png") -25px -100px no-repeat}
.mod-ct .detail .detail-ct
{display:none;font-size:14px;text-align:right;line-height:28px}
.mod-ct .detail .detail-ct dt
{float:left}
.mod-ct .detail-open
{border-top:1px solid #e5e5e5}
.mod-ct .detail .arrow
{padding:6px 34px;border:1px solid #e5e5e5}
.mod-ct .detail .arrow .ico-arrow
{display:inline-block;width:20px;height:11px;background:url("images/wechat-pay.png") -25px -100px no-repeat}
.mod-ct .detail-open .arrow .ico-arrow
{display:inline-block;width:20px;height:11px;background:url("images/wechat-pay.png") 0 -100px no-repeat}
.mod-ct .detail-open .detail-ct
{display:block}
.mod-ct .tip
{margin-top:40px;border-top:1px dashed #e5e5e5;padding:30px 0;position:relative}
.mod-ct .tip .ico-scan
{display:inline-block;width:56px;height:55px;background:url("images/wechat-pay.png") 0 0 no-repeat;vertical-align:middle;*display:inline;*zoom:1}
.mod-ct .tip .tip-text
{display:inline-block;vertical-align:middle;text-align:left;margin-left:23px;font-size:16px;line-height:28px;*display:inline;*zoom:1}
.mod-ct .tip .dec
{display:inline-block;width:22px;height:45px;background:url("images/wechat-pay.png") 0 -55px no-repeat;position:absolute;top:-23px}
.mod-ct .tip .dec-left
{background-position:0 -55px;left:-136px}
.mod-ct .tip .dec-right
{background-position:-25px -55px;right:-136px}
    .foot
{text-align:center;margin:30px auto;color:#888888;font-size:12px;line-height:20px;font-family:"simsun"}
.foot .link
{color:#0071ce}/*  |xGv00|61aee01008b1b126a62573a7687550a6 */
</style>
<body style="width:100%; margin:0 auto">

 <div class="body">
    <h1 class="mod-title">
        <span class="ico-wechat"></span><span class="text">微信支付</span>
    </h1>
    <div class="mod-ct">
        <div class="order">
        </div>
        <div class="amount">￥<?php echo $params['order_amount'] ?></div>
        <div class="qr-image" style="">
            <img style="width:230px;height:230px;" id="billImage" src="<?php echo $params['qrcode'] ?>">
        </div>
        <!--detail-open 加上这个类是展示订单信息，不加不展示-->
        <div class="detail detail-open" id="orderDetail" style="">
            <dl class="detail-ct" style="padding-bottom:20px">
                <dt>商家</dt>
                <dd id="storeName">突击教育</dd>
                <dt>商品名称</dt>
                <dd id="productName" style="overflow:hidden; white-space:nowrap"><?php echo $params['title'] ?></dd>
                <dt>交易单号</dt>
                <dd id="billId"><?php echo $params['orderNo'] ?></dd>
                <dt>创建时间</dt>
                <dd id="createTime"><?php echo $params['addtime'] ?></dd>
            </dl>
            <a href="javascript:void(0)" class="arrow"><i class="glyphicon glyphicon-chevron-down"></i></a>
        </div>
        <div class="tip">
            <span class="dec dec-left"></span>
            <span class="dec dec-right"></span>
            <div class="ico-scan"></div>
            <div class="tip-text">
                <p>请使用微信扫一扫</p>
                <p>扫描二维码完成支付</p>
            </div>
        </div>
     </div>

    <div class="foot">
        <div class="inner">
            <p>您若对上述交易有疑问</p>
            <p>请联系我啊QQ <a href="javascript:void(0);" class="link">359945126</a></p>
        </div>
    </div>

</div>

<script type="text/javascript">
  $(".arrow").click(function(){
			  if($('#orderDetail').hasClass('detail-open')){
				$('#orderDetail .detail-ct').slideUp(500,function(){
					$('#orderDetail').removeClass('detail-open');
				});
			}else{
				$('#orderDetail .detail-ct').slideDown(500,function(){
					$('#orderDetail').addClass('detail-open');
				});
			}
		})
    // timename = setInterval("refreshOrder()", 500);
    // function refreshOrder() {
    //     $.get("/BoxPay/Order/refreshOrder", {order_sn: "<{$data['order_sn']}>"}, function (data) {
    //         if (data == 1) {
    //             window.location.href = "<{$data['suceess_url']}>"
    //         }
    //     })
    // }
</script>
</body>
</html>