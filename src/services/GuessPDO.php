<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-12
 * Time: 下午11:09
 */

namespace guess;
header('Content-Type: text/html; charset=utf-8');
require_once(dirname(__FILE__) . "/config/DbConfig.php");

use \PDO;
use \PDOException;
use database\DbConfig;

class GuessPDO {

    private $conn = null;

    /**
     * @param null|\PDO $conn
     */
    public function setConn($conn)
    {
        $this->conn = $conn;
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

    public function insert($userid, $openid, $game, $racer_team){
        try {
            $mumm_customer_guess = array(
                ':userid'               => $userid,
                ':openid'               => $openid,
                ':game'                 => $game,
                ':racer_team'           => $racer_team);
            $sql = 'INSERT INTO mumm_customer_guess(userid, openid, game, racer_team)
                    VALUES(:userid, :openid, :game, :racer_team)';
            $q = $this->getConn()->prepare($sql);
            $result = $q->execute($mumm_customer_guess);
            // $q->debugDumpParams();
            return $result;
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function insertSingleRow($userid, $openid, $game, $racerTeam, $racer,
            $pubLocation, $pub, $purchaseCode, $cellphone){
        try {
            $mumm_customer_guess = array(
                ':userid'               => $userid,
                ':openid'               => $openid,
                ':game'                 => $game,
                ':racer_team'           => $racerTeam,
                ':racer'                => $racer,
                ':pub_location'        => $pubLocation,
                ':pub'                  => $pub,
                ':purchase_code'      => $purchaseCode,
                ':cellphone'           =>   $cellphone);
            $sql = 'INSERT INTO mumm_customer_guess(userid, openid, game, racer_team, racer, pub_location, pub, purchase_code, cellphone)
                    VALUES(:userid, :openid, :game, :racer_team, :racer, :pub_location, :pub, :purchase_code, :cellphone)';
            $q = $this->getConn()->prepare($sql);
            $result = $q->execute($mumm_customer_guess);
            // $q->debugDumpParams();
            return $result;
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function findByOpenIdAndGame($openid, $game){
        try {
            $mumm_customer_guess = array(
                ':openid' => $openid,
                ':game' => $game
            );

            $sql = 'SELECT *
                    FROM mumm_customer_guess
                    WHERE openid  = :openid
                    AND game  = :game';

            $q = $this->getConn()->prepare($sql);
            $q->execute(array(':openid' => $openid, ':game' => $game));
            $q->setFetchMode(PDO::FETCH_ASSOC);
            while ($r = $q->fetchObject()) {
                return $r;
            }
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function findBingo($openid, $game){
        try {
            $bingo = 1;
            $mumm_customer_guess = array(
                ':openid' => $openid,
                ':bingo' => $bingo,
                ':game' => $game
            );

            $sql = 'SELECT *
                    FROM mumm_customer_guess
                    WHERE openid  = :openid
                    AND bingo  = :bingo
                    AND game  = :game';

            $q = $this->getConn()->prepare($sql);
            $q->execute(array(':openid' => $openid, ':bingo' => $bingo, ':game' => $game));
            $q->setFetchMode(PDO::FETCH_ASSOC);
            while ($r = $q->fetchObject()) {
                return $r;
            }
        } catch (Exception $ex) {
            echo($ex->getMessage());
        }
    }

    public function findBingoCellphone($cellphone, $game){
        try {
            $bingo = 1;
            $mumm_customer_guess = array(
                ':cellphone' => $cellphone,
                ':bingo' => $bingo,
                ':game' => $game
            );

            $sql = 'SELECT *
                    FROM mumm_customer_guess
                    WHERE cellphone  = :cellphone
                    AND bingo  = :bingo
                    AND game  = :game';

            $q = $this->getConn()->prepare($sql);
            $q->execute(array(':cellphone' => $cellphone, ':bingo' => $bingo, ':game' => $game));
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
//$obj = new GuessPDO();

// TEST

// insert
// try {
//     if($obj->insertSingleRow(1, 'oxqECj7JTrpVG7BfJnNCUpQap0Xc', 'Belgium斯帕',
//             '红牛车队', 'Sebastian Vettel',  '杭州', 'Linx', '', '13521932814')) {
//         echo '<br>A new task has been added successfully.<br>';
//     } else {
//         echo '<br>Error adding the task.<br>';
//     }
//     if($obj->insertSingleRow(2, 'oxqECj13-L8dz--q6dd9Z34ouTfc', 'Belgium斯帕',
//             '法拉利', 'Fernando Alonso',  '北京', 'Circle', 'BJC', '13581201192')) {
//         echo '<br>A new task has been added successfully.<br>';
//     } else {
//         echo '<br>Error adding the task.<br>';
//     }
// } catch (Exception $ex) {
//        echo($ex->getMessage());
// }

// find
//try {
//    if($guess = $obj->findByOpenIdAndGame('oxqECj7JTrpVG7BfJnNCUpQap0Xc', 'Belgium斯帕')) {
//        echo '<br>Found.<br>'.(var_dump($guess)); // htmlentities($guess->game, ENT_QUOTES, "UTF-8"); // json_encode($guess->game)
//    } else {
//        echo '<br>Error.<br>';
//    }
//} catch (Exception $ex) {
//    echo($ex->getMessage());
//}

//try {
//    if($guess = $obj->findBingo('oxqECj7JTrpVG7BfJnNCUpQap0Xc', 'Belgium斯帕')) {
//        echo '<br>Found.<br>'.(var_dump($guess)); // htmlentities($guess->game, ENT_QUOTES, "UTF-8"); // json_encode($guess->game)
//    } else {
//        echo '<br>Error.<br>';
//    }
//
//    if($guess = $obj->findBingo('oxqECj13-L8dz--q6dd9Z34ouTfc', 'Belgium斯帕')) {
//        echo '<br>Found.<br>'.(var_dump($guess)); // htmlentities($guess->game, ENT_QUOTES, "UTF-8"); // json_encode($guess->game)
//    } else {
//        echo '<br>Not Found.<br>'.(var_dump($guess));
//    }
//} catch (Exception $ex) {
//    echo($ex->getMessage());
//}

//$v = explode('-', 'abc-txt');
//echo end($v).'<br>';
//echo $v[0];

