<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-12
 * Time: 下午10:16
 */

namespace pub;
require_once(dirname(__FILE__) . "/config/DbConfig.php");

use \PDO;
use \PDOException;
use database\DbConfig;
class PubPDO {
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

    public function insertSingleRow($pubName, $code, $amount){
        try {
            $mumm_pub_storage = array(
                ':pubName' => $pubName,
                ':code' => $code,
                ':amount' => $amount);

            $sql = 'INSERT INTO mumm_pub_storage(pub, code, promoted_gift_amount)
                    VALUES(:pubName, :code, :amount)';
            $q = $this->getConn()->prepare($sql);

            return $q->execute($mumm_pub_storage);
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function findPub($pubCode){
        if (is_null($pubCode) || (is_string($pubCode) &&  '' === trim($pubCode))) {
            return null;
        }
        try {
            $mumm_pub_storage = array(
                ':pubCode' => strtoupper($pubCode)
            );
            $sql = 'SELECT *
                    FROM mumm_pub_storage
                    WHERE code  = :pubCode';

            $q = $this->getConn()->prepare($sql);
            $q->execute($mumm_pub_storage);
            $q->setFetchMode(PDO::FETCH_ASSOC);
            while ($r = $q->fetchObject()) {
                return $r;
            }
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function updatePubStorage($pubCode){
        $mumm_pub_storage = array(':pubCode' => $pubCode);
        $sql = 'UPDATE mumm_pub_storage
            SET promoted_gift_amount  = promoted_gift_amount + 1
            WHERE code = :pubCode';
        $q = $this->getConn()->prepare($sql);
        return $q->execute($mumm_pub_storage);
    }
}

// db setup test
// $obj = new PubPDO();

// try {
//     if($obj->insertSingleRow('K歌之王', 'SHKOP', 0)) {
//         echo 'A new task has been added successfully<br>';
//     } else {
//         echo 'Error adding the task';
//     }
// } catch (Exception $ex) {
//        echo($ex->getMessage());
// }

// try {
//     if($obj->insertSingleRow('Linx', 'HZLINX', 0)) {
//         echo 'A new task has been added successfully<br>';
//     } else {
//         echo 'Error adding the task';
//     }
//     if($obj->insertSingleRow('G+', 'HZG', 0)) {
//         echo 'A new task has been added successfully<br>';
//     } else {
//         echo 'Error adding the task';
//     }
//     if($obj->insertSingleRow('Circle', 'BJC', 0)) {
//         echo 'A new task has been added successfully<br>';
//     } else {
//         echo 'Error adding the task';
//     }
//     if($obj->insertSingleRow('Babyface', 'SHT', 0)) {
//         echo 'A new task has been added successfully<br>';
//     } else {
//         echo 'Error adding the task';
//     }
//     if($obj->insertSingleRow('LeNest', 'SZLN', 0)) {
//         echo 'A new task has been added successfully<br>';
//     } else {
//         echo 'Error adding the task';
//     }
// } catch (Exception $ex) {
//     echo($ex->getMessage());
// }

// find
//try {
//    if($pub = $obj->findPub('HZLINX')) {
//        echo $pub->promoted_gift_amount.'<br>';
//        $amount = (int)$pub->promoted_gift_amount;
//        if ($amount > 5) {
//            echo "Greater than 5";
//        } else {
//            echo "Less than or equals to 5";
//        }
//        echo '<br>Found.<br>'.(var_dump($pub));
//    } else {
//        echo '<br>Error.<br>';
//    }
//} catch (Exception $ex) {
//    echo($ex->getMessage());
//}

// update
//try {
//    if($obj->updatePubStorage('SHKOP') !== false) {
//        echo 'The task has been updated successfully';
//    } else {
//        echo 'Error updated the task';
//    }
//} catch (Exception $ex) {
//    echo($ex->getMessage());
//}