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

use \PDO;
use \PDOException;
use database\DbConfig;
use app\AppConfig;
use guess\GuessPDO;

header('Content-Type: application/json; charset=utf-8');

$requestObj = json_decode(file_get_contents("php://input"));

$guessService = new GuessService();

$guessService->checkGuess($requestObj);

class GuessService {
    public function checkGuess($requestObj) {
        $game = AppConfig::GAME;
        $customer = null;
        $openid = $requestObj->openid;
        if (null === $openid || 'null' === $openid || '' === trim($openid)) {
            $openid = 'FakeId';
        }
        if (null != $openid && "" != trim($openid)) {
            $guessPDO = new GuessPDO();
            $guess = $guessPDO->findBingo($openid, AppConfig::GAME);
            if (null !== $guess) {
                echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"Successfully updated "));
            } else {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E001', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"抱歉，您未中奖！<br>请参照竞猜则重新竞猜"));
            }
        } else {
            echo json_encode(array('success'=>false, 'type'=>'info', 'request'=>$requestObj, 'object'=>null, 'message'=>"Invalid Open Id [".$openid."]"));
        }
    }
}

