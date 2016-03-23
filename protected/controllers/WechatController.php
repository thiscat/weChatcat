<?php

class WechatController extends Controller
{
	public function actionIndex()
	{
		if(isset($_GET["echostr"])){
			$this->valid();
		}else{
			$this->responseMsg();
		}
	}

	//回复信息
	public function responseMsg()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if(!empty($postStr)){
			$postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
			$msgType = trim($postObj->MsgType);

			switch($msgType){
				case "event":
					$this->receiveEvent($postObj);
					break;
				case "text":
					$this->receiveText($postObj);
					break;
			}
		}else{
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
		switch($postObj->Event){
			case "subscribe":
				$content = "关注信息";
				break;
		}
		$this->transmit($postObj,$content);
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
}