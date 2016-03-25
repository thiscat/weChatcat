<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class BaseController extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

	protected $appid = "wxf13635d0906784cc";
	protected $appsecret = "3ea52e86c92d67a0968cd42b5067f596";
	protected $access_token;

	public function beforeaction($action){
		$this->getAccessToken();
		return true;
	}

	/**
	 * 文本信息
	 * @object $postObj 接收信息 对象形式
	 * @string $content 发送信息的内容
	 */
	public function transmitText($postObj,$content)
	{
		$toUsername = $postObj->ToUserName;
		$fromUserName = $postObj->FromUserName;
		$createTime = time();
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";

		$resultStr = sprintf($textTpl,$fromUserName,$toUsername,$createTime,$content);
		echo $resultStr;
	}

	/**
	 * 发送图文信息
	 * @param $postObj
	 * @param $newsArray
	 * @return string
	 */
	public function transmitNews($postObj,$newsArray)
	{
		if(!is_array($newsArray)){
			return "";
		}
		$itemTpl = "    <item>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						<PicUrl><![CDATA[%s]]></PicUrl>
						<Url><![CDATA[%s]]></Url>
						</item>";

		$itemStr = "";
		foreach($newsArray as $item){
			$itemStr .= sprintf($itemTpl,$item['Title'],$item['Description'],$item['PicUrl'],$item['Url']);
		}

		$toUsername = $postObj->ToUserName;
		$fromUserName = $postObj->FromUserName;
		$createTime = time();
		$xmlTpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[news]]></MsgType>
				<ArticleCount>%s</ArticleCount>
				<Articles>$itemStr</Articles>
				</xml> ";
		$resultStr = sprintf($xmlTpl,$fromUserName,$toUsername,$createTime,count($newsArray));
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
			$json = $this->curl_request($url);
			$tokenArr = json_decode($json,true);
			$this->access_token = $tokenArr['access_token'];
			$tokenArr['expires_in'] = time() + 7200;

			Yii::app()->session['tokenArr'] = $tokenArr;
			file_put_contents('access_token.txt',json_encode($tokenArr));
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
	function curl_request($url,$data = null){
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