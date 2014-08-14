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

    public function responseMsg()
    {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            echo "访问路径非法！";
            return;
        }
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

            if (!empty($keyword)) {
                switch ($keyword)
                {
                    case "?":
                    case "？":
                        $msgType = "text";
                        $contentStr = date("Y-m-d H:i:s",time());
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        break;
                    case "m":
                    case "M":
                        $msgType = "text";
                        // $urlToGetUserProfile = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=".OPENID."&lang=zh_CN"
                        $contentStr = date("Y-m-d H:i:s",time())."OPENID: ".$fromUsername;
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        break;
                    case "f1":
                    case "F1":
                        $resultStr = $this->checkGame($fromUsername, $toUsername);
                        echo $resultStr;
                        break;
                    case "赛程":
                        $resultStr = $this->checkSchedule($fromUsername, $toUsername);
                        echo $resultStr;
                        break;
                    case "中奖":
                    case "未中奖":
                        $resultStr = $this->checkGuess($fromUsername, $toUsername);
                        echo $resultStr;
                        break;
                    default :
                        $msgType = "text";
                        $contentStr = "感谢您关注【玛姆香槟人生玩家】\n请回复内容：\n 1. \" F1 \"  - (参与F1冠军竞猜)\n 2. \" 中奖 \"  - (查询竞猜中奖)\n 3. \" 赛程 \"  - (查看F1赛程)";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        break;
                }
            } else {
                echo "查询文字不能为空白";
            }
        }else{
            echo "";
            exit;
        }
    }

    private function checkSchedule($fromUsername, $toUsername) {
        $url = 'http://beyonddev1.sinaapp.com/info/schedule.html?openid='.$fromUsername;
        $record=array(
            'title' =>'2014年 F1赛程表',
            'description' =>'即刻加入我们的竞猜吧！玛姆香槟预祝每位 人生玩家 获得玩家好礼...',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_schedule.jpg',
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

    private function checkGame($fromUsername, $toUsername) {
        $url = 'http://beyonddev1.sinaapp.com/info/index.html?openid='.$fromUsername;
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
            'title' =>'2014 F1 比利时 斯帕站竞猜结果',
            'description' =>'激动么？！竞猜结果马上要公布！MUMM香槟即将开启！',
            'picUrl' => 'http://beyonddev1.sinaapp.com/images/m_result.jpg',
            'url' =>'http://beyonddev1.sinaapp.com/cashlottery/bingo.html?openid='.$fromUsername
        );
        $missMessage = array(
            'title' =>'2014 F1 比利时 斯帕站竞猜结果',
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
}