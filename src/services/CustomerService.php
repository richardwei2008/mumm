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

$customerService = new CustomerService();

$customerService->makeGuess($requestObj);

class CustomerService {
    public function makeGuess($requestObj) {
        $game = AppConfig::GAME;
        $customer = null;
        $openid = $requestObj->openid;
        if (null === $openid || 'null' === $openid || '' === trim($openid)) {
            $openid = 'FakeId';
        }
        if (null != $openid && "" != trim($openid)) {
            $customerPDO = new CustomerPDO();
            $guessPDO = new GuessPDO();
            $customer = $customerPDO->findByOpenId($openid);
            $guess = $guessPDO->findByOpenIdAndGame($openid, $game);
            $userid = (null == $customer ? null : $customer->id);
            if ($customer == null) {
                $id = $customerPDO->insertSingleRow($openid);
                if ($id > 0) {
                    $userid = $id;
//                    echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'test'=>$userid, 'object'=>$customer, 'message'=>"Successfully inserted "));
                }
//                else {
//                    echo json_encode(array('success'=>false, 'type'=>'error', 'request'=>$requestObj, 'test'=>$userid, 'object'=>$customer, 'message'=>"Error Occur "));
//                }
//                echo json_encode(array('test'=>$id));
            }
//            else {
//                echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'test'=>$userid, 'object'=>$customer, 'message'=>"Customer Exist "));
//            }
            if ($guess == null) {
//                echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'racerTeam'=>$racerTeam, 'object'=>$guess, 'message'=>"Successfully updated "));
                $v = explode('-', $requestObj->data->racerChoice);
                $racerTeam = $v[0];
                $racer = end($v);
                $v = explode('-', $requestObj->data->pubChoice);
                $pubLocation = $v[0];
                $pub = end($v);
                $purchaseCode = $requestObj->data->purchaseCode;
                if (!is_null($purchaseCode) && (is_string($purchaseCode))) {
                    $purchaseCode = strtoupper($purchaseCode);
                }
                $cellphone = $requestObj->data->cellphone;
                if ($guessPDO->insertSingleRow($userid, $openid, $game, $racerTeam, $racer,
                    $pubLocation, $pub, $purchaseCode, $cellphone)) {
                    echo json_encode(array('success'=>true, 'type'=>'info', 'request'=>$requestObj, 'test'=>$userid, 'object'=>$guess, 'message'=>"Successfully updated "));
                } else {
                    echo json_encode(array('success'=>false, 'type'=>'error', 'request'=>$requestObj, 'object'=>$guess, 'message'=>"Error Occur "));
                }
            } else {
                echo json_encode(array('success'=>false, 'type'=>'warn', 'code'=>'E001', 'request'=>$requestObj,  'test'=>$userid, 'object'=>$guess, 'message'=>"您已提交过竞猜。<br>每位客户每站比赛只能提交一次竞猜"));
            }
        } else {
            echo json_encode(array('success'=>false, 'type'=>'info', 'request'=>$requestObj, 'object'=>null, 'message'=>"Invalid Open Id [".$openid."]"));
        }
    }
}

