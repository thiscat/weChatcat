<?php

class WechatController extends Controller
{
	private $appid = "wxf13635d0906784cc";
	private $appsecret = "3ea52e86c92d67a0968cd42b5067f596 ";
	private $access_token;

	public function actionIndex()
	{
		if (isset($_GET["echostr"])) {
			$this->valid();
		} else {
			$this->responseMsg();
		}
	}

	public function init()
	{
		$this->getAccessToken();
	}

	//回复信息
	public function responseMsg()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)) {
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$msgType = trim($postObj->MsgType);

			switch ($msgType) {
				case "event":
					$this->receiveEvent($postObj);
					break;
				case "text":
					$this->receiveText($postObj);
					break;
			}
		} else {
			echo "没有接收到数据";
			exit;
		}
	}

	/**
	 * 触发事件
	 * @object $postObj
	 */
	public function receiveEvent($postObj)
	{
		switch ($postObj->Event) {
			case "subscribe":
				$content = "关注信息";
				break;
			case "LOCATION":

		}
		$this->transmit($postObj, $content);
	}

	public function actionMovie($keyword="功夫熊猫")
	{
		$url = "http://www.diediao.com/search-wd-{$keyword}.html";
		$result = $this->curl_post($url);
		$result = preg_replace("'<script(.*?)</script>'is","",$result);//去除js文件
		$reg = array("　","\t","\n","\r");
		$result = str_replace($reg, '', $result);
		preg_match('#<ul class="show-list" id="contents">.*?</ul>#',$result,$showList);
		var_dump($showList[0]);
	}

	public function actionCreateMenu()
	{
		$jsonMenu = '{
					"button": [
						{
							"name": "菜单",
							"sub_button": [
								{
									"type": "click",
									"name": "电影",
									"key": "movie",
								},
							]
						},
						{
							"name": "附属菜单",
							"sub_button": [
								{
									"type": "pic_sysphoto",
									"name": "系统拍照发图",
									"key": "sysphoto",
								   "sub_button": [ ]
								 },
								{
									"type": "pic_photo_or_album",
									"name": "拍照或者相册发图",
									"key": "photo_or_album",
									"sub_button": [ ]
								},
								{
									"type": "pic_weixin",
									"name": "微信相册发图",
									"key": "pic_weixin",
									"sub_button": [ ]
								}
							]
						},
						{
							"name": "发送位置",
							"type": "location_select",
							"key": "location"
						},
						{
						   "type": "media_id",
						   "name": "图片",
						   "media_id": "media"
						},
						{
						   "type": "view_limited",
						   "name": "图文消息",
						   "media_id": "view"
						}
					]
				}';
		$post_url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->access_token}";
		$result = $this->curl_post($post_url,$jsonMenu);
		var_dump($result);
	}

	/**
	 * 发送文本信息
	 * @obj $postObj
	 */
	public function receiveText($postObj)
	{
		$keyword = trim($postObj->Content);
		if($keyword == "?" || $keyword == "？"){
			$content = "当前时间".date('Y-m-d H:i:s',time());
			$this->transmit($postObj,$content);
		}
	}

	/**
	 * 被动发送信息
	 * @object $postObj 接收信息 对象形式
	 * @string $content 发送信息的内容
	 * @string string $msgType 信息类型，默认文本信息
	 */
	public function transmit($postObj,$content,$msgType = "text")
	{
		$toUsername = $postObj->ToUserName;
		$fromUserName = $postObj->FromUserName;
		$createTime = time();
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";

		$resultStr = sprintf($textTpl,$fromUserName,$toUsername,$createTime,$msgType,$content);
		echo $resultStr;
	}

	/**
	 * 获取access_token
	 */
	public function getAccessToken()
	{
		$tokenArr = Yii::app()->session['tokenArr'];
		if(empty($tokenArr)){
			$tokenArr = json_decode(file_get_contents('access_token.txt'),true);
		}

		if(!empty($tokenArr['access_token']) && time() < $tokenArr['expires_in']){
			$this->access_token = $tokenArr['access_token'];
		}else{
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";
			$json = $this->curl_post($url);
			$tokenArr = json_decode($json,true);
			$this->access_token = $tokenArr['access_token'];
			$tokenArr['expires_in'] = time() + 7200;

			Yii::app()->session['tokenArr'] = $tokenArr;
			file_put_contents('access_token.txt',json_encode($tokenArr));
		}
	}

	//微信基本配置验证
	public function valid()
	{
		$echoStr = $_GET["echostr"];
		//valid signature , option
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}

	public function checkSignature()
	{
		// you must define TOKEN by yourself
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = 'cat';//token,必须与微信基本配置填写的一致
		$tmpArr = array($token, $timestamp, $nonce);
		// use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * curl
	 * @string $url
	 * @param null $data
	 * @return mixed
	 */
	function curl_post($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
}