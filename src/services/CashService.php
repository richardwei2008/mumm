<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-13
 * Time: 上午8:32
 */
namespace customer;
require_once(dirname(__FILE__) . "/config/DbConfig.php");
require_once(dirname(__FILE__) . "/config/AppConfig.php");
require_once (dirname(__FILE__)."/CustomerPDO.php");
require_once (dirname(__FILE__)."/GuessPDO.php");
require_once (dirname(__FILE__)."/PubPDO.php");
require_once (dirname(__FILE__)."/CashPDO.php");

use \PDO;
use \PDOException;
use database\DbConfig;
use app\AppConfig;
use guess\GuessPDO;
use pub\PubPDO;

header('Content-Type: application/json; charset=utf-8');

$requestObj = json_decode(file_get_contents("php://input"));

$cashService = new CashService();

$cashService->cashLottery($requestObj);

class CashService {
    public function cashLottery($requestObj) {
        $game = AppConfig::GAME;
        $customer = null;
        $pubCode = $requestObj->pubCode;
        $pubPDO = new PubPDO();
        if (null === $pubCode || 'null' === $pubCode || '' === trim($pubCode)) {
            $pubCode = 'FakePubCode';
        }
        $pub = null;
        if (null != $pubCode && "" != trim($pubCode)) {
            $pub = $pubPDO->findPub($pubCode);
            if (null === $pub) {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E002', 'request'=>$requestObj, 'object'=>null, 'message'=>"酒吧识别码无效(".$pubCode.")<br>请正确填写！"));
                return;
            } else if ((int)$pub->promoted_gift_amount >= 5) {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E003', 'request'=>$requestObj, 'object'=>null, 'message'=>"非常抱歉，您来晚了<br>本店所有礼品已送出！"));
                return;
            }
        } else {
            echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E002', 'request'=>$requestObj, 'object'=>null, 'message'=>"酒吧识别码无效(".$pubCode.")<br>请正确填写！"));
            return;
        }
        $cellphone = $requestObj->cellphone;
//        echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'object'=>$cellphone, 'message'=>"Successfully updated "));
        if (null === $cellphone || 'null' === $cellphone || '' === trim($cellphone)) {
            $cellphone = 'FakeCellPhone';
        }
        if (null != $cellphone && "" != trim($cellphone)) {
            $guessPDO = new GuessPDO();
            $guess = $guessPDO->findBingoCellphone($cellphone, AppConfig::GAME);
            if (null !== $guess) {
                $pubName = (null != $pub->pub ? trim($pub->pub) : "");
                $guessPubName = (null != $guess->pub ? trim($guess->pub) : "");
                if (strcmp($pubName, $guessPubName) !== 0) {
                    echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E005', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"抱歉，您不是在本酒吧参与竞猜，无法兑奖！<br>请联系工作人员"));
                    return;
                }
                $pubPDO->updatePubStorage($pubCode);
                $cashPDO = new CashPDO();
                if ($cashLog = $cashPDO->findCashLog($cellphone)) {
                    echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E004', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"抱歉，您已兑换过礼品<br>请勿重复兑奖！"));
                    return;
                }
                $name = $requestObj->name;
                $address = $requestObj->address;
                $mail = $requestObj->mail;
                $cashPDO->insertSingleRow(null, $cellphone, $name, $address, $mail);
                echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'object'=>$requestObj->name, 'message'=>"Successfully updated "));
            } else {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E001', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"抱歉，您未中奖<br>或者您输入的手机号码有误"));
            }
        } else {
            echo json_encode(array('success'=>false, 'type'=>'info', 'request'=>$requestObj, 'object'=>null, 'message'=>"Invalid Open Id [".$cellphone."]"));
        }
    }

    public function testCashLottery($requestObj) {
        $game = AppConfig::GAME_TEST;
        $customer = null;
        $pubCode = $requestObj->pubCode;
        $pubPDO = new PubPDO();
        if (null === $pubCode || 'null' === $pubCode || '' === trim($pubCode)) {
            $pubCode = 'FakePubCode';
        }
        $pub = null;
        if (null != $pubCode && "" != trim($pubCode)) {
            $pub = $pubPDO->findPub($pubCode);
            if (null === $pub) {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E002', 'request'=>$requestObj, 'object'=>null, 'message'=>"酒吧识别码无效(".$pubCode.")<br>请正确填写！"));
                return;
            } else if ((int)$pub->promoted_gift_amount >= 10) {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E003', 'request'=>$requestObj, 'object'=>null, 'message'=>"非常抱歉，您来晚了<br>本店所有礼品已送出！"));
                return;
            }
        } else {
            echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E002', 'request'=>$requestObj, 'object'=>null, 'message'=>"酒吧识别码无效(".$pubCode.")<br>请正确填写！"));
            return;
        }
        $cellphone = $requestObj->cellphone;
//        echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'object'=>$cellphone, 'message'=>"Successfully updated "));
        if (null === $cellphone || 'null' === $cellphone || '' === trim($cellphone)) {
            $cellphone = 'FakeCellPhone';
        }
        if (null != $cellphone && "" != trim($cellphone)) {
            $guessPDO = new GuessPDO();
            $guess = $guessPDO->findBingoCellphone($cellphone, AppConfig::GAME_TEST);
            if (null !== $guess) {
                $pubName = (null != $pub->pub ? trim($pub->pub) : "");
                $guessPubName = (null != $guess->pub ? trim($guess->pub) : "");
                if (strcmp($pubName, $guessPubName) !== 0) {
                    echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E005', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"抱歉，您不是在本酒吧参与竞猜，无法兑奖！<br>请联系工作人员"));
                    return;
                }
                $pubPDO->updatePubStorage($pubCode);
                $cashPDO = new CashPDO();
                if ($cashLog = $cashPDO->findCashLog($cellphone)) {
                    echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E004', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"抱歉，您已兑换过礼品<br>请勿重复兑奖！"));
                    return;
                }
                $name = $requestObj->name;
                $address = $requestObj->address;
                $mail = $requestObj->mail;
                $cashPDO->insertSingleRow(null, $cellphone, $name, $address, $mail);
                echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'object'=>$requestObj->name, 'message'=>"Successfully updated "));
            } else {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E001', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"抱歉，您未中奖<br>或者您输入的手机号码有误"));
            }
        } else {
            echo json_encode(array('success'=>false, 'type'=>'info', 'request'=>$requestObj, 'object'=>null, 'message'=>"Invalid Open Id [".$cellphone."]"));
        }
    }
}

