<?php

namespace database;
require_once(dirname(__FILE__) . "/services/config/DbConfig.php");
require_once(dirname(__FILE__) . "/services/config/AppConfig.php");
require_once (dirname(__FILE__)."/services/CustomerPDO.php");
require_once (dirname(__FILE__)."/services/GuessPDO.php");

use app\AppConfig;
use guess\GuessPDO;

define("TOKEN", "MUMM");
$wechatObj = new BeyondWechatApplication();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

//$guessPDO = new GuessPDO();
//$result = $wechatObj->checkLottery("oxqECj7JTrpVG7BfJnNCUpQap0Xc");
//echo "Result: ".var_dump($result);

class BeyondWechatApplication
{
    private $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";

    private $newsTplHead = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>";
    private $newsTplBody = "<item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>";
    private $newsTplFoot = "</Articles>
                <FuncFlag>0</FuncFlag>
                </xml>";

    private $guessPDO = null;

    /**
     * @return mixed
     */
    public function getGuessPDO()
    {
        if ($this->guessPDO == null) {
            $this->guessPDO = new GuessPDO();
        }
        return $this->guessPDO;
    }

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
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
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function checkLottery($openid) {
        if (null === $openid || 'null' === $openid || '' === trim($openid)) {
            return false;
        }
        $bingo = $this->getGuessPDO()->findBingo($openid, AppConfig::GAME);
//        $bingo = $this->getGuessPDO();
//        $bingo = new GuessPDO();
        if (null != $bingo) {
            return true;
        }
        return false;
    }

    public function testLottery($openid) {
        if (null === $openid || 'null' === $openid || '' === trim($openid)) {
            return false;
        }
        $bingo = $this->getGuessPDO()->findBingo($openid, AppConfig::GAME_TEST);
        if (null != $bingo) {
            return true;
        }
        return false;
    }

