<?php

class WechatController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
	}

	//微信基本配置验证
	public function actionValid()
	{
		$echoStr = $_GET["echostr"];
		//valid signature , option
		if($this->actionCheckSignature()){
			echo $echoStr;
			exit;
		}
	}

	public function actionCheckSignature()
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