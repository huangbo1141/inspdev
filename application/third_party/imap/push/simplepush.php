<?php

// Put your device token here (without spaces):
$deviceToken = 'fc931d21fca88641858758ac4777f98ac8e425d0e334ebc47878661203be517d';

$deviceToken = 'c122fd45519513c76f9047b3ebe59e2d2e54f62a821d4cf40f5a3e8e54bcfb59';


// Put your private key's passphrase here:
$passphrase = 'twinklestar';
//$passphrase = '';

// Put your alert message here:
$message = 'Game Request';

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', '/home/travpholer/www/adminuser/assets/rest/apns/push/ckPro.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
$fp = stream_socket_client(
	'ssl://gateway.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;


$jsonstr = '{
    "aps" :  {
        "alert" : "You’re invited!",
        "content-available" : 1
    }
}';
$jsonstr = '{
    "aps" :  {
        "alert" : "You’re invited!",
        "category" : "INVITE_CATEGORY4",
		"sound":"default"
    }
}';



$jsonstr = '{ "aps" : { "alert" : "test", "category" : "INVITE_CATEGORY4", "sound":"default" }, "sq_id":"4" }';

$jsonstr = '{
    "aps" :  {
        "alert" : "You’re invited!",
		"sound":"default"
    }
}';
	$body = json_decode($jsonstr,true);
// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
	echo 'Message not delivered' . PHP_EOL;
else
	echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);