    public function responseMsg()
    {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            echo "访问路径非法！";
            return;
        }
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            switch($RX_TYPE)
            {
                case "text":
                    $resultStr = $this->handleText($postObj);
                    break;
                case "event":
                    $resultStr = $this->handleEvent($postObj);
                    break;
                default:
                    $resultStr = "Unknow msg type: ".$RX_TYPE;
                    break;
            }
        }else{
            echo "";
            exit;
        }
    }

    private function handleEvent($postObj) {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $msgType = "text";
        $time = time();
        switch ($postObj->Event)
        {
            case "subscribe":
//                $contentStr = $this->responseDefaultText($fromUsername);
//                $resultStr = sprintf($this->textTpl, $fromUsername, "", $time, $msgType, $contentStr);
//                echo $resultStr;
                $resultStr = $this->checkGame($fromUsername, $toUsername);
                echo $resultStr;
                break;
            default :
                $contentStr = "未知消息: ".$postObj->Event.".请联系微信客服";
                break;
        }
    }


    private function handleText($postObj) {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();

        if (!empty($keyword)) {
            switch ($keyword)
            {
//                case "?":
//                case "？":
//                    $msgType = "text";
//                    $contentStr = date("Y-m-d H:i:s",time());
//                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//                    echo $resultStr;
//                    break;
//                case "m":
//                case "M":
//                    $msgType = "text";
//                    // $urlToGetUserProfile = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=".OPENID."&lang=zh_CN"
//                    $contentStr = date("Y-m-d H:i:s",time())."OPENID: ".$fromUsername;
//                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//                    echo $resultStr;
//                    break;
                case "f1":
                case "F1":
                    $resultStr = $this->checkGame($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                case "测试赛程YiKg":
                    $resultStr = $this->testSchedule($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                case "赛程":
                    $resultStr = $this->checkSchedule($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                case "中奖":
                    $resultStr = $this->checkGuess($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                case "未中奖":
                    $resultStr = $this->wrong($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                case "测试中奖YiKg":
                    $resultStr = $this->testGuess($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                case "测试未中奖YiKg":
                    $resultStr = $this->testWrong($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                case "over":
                    $resultStr = $this->gameOver($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
				case "YiKg":
                    $resultStr = $this->visit($fromUsername, $toUsername);
                    echo $resultStr;
                    break;
                default :
                    $msgType = "text";
                    $contentStr = $this->responseDefaultText($fromUsername);
                    $resultStr = sprintf($this->textTpl, $fromUsername, "", $time, $msgType, $contentStr);
                    echo $resultStr;
                    break;
            }
        } else {
            echo "查询文字不能为空白";
        }
    }

    private function responseDefaultText($fromUsername) {
//        if ($fromUsername === null) {
        $fromUsername = "";
//        }
        return "感谢您关注【玛姆香槟人生玩家】".$fromUsername."\n请回复内容：\n 1. \" F1 \"  - (参与F1冠军竞猜)\n 2. \" 中奖 \"  - (查询竞猜中奖)\n 3. \" 赛程 \"  - (查看F1赛程)";
    }

    private function checkSchedule($fromUsername, $toUsername) {
        $url = 'http://beyonddev1.sinaapp.com/info/schedule.html?openid='.$fromUsername;
        $record=array(
            'title' =>'2014年 F1赛程表',
            'description' =>'即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼...',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
//            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_schedule_201408261216.jpg',
            'url' => $url
        );
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $record['title'];
        $desc = $record['description'];
        $picUrl = $record['picUrl'];
        $url = $record['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return  $header.$body.$footer;
    }

    private function testSchedule($fromUsername, $toUsername) {
        $url = 'http://beyonddev1.sinaapp.com/info/schedule_test.html?openid='.$fromUsername;
        $record=array(
            'title' =>'2014年 F1赛程表',
            'description' =>'即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼...',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' => $url
        );
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $record['title'];
        $desc = $record['description'];
        $picUrl = $record['picUrl'];
        $url = $record['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return  $header.$body.$footer;
    }

	 private function visit($fromUsername, $toUsername) {
        // $url = 'http://beyonddev1.sinaapp.com/index.html?openid='.$fromUsername; // TODO
        $url = 'http://beyonddev1.sinaapp.com/submit_201409191027.html?openid='.$fromUsername;
        $record=array(
            'title' =>'参与玛姆香槟F1竞猜，尽享礼遇！',
            'description' =>'即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼...',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_F1.jpg',
            'url' => $url
        );
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $record['title'];
        $desc = $record['description'];
        $picUrl = $record['picUrl'];
        $url = $record['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
    }
	
    private function checkGame($fromUsername, $toUsername) {
         $url = 'http://beyonddev1.sinaapp.com/index.html?openid='.$fromUsername;
//        $url = 'http://beyonddev1.sinaapp.com/cashlottery/over.html?openid='.$fromUsername;
        $record=array(
            'title' =>'参与玛姆香槟F1竞猜，尽享礼遇！',
            'description' =>'即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼...',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_F1.jpg',
            'url' => $url
        );
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $record['title'];
        $desc = $record['description'];
        $picUrl = $record['picUrl'];
        $url = $record['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
    }

    public function checkGuess($fromUsername, $toUsername) {
        $bingoMessage = array(
            'title' =>'2014 F1 意大利 蒙扎站竞猜结果',
            'description' =>'激动么？！竞猜结果马上要公布！MUMM香槟即将开启！',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' =>'http://beyonddev1.sinaapp.com/cashlottery/bingo.html?openid='.$fromUsername
        );
        $missMessage = array(
            'title' =>'2014 F1 意大利 蒙扎站竞猜结果',
            'description' =>'激动么？！竞猜结果马上要公布！MUMM香槟即将开启！',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' =>'http://beyonddev1.sinaapp.com/cashlottery/miss.html?openid='.$fromUsername
        );
        $isBingo = $this->checkLottery($fromUsername);
//                        $isBingo = $this->checkLottery("oxqECj7JTrpVG7BfJnNCUpQap0Xc");
//                        $isBingo = true;
        $content = $missMessage;
        if ($isBingo) {
            $content = $bingoMessage;
        }
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $content['title'];
        $desc = $content['description'];
        $picUrl = $content['picUrl'];
        $url = $content['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
    }

    public function testGuess($fromUsername, $toUsername) {
        $bingoMessage = array(
            'title' =>'2014 F1 新加坡站竞猜结果',
            'description' =>'激动么？！竞猜结果马上要公布！MUMM香槟即将开启！',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' =>'http://beyonddev1.sinaapp.com/cashlottery/bingo_test.html?openid='.$fromUsername
        );
        $missMessage = array(
            'title' =>'2014 F1 新加坡站竞猜结果',
            'description' =>'激动么？！竞猜结果马上要公布！MUMM香槟即将开启！',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' =>'http://beyonddev1.sinaapp.com/cashlottery/miss_test.html?openid='.$fromUsername
        );
        $isBingo = $this->testLottery($fromUsername);
        $content = $missMessage;
        if ($isBingo) {
            $content = $bingoMessage;
        }
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $content['title'];
        $desc = $content['description'];
        $picUrl = $content['picUrl'];
        $url = $content['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
    }

    public function wrong($fromUsername, $toUsername) {
        $missMessage = array(
            'title' =>'2014 F1 意大利 蒙扎站竞猜结果',
            'description' =>'激动么？！竞猜结果马上要公布！MUMM香槟即将开启！',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' =>'http://beyonddev1.sinaapp.com/cashlottery/miss.html?openid='.$fromUsername
        );
        $content = $missMessage;
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $content['title'];
        $desc = $content['description'];
        $picUrl = $content['picUrl'];
        $url = $content['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
    }

    public function testWrong($fromUsername, $toUsername) {
        $missMessage = array(
            'title' =>'2014 F1 新加坡站竞猜结果',
            'description' =>'激动么？！竞猜结果马上要公布！MUMM香槟即将开启！',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' =>'http://beyonddev1.sinaapp.com/cashlottery/miss_test.html?openid='.$fromUsername
        );
        $content = $missMessage;
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $content['title'];
        $desc = $content['description'];
        $picUrl = $content['picUrl'];
        $url = $content['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
    }

    public function gameOver($fromUsername, $toUsername) {
        $url = 'http://beyonddev1.sinaapp.com/cashlottery/over.html?openid='.$fromUsername;
        $overMessage=array(
            'title' =>'参与玛姆香槟F1竞猜，尽享礼遇！',
            'description' =>'即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼...',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_F1.jpg',
            'url' => $url
        );
        $content = $overMessage;
        $header = sprintf($this->newsTplHead, $fromUsername, $toUsername, time());
        $title = $content['title'];
        $desc = $content['description'];
        $picUrl = $content['picUrl'];
        $url = $content['url'];
        $body = sprintf($this->newsTplBody, $title, $desc, $picUrl, $url);
        $FuncFlag = 0;
        $footer = sprintf($this->newsTplFoot, $FuncFlag);
        return $header.$body.$footer;
    }
}