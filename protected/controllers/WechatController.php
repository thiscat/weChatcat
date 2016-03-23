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
			$toUsername = $postObj->ToUserName;
			$fromUserName = $postObj->FromUserName;
			$keyword = trim($postObj->Content);
			$createTime = time();
			$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						</xml>";
			if($keyword == "?" || $keyword == "？"){
				$msgType = "text";
				$content = "当前时间".date('Y-m-d H:i:s',time());
				$resultStr = sprintf($textTpl,$fromUserName,$toUsername,$createTime,$msgType,$content);
				echo $resultStr;
			}
		}else{
			echo "没有接收到数据";
			exit;
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
}