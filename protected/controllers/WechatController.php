<?php

class WechatController extends BaseController
{
    protected $appid = "wxf13635d0906784cc";
    protected $appsecret = "3ea52e86c92d67a0968cd42b5067f596";

	public function actionIndex()
	{
		if (isset($_GET["echostr"])) {
			$this->valid();
		} else {
			$this->responseMsg();
		}
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
			echo "";
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
				$content = "关注\r\n信息,测试关注\n信息";
				break;
			case "LOCATION":
				$content = "本地\r\n信息";
				break;
			case "CLICK":
                $content = $this->clickEvent($postObj->EventKey);
				break;
			case "location":
				$content = $postObj->Label;
				break;
		}
		$this->transmitText($postObj, $content);
	}

    public function clickEvent($evenKey)
    {
        if(strstr($evenKey,"movie")){
            $content = "http://m.kb20.cc/vod-show-id-1-p-1.html";
        }else{
            $content = "没有数据";
        }
        return $content;
    }

	public function actionMovie($keyword="功夫熊猫")
	{
		$url = "http://www.diediao.com/search-wd-{$keyword}.html";
		$result = $this->curl_request($url);
		$result = preg_replace("'<script(.*?)</script>'is","",$result);//去除js文件
		$reg = array("　","\t","\n","\r");
		$result = str_replace($reg, '', $result);
		preg_match('#<ul class="show-list" id="contents">.*?</ul>#',$result,$showList);
		var_dump($showList[0]);
	}

	/**
	 * 发送文本信息
	 * @obj $postObj
	 */
	public function receiveText($postObj)
	{
		$keyword = trim($postObj->Content);
		if(strstr($keyword,"电影")){
			$content = "http://m.kb20.cc/vod-show-id-1-p-1.html";
		}else if(strstr($keyword,"测试")){
			$content = "\n\n回复“搜索” 了解详情\n其他文字";
		}else{
			$content = array();
			$content[] = array( "Title"=>"单图文",
								 "Description"=>"单图文内容",
								 "PicUrl"=>"http://tu8.diediao.com/Uploads/vod/2015-03-07/54fadab893e69.jpg",
								 "Url" =>"http://m.kb20.cc/Animation/gongfuxiongmiao3/");
		}

		if(is_array($content)){
			if(isset($content[0][PicUrl])){
				$this->transmitNews($postObj,$content);
			}
		}else{
			$this->transmitText($postObj,$content);
		}
	}


    /**
     * 获取用户信息
     */
    public function actionGetUserInfo()
    {
        $oauthInfo = Yii::app()->session['Oauth'];
        $file = 'Oauth.txt';
        if(empty($oauthInfo)){
            if(file_exists($file)) {
                $oauthInfo = json_decode(file_get_contents($file));
            }
        }

        if(isset($oauthInfo) && time() > $oauthInfo['expires_in']){
            $access_token = $oauthInfo['access_token'];
            $openid = $oauthInfo['openid'];
        }else{
            $redirect_uri = "http://cat-wechat.coding.io/wechat/oauth";
            $oauthInfo = $this->actionAuth($redirect_uri);//调用授权链接获取信息
            Yii::app()->session['Oauth'] = $oauthInfo;
            file_put_contents($file,json_encode($oauthInfo));

            $access_token = $oauthInfo['access_token'];
            $openid = $oauthInfo['openid'];
        }

        $userInfoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $result = $this->curl_request($userInfoUrl);
        var_dump($result);
    }

	//网页授权获取用户信息回调页面
	public function actionOauth()
	{
		if (isset($_GET['code'])){
			$getTokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->appsecret&code=".$_GET['code']."&grant_type=authorization_code";
			$jsonObj = $this->curl_request($getTokenUrl);

            $oauthInfo = json_decode($jsonObj,true);
            $oauthInfo['expires_in'] = time() + 7200;
            return $oauthInfo;
		}else{
			echo "NO CODE";
		}
	}

	//创建自定义菜单
	public function actionCreateMenu()
	{
		$jsonMenu = '{
					  "button":[
					  {
							"name":"菜单",
						   "sub_button":[
							{
							   "type": "click",
								"name": "电影",
								"key": "movie",
							},
							{
								"type":"view",
								"name":"首页",
								"url":"http://cat-wechat.coding.io/home/index"
							},
							{
								"name": "发送位置",
								"type": "location_select",
								"key": "location"
							}
							]
					   },
					   {
						   "name": "附属菜单",
							"sub_button": [
								{
									"type": "pic_sysphoto",
									"name": "系统拍照发图",
									"key": "sysphoto",
								 },
								{
									"type": "pic_photo_or_album",
									"name": "拍照或者相册发图",
									"key": "photo_or_album",
								},
								{
									"type": "pic_weixin",
									"name": "微信相册发图",
									"key": "pic_weixin",
								}
							]
						}
					 ]
				 }';
		$post_url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->access_token}";
		$result = $this->curl_request($post_url,$jsonMenu);
		var_dump($result);
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
}