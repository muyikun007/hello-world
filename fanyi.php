<?php
define("TOKEN", "wxfanyi");
$weixin = new weixin();

if(isset($_GET['echostr'])){
    $weixin->valid();
}else{
    $weixin->responseMsg();
}

class weixin
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";  

				
                if(mb_substr($keyword, 0, 2, 'UTF-8') == '翻译')
                {
                    $fanyi = mb_substr($keyword, 0, 2, 'UTF-8');
                    $word = str_replace($fanyi, '', $keyword);
                    $key = '330102425';
                    $keyfrom = 'qiyunkj';
                    $url = 'http://fanyi.youdao.com/openapi.do?keyfrom='.$keyfrom.'&key='.$key.'&type=data&doctype=json&version=1.1&q=' . urlencode($word);
                    
                    $fanyiJson = file_get_contents($url);
                    
                    $fanyiArr = json_decode($fanyiJson, true);
                	
                    $contentStr = "【查询】\n" . $fanyiArr['query'] . "\n【翻译】\n" . $fanyiArr['translation'][0];
                    
                    
                    if(isset($fanyiArr['web'])){
                        $extension = "\n【扩展翻译】";
                        
                        $arr = $fanyiArr['web'][0]['value'];
                        $n = 1;
                        foreach($arr as $v){
                            $extension .= "\n" . $n . '、' . $v;
                            $n++;
                        }

                    }else{
                        $extension = '';
                    }

                    $contentStr .= $extension;

                    $msgType = "text";

                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);

                	echo $resultStr;
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
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

?>