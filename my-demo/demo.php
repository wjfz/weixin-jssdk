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
这个页面是demo页面。
认证服务号已测试成功。
记得现在后台设置已备案的安全域名。
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
	wx.config({
		debug: true,
		appId: '<?=$appId?>',
		timestamp: <?=$timestamp?>,
		nonceStr: '<?=$nonceStr?>',
		signature: '<?=$signature?>',
		jsApiList: [
			'checkJsApi',
			'onMenuShareTimeline',
			'onMenuShareAppMessage'
		  ]
	});

	wx.ready(function () {
		var shareData = {
			title: '这里是分享标题',
			desc: '这里是发送给好友的时候的简介',
			link: 'http://baidu.com',
			imgUrl: 'http://baidu.com/logo.jpg'
		};
		wx.onMenuShareAppMessage(shareData);
		wx.onMenuShareTimeline(shareData);
	});

	wx.error(function (res) {
	  alert(res.errMsg);
	});
</script>
</html>
