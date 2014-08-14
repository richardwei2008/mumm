<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-12
 * Time: 下午11:09
 */

namespace customer;
require_once(dirname(__FILE__) . "/config/DbConfig.php");

use \PDO;
use \PDOException;
use database\DbConfig;

class CustomerPDO {

    private $conn = null;

    /**
     * @param null|\PDO $conn
     */
    public function setConn($conn)
    {
        return $this->conn;
    }

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
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8")
                );
                //            echo "Connected successfully.";
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
//        $this->getConn();
//        $connectionString = sprintf("mysql:host=%s;dbname=%s",
//            DbConfig::DB_HOST,
//            DbConfig::DB_NAME);
//        try {
//            $this->conn = new PDO($connectionString,
//                DbConfig::DB_USER,
//                DbConfig::DB_PASSWORD,
//                array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8")
//            );
//             echo "Connected successfully.";
//        } catch (PDOException $pe) {
//            die($pe->getMessage());
//        }
    }

    public function insertSingleRow($openid){
        try {
            $mumm_customer_info = array(
                ':openid' => $openid);

            $sql = 'INSERT INTO mumm_customer_info(openid)
                    VALUES(:openid)';
            $q = $this->getConn()->prepare($sql);

            if($q->execute($mumm_customer_info)) {
                return $this->getConn()->lastInsertId();
            } else {
                return 0;
            }
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function findByOpenId($openid){
        try {
            $mumm_customer_info = array(
                ':openid' => $openid);

            $sql = 'SELECT id, openid
                    FROM mumm_customer_info
                    WHERE openid  = :openid';

            $q = $this->getConn()->prepare($sql);
            $q->execute(array(':openid' => $openid));
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
// $obj = new CustomerPDO();

// TEST

// insert
// try {
//     if($c =$obj->insertSingleRow('oxqECj7JTrpVG7BfJnNCUpQap012')) {
//         echo 'A new task has been added successfully.<br>'.json_encode($c);
//     } else {
//         echo 'Error adding the task';
//     }
//     if($obj->insertSingleRow('oxqECj13-L8dz--q6dd9Z34ouTfc')) {
//         echo 'A new task has been added successfully.<br>';
//     } else {
//         echo 'Error adding the task';
//     }
// } catch (Exception $ex) {
//        echo($ex->getMessage());
// }

//find
//try {
//    if($c = $obj->findByOpenId('oxqECj7JTrpVG7BfJnNCUpQap0Xc')) {
//        echo 'A new task has been added successfully.<br>'.json_encode($c);
//    } else {
//        echo 'Error adding the task';
//    }
//} catch (Exception $ex) {
//       echo($ex->getMessage());
//}