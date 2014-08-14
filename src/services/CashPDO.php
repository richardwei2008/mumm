<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-12
 * Time: 下午10:16
 */

namespace customer;
require_once(dirname(__FILE__) . "/config/DbConfig.php");

use \PDO;
use \PDOException;
use database\DbConfig;
class CashPDO {
    private $conn = null;

    /**
     * @return null|\PDO
     */
    public function getConn()
    {
        if ($this->conn == null) {
            $connectionString = sprintf("mysql:host=%s;dbname=%s",
                DbConfig::DB_HOST,
                DbConfig::DB_NAME);
            try {
                $this->conn = new PDO($connectionString,
                    DbConfig::DB_USER,
                    DbConfig::DB_PASSWORD,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8",
                    )
                );
            } catch (PDOException $pe) {
                die($pe->getMessage());
            }
        }
        return $this->conn;
    }

    /**
     * Open the database connection
     */
    public function __construct(){
        // open database connection
    }

    public function insertSingleRow($openid, $cellphone, $name, $address, $mail){
        try {
            $mumm_cash_log = array(
                ':openid' => $openid,
                ':cellphone' => $cellphone,
                ':name' => $name,
                ':address' => $address,
                ':mail' => $mail);

            $sql = 'INSERT INTO mumm_cash_log(openid, cellphone, name, address, mail)
                    VALUES(:openid, :cellphone, :name, :address, :mail)';
            $q = $this->getConn()->prepare($sql);
            return $q->execute($mumm_cash_log);
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function findCashLog($cellphone){
        try {
            $mumm_cash_log = array(
                ':cellphone' => $cellphone
            );
            $sql = 'SELECT *
                    FROM mumm_cash_log
                    WHERE cellphone  = :cellphone';

            $q = $this->getConn()->prepare($sql);
            $q->execute($mumm_cash_log);
            $q->setFetchMode(PDO::FETCH_ASSOC);
            while ($r = $q->fetchObject()) {
                return $r;
            }
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }
}

// db setup test
// $obj = new CashPDO();

// try {
//     if($obj->insertSingleRow('FakeId', '13911102393', '测试用户', 'address', 'mail')) {
//         echo 'A new task has been added successfully<br>';
//     } else {
//         echo 'Error adding the task';
//     }
// } catch (Exception $ex) {
//        echo($ex->getMessage());
// }

// find localhost/mumm/services/CashPDO.php
//try {
//    if($pub = $obj->findCashLog('13911102393')) {
//        echo '<br>Found.<br>'.(var_dump($pub)); // htmlentities($guess->game, ENT_QUOTES, "UTF-8"); // json_encode($guess->game)
//    } else {
//        echo '<br>Error.<br>';
//    }
//} catch (Exception $ex) {
//    echo($ex->getMessage());
//}