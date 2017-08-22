<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function writeToLog($message) {
    /* global $config;
      if ($fp = fopen($config['logfile'], 'at')) {
      fwrite($fp, date('c') . ' ' . $message . PHP_EOL);
      fclose($fp);
      } */
    if (isset($_REQUEST['printmode']) && $_REQUEST['printmode'] == 1) {
        echo $message;
    }
}

function fatalError($message) {
    
}

class APNS_Push {

    private $fp = NULL;
    private $server;
    private $certificate;
    private $passphrase;
    private $pdo;

    function __construct($param1, $pdo) {
        $config = $param1['pushconfig'];
        $this->server = $config['server'];
        $this->certificate = $config['certificate'];
        $this->passphrase = $config['passphrase'];
        $this->pdo = $pdo;
        if (isset($_REQUEST['printmode']) && $_REQUEST['printmode'] == 1) {
            echo "APNS_PUSH config:<br/>";
            //echo $config['certificate']." <br/>";
            //var_dump($config);
        }
    }

    function sendCalendarMessage($iosids, $data) {
        if (!$this->connectToAPNS()) {
            return FALSE;
        }
        $cnt = 0;
        foreach ($iosids as $token) {
            switch ($data['type']) {
                case 1: {
                        $info_rank = $data['rdata'];
                        $msg = "Congratulations! You have reached "
                                . $info_rank['tbr_title']
                                . " - Level "
                                . $info_rank['tbr_rank'];
                        break;
                    }
                case 2: {
                        $rdata = $data['rdata'];
                        $msg = "Message Received";
                        if (isset($rdata['ttc_subject'])) {
                            $msg = $rdata['ttc_subject'];
                        }
                        break;
                    }

                default : {
                        $msg = "Congratulations";
                        break;
                    }
            }
            $body = array("aps" => array("alert" => $msg), "data" => $data);
            $payload = json_encode($body);
            $ret = $this->sendNotification('1', $token, $payload);
            $cnt++;
        }
        return TRUE;
    }

    // Opens an SSL/TLS connection to Apple's Push Notification Service (APNS).
    // Returns TRUE on success, FALSE on failure.
    function connectToAPNS() {
        writeToLog("Certificated Path:");
        writeToLog($this->certificate);
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->certificate);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);

        //echo $this->server;
        $this->fp = stream_socket_client(
                'ssl://' . $this->server, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$this->fp) {
            writeToLog($this->certificate);
            writeToLog($this->server);
            writeToLog("Failed to connect: $err $errstr");

            return FALSE;
        }

        writeToLog('Connection OK');
        return TRUE;
    }

    // Drops the connection to the APNS server.
    function disconnectFromAPNS() {
        fclose($this->fp);
        $this->fp = NULL;
    }

    // Attempts to reconnect to Apple's Push Notification Service. Exits with
    // an error if the connection cannot be re-established after 3 attempts.
    function reconnectToAPNS() {
        $this->disconnectFromAPNS();

        $attempt = 1;

        while (true) {
            writeToLog('Reconnecting to ' . $this->server . ", attempt $attempt");

            if ($this->connectToAPNS())
                return true;

            if ($attempt++ > 3) {
                fatalError('Could not reconnect after 3 attempts');
                return false;
            }

            sleep(60);
        }
    }

    // Sends a notification to the APNS server. Returns FALSE if the connection
    // appears to be broken, TRUE otherwise.
    function sendNotification($messageId, $deviceToken, $payload) {
        //echo $deviceToken."<br>";
        //echo $payload."<br>";
        if (strlen($deviceToken) != 64) {
            writeToLog("Message $messageId has invalid device token " . $deviceToken);
            return TRUE;
        }

        if (strlen($payload) < 10) {
            writeToLog("Message $messageId has invalid payload");
            return TRUE;
        }

        writeToLog("Sending message $messageId to '$deviceToken', payload: '$payload'");

        if (!$this->fp) {
            writeToLog('No connection to APNS');
            return FALSE;
        }

        // The simple format
        $msg = chr(0)                       // command (1 byte)
                . pack('n', 32)                // token length (2 bytes)
                . pack('H*', $deviceToken)     // device token (32 bytes)
                . pack('n', strlen($payload))  // payload length (2 bytes)
                . $payload;                    // the JSON payload

        /*
          // The enhanced notification format
          $msg = chr(1)                       // command (1 byte)
          . pack('N', $messageId)        // identifier (4 bytes)
          . pack('N', time() + 86400)    // expire after 1 day (4 bytes)
          . pack('n', 32)                // token length (2 bytes)
          . pack('H*', $deviceToken)     // device token (32 bytes)
          . pack('n', strlen($payload))  // payload length (2 bytes)
          . $payload;                    // the JSON payload
         */

        $result = @fwrite($this->fp, $msg, strlen($msg));

        if (!$result) {
            writeToLog('Message not delivered');
            return FALSE;
        }

        writeToLog('Message successfully delivered');
        return TRUE;
    }

}
