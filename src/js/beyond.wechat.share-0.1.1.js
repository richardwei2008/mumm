
(function () {
	if (document.addEventListener) {
		document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
	} else if (document.attachEvent) {
		document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
		document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
	}

	function onBridgeReady() {
		// subscribe  用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。  
		// openid  用户的标识，对当前公众号唯一  
		// nickname  用户的昵称  
		// sex  用户的性别，值为1时是男性，值为2时是女性，值为0时是未知  
		// city  用户所在城市  
		// country  用户所在国家  
		// province  用户所在省份  
		// language  用户的语言，简体中文为zh_CN  
		// headimgurl  用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空  
		// subscribe_time  用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
		window.user = {
			"subscribe": 0,
			"openId": null,
			"nickname": null,
			"sex": null,
			"city": null,
			"country": null,
			"province": null,
			"language": null,
			"headimgurl": null,
			"subscribe_time": null
		};
		window.shareData = {
			"imgUrl": window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/../images/shareicon.jpg",
			//可以是页面的头像，也可以是自己定义的一张图片不变，每个页面可以有这个JS
			"timeLineLink": "http://mp.weixin.qq.com/s?__biz=MzA3MDE3NzMwOQ==&mid=201143105&idx=1&sn=502c49cfe41494f6cbf801256fe87721#rd", // window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/share.html",
			"sendFriendLink": "http://mp.weixin.qq.com/s?__biz=MzA3MDE3NzMwOQ==&mid=201143105&idx=1&sn=502c49cfe41494f6cbf801256fe87721#rd", // window.location.href.substring(0, window.location.href.lastIndexOf('/')) + "/share.html",
			"weiboLink": "http://mp.weixin.qq.com/s?__biz=MzA3MDE3NzMwOQ==&mid=201143105&idx=1&sn=502c49cfe41494f6cbf801256fe87721#rd", // window.location.href.substring(0, window.location.href.lastIndexOf('/')),
			//发送朋友圈
			"tTitle": "参与玛姆香槟F1竞猜，尽享礼遇！",
			"tContent": "即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼",
			//发送给朋友
			"fTitle": "参与玛姆香槟F1竞猜，尽享礼遇！",
			"fContent": "即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼",
			"wContent": "参与玛姆香槟F1竞猜，尽享礼遇！"
		};
		var content = "即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼";
		var title = "参与玛姆香槟F1竞猜，尽享礼遇！";
		// if (url.indexOf('?') < 0) {
		// 	url = url + '?user=richard';
		// }
		// 发送给好友;
		WeixinJSBridge.on('menu:share:appmessage', function (argv) {
			WeixinJSBridge.invoke('sendAppMessage', {
				"img_url" : window.shareData.imgUrl,
				"img_width" : "640",
				"img_height" : "640",
				"link" : window.shareData.sendFriendLink,
				"desc" : window.shareData.fContent,
				"title" : window.shareData.fTitle
			}, function (res) {});
		});
		// 分享到朋友圈;
		WeixinJSBridge.on('menu:share:timeline', function (argv) {
			WeixinJSBridge.invoke('shareTimeline', {
				"img_url" : window.shareData.imgUrl,
				"img_width" : "640",
				"img_height" : "640",
				"link" : window.shareData.timeLineLink,
				"desc" : window.shareData.tContent,
				"title" : window.shareData.tTitle
			}, function (res) {});
		});
		// 分享到微博;
		var weiboContent = '';
		WeixinJSBridge.on('menu:share:weibo', function (argv) {
			WeixinJSBridge.invoke('shareWeibo', {
				"content" : window.shareData.tTitle,
				"url" : window.shareData.weiboLink,
			}, function (res) {});
		});
		// 分享到Facebook
		WeixinJSBridge.on('menu:share:facebook', function (argv) {
			WeixinJSBridge.invoke('shareFB', {
				"img_url" : window.shareData.imgUrl,
				"img_width" : "640",
				"img_height" : "640",
				"link" : window.shareData.weiboLink,
				"desc" : window.shareData.tTitle,
				"title" : window.shareData.tContent
			}, function (res) {});
		});
		// 新的接口
		// WeixinJSBridge.on('menu:general:share', function (argv) {
		// 	var scene = 0;
		// 	switch (argv.shareTo) {
		// 	case 'friend':
		// 		scene = 1;
		// 		break;
		// 	case 'timeline':
		// 		scene = 2;
		// 		break;
		// 	case 'weibo':
		// 		scene = 3;
		// 		break;
		// 	}
		// 	argv.generalShare({
		// 		"appid" : "",
		// 		"img_url" : img_url,
		// 		"img_width" : "640",
		// 		"img_height" : "640",
		// 		"link" : url + "&screen=" + chapterShare.chapter + "&scene=" + scene,
		// 		"desc" : content,
		// 		"title" : title
		// 	}, function (res) {});
		// });
		// get network type
		var nettype_map = {
			"network_type:fail" : "fail",
			"network_type:edge" : "2g",
			"network_type:wwan" : "3g",
			"network_type:wifi" : "wifi"
		};
		/*
		if (typeof WeixinJSBridge != "undefined" && WeixinJSBridge.invoke){
		WeixinJSBridge.invoke('getNetworkType',{}, function(res) {
		var networkType = nettype_map[res.err_msg];
		if(networkType=="2g"){
		alert("请使用3G或wifi浏览本网页。");
		}
		});
		}
		 */

	};
	setWxContent = function (title) {
		if (typeof(window.shareData) != "undefined") {
			window.shareData["tTitle"] = title;
			window.shareData["fTitle"] = title;
			window.shareData["wContent"] = title + " —— " + window.shareData["fContent"];
		}
	};
})();

function isWeiXin() {
		var ua = window.navigator.userAgent.toLowerCase();
		if (ua.match(/MicroMessenger/i) == 'micromessenger') {
			return true;
		} else {
			return false;
		}
	};

function addWxContact(wxid) {      
	if (typeof WeixinJSBridge == 'undefined') return false;          
		WeixinJSBridge.invoke('addContact', {              
		webtype: '1',              
		username: 'gh_e5430c6431e7'          
	},  function(d) {             
		 // 返回d.err_msg取值，d还有一个属性是err_desc
            // add_contact:cancel 用户取消
            // add_contact:fail　关注失败
            // add_contact:ok 关注成功
            // add_contact:added 已经关注
            WeixinJSBridge.log(d.err_msg);
            cb && cb(d.err_msg);
			});
};
