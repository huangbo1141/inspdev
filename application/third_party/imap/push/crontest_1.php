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


try {

    include_once 'function.php';
    include_once 'push_config.php';
    include_once 'mypush.php';


    date_default_timezone_set("UTC");
    $obj = new UserTable($g_config);
    $obj->start();

    //$obj->start();
} catch (Exception $e) {
    //fatalError($e);
    error($e);
}

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
        $this->host = $config['db']['host'];
        $this->dbname = $config['db']['dbname'];
        $this->username = $config['db']['username'];
        $this->password = $config['db']['password'];
        // Create a connection to the database.
        $this->pdo = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->dbname, $this->username, $this->password, array());

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->query('SET NAMES utf8mb4');

        $this->config = $config;
    }

    function start() {
        $retjson = array();
        try {
            $this->pdo->beginTransaction();
            $datas = array("payload" => "cron payload", "time_queued" => 'now()');

            // update the endtime just pass the current time
            $sql = "update reward_raffle set rrf_status = 2 "
                    . " where rrf_status = 0 and "
                    . " rrf_endtime <= CONVERT_TZ( NOW(), @@session.time_zone, '+00:00' )";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            //echo $sql."<br>";
            $this->pdo->commit();
        } catch (Exception $ex) {
            $this->pdo->rollBack();
            $retjson['error'] = "error";
            $retjson['rp_profile'] = "there was rollback";
            echo json_encode($retjson);
            return;
        }

        date_default_timezone_set("UTC");
        $dt = new DateTime();
        $mutc =  $dt->format('Y-m-d H:i:s');
        //get the raffle which in 15 minutes until endtime
        $sql = "select A.*,B.rp_rid,D.device_token from reward_raffle as A "
                . " inner join reward_undertake_raffle as B on A.rrf_id = B.rrf_rid "
                . " inner join reward_setting as C  on C.rp_rid = B.rp_rid"
                . " left join active_users as D     on D.rp_rid = B.rp_rid"
                . " where A.rrf_status = 0  AND A.rrf_apn_proceed != 1"
                . " and  ( TIMESTAMPDIFF( MINUTE ,'$mutc', DATE_FORMAT(  A.rrf_endtime ,  '%Y-%m-%d %H:%i:%s' )   )) between 0 and 15"
                . " and  C.rst_raffle_before = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //echo $sql."<br>";
        $obj = new APNS_Push($this->config, $this->pdo);
        $pushret = $obj->sendRaffleTimePush($messages);


        $retjson = array();
        $retjson['error'] = "no";
        $retjson['sql'] = $sql;
        $retjson['messages'] = $messages;
        //$retjson['pushret'] = $pushret;

        echo json_encode($retjson);
    }

}
