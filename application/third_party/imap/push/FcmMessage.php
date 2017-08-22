<?php

/*
  Parameter Example
  $data = array('post_id'=>'12345','post_title'=>'A Blog post');
  $target = 'single tocken id or topic name';
  or
  $target = array('token1','token2','...'); // up to 1000 in one request
 */

function sendMessage($data, $target) {
//FCM api URL
    $url = 'https://fcm.googleapis.com/fcm/send';
//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    $server_key = 'PASTE_YOUR_SERVER_KEY_HERE';

    $fields = array();
    $fields['data'] = $data;
    if (is_array($target)) {
        $fields['registration_ids'] = $target;
    } else {
        $fields['to'] = $target;
    }
//header with content_type api key
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $server_key
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}
