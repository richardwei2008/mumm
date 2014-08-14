<?php
// echo dirname(__FILE__)."/config/DbConfig.php";
require_once(dirname(__FILE__) . "/config/DbConfig.php");
use database\DbConfig;
try {
    $conn = new PDO("mysql:host=".DbConfig::DB_HOST.";dbname=".DbConfig::DB_NAME, DbConfig::DB_USER, DbConfig::DB_PASSWORD,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
    echo "Connected to ".DbConfig::DB_NAME." at ".DbConfig::DB_HOST." successfully.";
} catch (PDOException $pe) {
    die("Could not connect to the database ".DbConfig::DB_NAME." :" . $pe->getMessage());
}
