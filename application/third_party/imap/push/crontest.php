<?php

// This script should be run as a background process on the server. It checks
// every few seconds for new messages in the database table push_queue and 
// sends them to the Apple Push Notification Service.
//
// Usage: php push.php development &
//    or: php push.php production &
//
// The & will detach the script from the shell and run it in the background.
//
// The "development" or "production" parameter determines which APNS server
// the script will connect to. You can configure this in "push_config.php".
// Note: In development mode, the app should be compiled with the development
// provisioning profile and it should have a development-mode device token.
//
// If a fatal error occurs (cannot establish a connection to the database or
// APNS), this script exits. You should probably have some type of watchdog
// that restarts the script or at least notifies you when it quits. If this
// script isn't running, no push notifications will be delivered!

$ret = "";
try {

    include_once 'function.php';
    include_once 'push_config.php';
    include_once 'mypush.php';


    date_default_timezone_set("UTC");
    $obj = new UserTable($g_config);
    $ret = $obj->start();

    //$obj->start();
} catch (Exception $e) {
    //fatalError($e);

    error($e);
}
outputresult($ret);
////////////////////////////////////////////////////////////////////////////////

class UserTable {

    private $fp = NULL;
    private $host = "localhost";
    private $dbname = "reward";
    private $username = "root";
    private $password = "";
    private $config;

    function setJsonHeader() {
        header('Content-Type: application/json');
    }

    function __construct($config) {
        $this->host = $config['host'];
        $this->dbname = $config['dbname'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        // Create a connection to the database.
        $this->pdo = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->dbname, $this->username, $this->password, array());

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->query('SET NAMES utf8mb4');

        $this->config = $config;
    }

    function start() {
        $retjson = array();
        $ret = array();
        try {
            $time = date("Y-m-d H:i:s", time());
            $sql = "select *from rd_project where rp_endtime <= '$time' and rp_result = 0";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $list_rdproject = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (is_array($list_rdproject) && count($list_rdproject) > 0) {
                $inSqlIds = makeIdsStringForSql($list_rdproject, "rp_id");

                try {

                    $this->pdo->beginTransaction();
                    //update project result field
                    $sql = "update rd_project set rp_result = 1 where rp_endtime <= '$time' and rp_result = 0";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();


                    $sql = "SELECT B.* FROM rd_project AS A INNER JOIN rd_bid AS B"
                            . " ON A.rp_id = B.rp_id and A.ru_winner = B.ru_bidder"
                            . " where A.rp_id in $inSqlIds"
                            . " ORDER BY A.rp_id ASC ";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();
                    $list_bids_winner = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $sql = "SELECT B.* FROM rd_project AS A INNER JOIN rd_bid AS B"
                            . " ON A.rp_id = B.rp_id"
                            . " where A.rp_id in $inSqlIds"
                            . " ORDER BY A.rp_id ASC ";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();
                    $list_bids_4project = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $inSql = makeIdsStringForSql($list_bids_4project, "rb_id");
                    if (strlen($inSql) > 0) {
                        $sql = "update rd_bid set rb_result = 1 where rb_id in $inSql";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();
                    }
                    $inSql = makeIdsStringForSql($list_bids_winner, "rb_id");
                    if (strlen($inSql) > 0) {
                        $sql = "update rd_bid set rb_result = 2 where rb_id in $inSql";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute();
                    }


                    $ret['row'] = $inSqlIds;
                    $ret['response'] = 200;
                    $this->pdo->commit();
                } catch (Exception $ex) {
                    $this->pdo->rollBack();
                    $ret['response'] = 101;
                    $ret['sql'] = $sql;
                }
            } else {
                $ret['desc'] = "Nothing to update";
                return $ret;
            }
        } catch (Exception $ex) {
            $ret['response'] = 102;
            $ret['sql'] = $sql;
        }
        return $ret;
    }

}

//update bid table for winner
//                $sql = "SELECT * FROM rd_bid AS C INNER JOIN "
//                        . " (SELECT MAX( B.rb_place_coin ) AS maxcoin, B.rp_id AS projectid FROM rd_project AS A"
//                        . " INNER JOIN rd_bid AS B ON A.rp_id = B.rp_id"
//                        . " where B.rp_id in $inSqlIds"
//                        . " GROUP BY A.rp_id ORDER BY A.rp_id ASC ) AS D"
//                        . " ON C.rb_place_coin = D.maxcoin AND C.rp_id = D.projectid";
//   