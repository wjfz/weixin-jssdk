<?php

$appId = 'YOUR_APPID';
$appsecret = 'YOUR_APPSECRET';

$timestamp = time();
$jsapi_ticket = make_ticket($appId,$appsecret);
$nonceStr = make_nonceStr();
$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$signature = make_signature($nonceStr,$timestamp,$jsapi_ticket,$url);

function make_nonceStr()
{
	$codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	for ($i = 0; $i<16; $i++) {
		$codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
	}
	$nonceStr = implode($codes);
	return $nonceStr;
}

function make_signature($nonceStr,$timestamp,$jsapi_ticket,$url)
{
	$tmpArr = array(
	'noncestr' => $nonceStr,
	'timestamp' => $timestamp,
	'jsapi_ticket' => $jsapi_ticket,
	'url' => $url
	);
	ksort($tmpArr, SORT_STRING);
	$string1 = http_build_query( $tmpArr );
	$string1 = urldecode( $string1 );
	$signature = sha1( $string1 );
	return $signature;
}

function make_ticket($appId,$appsecret)
{
	// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
	$data = json_decode(file_get_contents("access_token.json"));
	if ($data->expire_time < time()) {
		$TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appsecret;
		$json = file_get_contents($TOKEN_URL);
		$result = json_decode($json,true);
		$access_token = $result['access_token'];
		if ($access_token) {
			$data->expire_time = time() + 7000;
			$data->access_token = $access_token;
			$fp = fopen("access_token.json", "w");
			fwrite($fp, json_encode($data));
			fclose($fp);
		}
	}else{
		$access_token = $data->access_token;
	}

	// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
	$data = json_decode(file_get_contents("jsapi_ticket.json"));
	if ($data->expire_time < time()) {
		$ticket_URL="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
		$json = file_get_contents($ticket_URL);
		$result = json_decode($json,true);
		$ticket = $result['ticket'];
		if ($ticket) {
			$data->expire_time = time() + 7000;
			$data->jsapi_ticket = $ticket;
			$fp = fopen("jsapi_ticket.json", "w");
			fwrite($fp, json_encode($data));
			fclose($fp);
		}
	}else{
		$ticket = $data->jsapi_ticket;
	}

	return $ticket;
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>微信JS-SDK Demo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
  <link rel="stylesheet" href="style.css">
</head>
<body ontouchstart="">
<div class="wxapi_container">
	<div class="wxapi_index_container">
	  <ul class="label_box lbox_close wxapi_index_list">
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-basic">基础接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-share">分享接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-image">图像接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-voice">音频接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-smart">智能接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-device">设备信息接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-location">地理位置接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-webview">界面操作接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-scan">微信扫一扫接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-shopping">微信小店接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-card">微信卡券接口</a></li>
		<li class="label_item wxapi_index_item"><a class="label_inner" href="#menu-pay">微信支付接口</a></li>
	  </ul>
	</div>
	<div class="lbox_close wxapi_form">
	  <h3 id="menu-basic">基础接口</h3>
	  <span class="desc">判断当前客户端是否支持指定JS接口</span>
	  <button class="btn btn_primary" id="checkJsApi">checkJsApi</button>

	  <h3 id="menu-share">分享接口</h3>
	  <span class="desc">获取“分享到朋友圈”按钮点击状态及自定义分享内容接口</span>
	  <button class="btn btn_primary" id="onMenuShareTimeline">onMenuShareTimeline</button>
	  <span class="desc">获取“分享给朋友”按钮点击状态及自定义分享内容接口</span>
	  <button class="btn btn_primary" id="onMenuShareAppMessage">onMenuShareAppMessage</button>
	  <span class="desc">获取“分享到QQ”按钮点击状态及自定义分享内容接口</span>
	  <button class="btn btn_primary" id="onMenuShareQQ">onMenuShareQQ</button>
	  <span class="desc">获取“分享到腾讯微博”按钮点击状态及自定义分享内容接口</span>
	  <button class="btn btn_primary" id="onMenuShareWeibo">onMenuShareWeibo</button>

	  <h3 id="menu-image">图像接口</h3>
	  <span class="desc">拍照或从手机相册中选图接口</span>
	  <button class="btn btn_primary" id="chooseImage">chooseImage</button>
	  <span class="desc">预览图片接口</span>
	  <button class="btn btn_primary" id="previewImage">previewImage</button>
	  <span class="desc">上传图片接口</span>
	  <button class="btn btn_primary" id="uploadImage">uploadImage</button>
	  <span class="desc">下载图片接口</span>
	  <button class="btn btn_primary" id="downloadImage">downloadImage</button>

	  <h3 id="menu-voice">音频接口</h3>
	  <span class="desc">开始录音接口</span>
	  <button class="btn btn_primary" id="startRecord">startRecord</button>
	  <span class="desc">停止录音接口</span>
	  <button class="btn btn_primary" id="stopRecord">stopRecord</button>
	  <span class="desc">播放语音接口</span>
	  <button class="btn btn_primary" id="playVoice">playVoice</button>
	  <span class="desc">暂停播放接口</span>
	  <button class="btn btn_primary" id="pauseVoice">pauseVoice</button>
	  <span class="desc">停止播放接口</span>
	  <button class="btn btn_primary" id="stopVoice">stopVoice</button>
	  <span class="desc">上传语音接口</span>
	  <button class="btn btn_primary" id="uploadVoice">uploadVoice</button>
	  <span class="desc">下载语音接口</span>
	  <button class="btn btn_primary" id="downloadVoice">downloadVoice</button>

	  <h3 id="menu-smart">智能接口</h3>
	  <span class="desc">识别音频并返回识别结果接口</span>
	  <button class="btn btn_primary" id="translateVoice">translateVoice</button>

	  <h3 id="menu-device">设备信息接口</h3>
	  <span class="desc">获取网络状态接口</span>
	  <button class="btn btn_primary" id="getNetworkType">getNetworkType</button>

	  <h3 id="menu-location">地理位置接口</h3>
	  <span class="desc">使用微信内置地图查看位置接口</span>
	  <button class="btn btn_primary" id="openLocation">openLocation</button>
	  <span class="desc">获取地理位置接口</span>
	  <button class="btn btn_primary" id="getLocation">getLocation</button>

	  <h3 id="menu-webview">界面操作接口</h3>
	  <span class="desc">隐藏右上角菜单接口</span>
	  <button class="btn btn_primary" id="hideOptionMenu">hideOptionMenu</button>
	  <span class="desc">显示右上角菜单接口</span>
	  <button class="btn btn_primary" id="showOptionMenu">showOptionMenu</button>
	  <span class="desc">关闭当前网页窗口接口</span>
	  <button class="btn btn_primary" id="closeWindow">closeWindow</button>
	  <span class="desc">批量隐藏功能按钮接口</span>
	  <button class="btn btn_primary" id="hideMenuItems">hideMenuItems</button>
	  <span class="desc">批量显示功能按钮接口</span>
	  <button class="btn btn_primary" id="showMenuItems">showMenuItems</button>
	  <span class="desc">隐藏所有非基础按钮接口</span>
	  <button class="btn btn_primary" id="hideAllNonBaseMenuItem">hideAllNonBaseMenuItem</button>
	  <span class="desc">显示所有功能按钮接口</span>
	  <button class="btn btn_primary" id="showAllNonBaseMenuItem">showAllNonBaseMenuItem</button>

	  <h3 id="menu-scan">微信扫一扫</h3>
	  <span class="desc">调起微信扫一扫接口</span>
	  <button class="btn btn_primary" id="scanQRCode0">scanQRCode(微信处理结果)</button>
	  <button class="btn btn_primary" id="scanQRCode1">scanQRCode(直接返回结果)</button>

	  <h3 id="menu-shopping">微信小店接口</h3>
	  <span class="desc">跳转微信商品页接口</span>
	  <button class="btn btn_primary" id="openProductSpecificView">openProductSpecificView</button>

	  <h3 id="menu-card">微信卡券接口</h3>
	  <span class="desc">批量添加卡券接口</span>
	  <button class="btn btn_primary" id="addCard">addCard</button>
	  <span class="desc">调起适用于门店的卡券列表并获取用户选择列表</span>
	  <button class="btn btn_primary" id="chooseCard">chooseCard</button>
	  <span class="desc">查看微信卡包中的卡券接口</span>
	  <button class="btn btn_primary" id="openCard">openCard</button>

	  <h3 id="menu-pay">微信支付接口</h3>
	  <span class="desc">发起一个微信支付请求</span>
	  <button class="btn btn_primary" id="chooseWXPay">chooseWXPay</button>
	</div>
  </div>
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  /*
   * 注意：
   * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
   * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
   * 3. 完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
   *
   * 如有问题请通过以下渠道反馈：
   * 邮箱地址：weixin-open@qq.com
   * 邮件主题：【微信JS-SDK反馈】具体问题
   * 邮件内容说明：用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
   */
  wx.config({
	debug: true,
	appId: '<?=$appId?>',
	timestamp: <?=$timestamp?>,
	nonceStr: '<?=$nonceStr?>',
	signature: '<?=$signature?>',
	jsApiList: [
		'checkJsApi',
		'onMenuShareTimeline',
		'onMenuShareAppMessage',
		'onMenuShareQQ',
		'onMenuShareWeibo',
		'hideMenuItems',
		'showMenuItems',
		'hideAllNonBaseMenuItem',
		'showAllNonBaseMenuItem',
		'translateVoice',
		'startRecord',
		'stopRecord',
		'onRecordEnd',
		'playVoice',
		'pauseVoice',
		'stopVoice',
		'uploadVoice',
		'downloadVoice',
		'chooseImage',
		'previewImage',
		'uploadImage',
		'downloadImage',
		'getNetworkType',
		'openLocation',
		'getLocation',
		'hideOptionMenu',
		'showOptionMenu',
		'closeWindow',
		'scanQRCode',
		'chooseWXPay',
		'openProductSpecificView',
		'addCard',
		'chooseCard',
		'openCard'
	  ]
  });
</script>
<script src="demo.js"></script>
</html>