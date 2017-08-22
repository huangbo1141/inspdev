<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function filterSqlFunction($tmp) {
    $pattern = array('/\'NOW\(\)\'/i');
    $replace = array('NOW()');
    $ret = preg_replace($pattern, $replace, $tmp);
    return $ret;
}


function makeInsertDataSql($datas, $table) {
    $sql = "insert into " . $table . " (";
    foreach ($datas as $key => $value) {

        $sql = $sql . $key . ",";
    }
    $sql = substr($sql, 0, -1);
    $sql = $sql . ") values ('";
    foreach ($datas as $key => $value) {
        if ($key == "ru_point") {
            $sql = substr($sql, 0, -1);

            $sql = $sql . "GeomFromText('" . $value . "'),'";
        } else {
            if (stripos($value, 'GeomFromText') !== false) {
                $sql = substr($sql, 0, -1);
                $sql = $sql . $value . ",'";
            } else {
                $sql = $sql . $value . "','";
            }
        }
    }
    $sql = substr($sql, 0, -2);
    $sql = $sql . ")";
    $sql = filterSqlFunction($sql);
    return $sql;
}

function testFunc($string) {
    Global $tablenames;
    echo"<br>";
    echo $string;
    echo"<br>";
    var_dump($tablenames);
}


function getRequestData($obj) {
    Global $tablenames, $tablefields, $definedrequest;

    $requestInput = array();
    $input = (array) $obj;
    // get table info
    foreach ($tablenames as $tablename) {
        if (isset($input[$tablename])) {
            $object = $input[$tablename];
            $objectarray = (array) $object;

            $requestInput[$tablename] = $objectarray;
        }
    }

    foreach ($definedrequest as $tag) {
        if (isset($input[$tag])) {
            $requestInput[$tag] = $input[$tag];
        }
    }
    return $requestInput;
}

function isCorrectUser($user, $connection) {
    $sql = "SELECT * FROM user WHERE username = '" . $user['username'] . "' and password='" . $user['password'] . "'";      //echo $sql;
    $result = mysql_query($sql, $connection);
    //echo $sql;
    if ($result) {
        $count = mysql_num_rows($result);
        if ($count) {
            $data = mysql_fetch_assoc($result);
            return $data;
        }
    }
    return null;
}


function isExistUser($user, $connection) {
    $sql = "SELECT * FROM user WHERE username = '" . $user['username'] . "'";      //echo $sql;
    $result = mysql_query($sql, $connection);
    //echo $sql;
    if ($result) {
        $count = mysql_num_rows($result);
        if ($count) {
            $data = mysql_fetch_assoc($result);
            return $data;
        }
    }
    return null;
}

//
function makeData($datas) {
    $array = array();
    foreach ($datas as $key => $value) {
        $array[$key] = $value;
    }
    return $array;
}


function makeWhereSql($datas) {
    $sql = "";
    //echo var_dump($datas);    echo "<br>";
    if (is_array($datas) && count($datas) > 0) {
        $sql = " where";
        foreach ($datas as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0]) && is_array($value[0])) {
                    foreach ($value as $subkey => $subval) {
                        if ($key == "create_datetime") {
                            $pieces = explode("/", $subval[1]);
                            if (count($pieces) == 3) {
                                $value1 = $pieces[2] . "-" . $pieces[1] . "-" . $pieces[0];
                                $sql = $sql . " DATE_FORMAT( " . $key . ",'%Y-%m-%d' ) " . $subval[0] . " '" . $value1 . "' and";
                            } else
                                $sql = $sql . " DATE_FORMAT( " . $key . ",'%Y-%m-%d' ) " . $subval[0] . " '" . $subval[1] . "' and";
                        } else
                            $sql = $sql . " " . $key . " " . $subval[0] . " '" . $subval[1] . "' and";
                    }
                }else if ($key == "create_datetime") {
                    $pieces = explode("/", $value[1]);
                    $value1 = $pieces[2] . "-" . $pieces[1] . "-" . $pieces[0];
                    $sql = $sql . " DATE_FORMAT( " . $key . ",'%Y-%m-%d' ) " . $value[0] . " '" . $value1 . "' and";
                } else
                    $sql = $sql . " " . $key . " " . $value[0] . " '" . $value[1] . "' and";
            }else {
                $sql = $sql . " " . $key . "='" . $value . "' and";
            }
        }
        $sql = substr($sql, 0, -3);
    } else if (is_string($datas) && strlen($datas) > 0) {
        $sql = $datas;
    } else {
        $sql = " where 1";
    }
    return $sql;
}



function makeUpdateSql($datas, $table, $wheredata) {
    $sql = "update " . $table . " set ";
    $part = "";
    foreach ($datas as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subkey => $subvalue) {
                if (is_array($wheredata) && !array_key_exists($subkey, $wheredata)) {
                    $part = $part . $subkey . "='" . $subvalue . "',";
                }
            }
        } else {
            if (is_array($wheredata) && !array_key_exists($key, $wheredata)) {
                if (stripos($value, 'GeomFromText') !== false) {
                    $part = $part . $key . "=" . $value . ",";
                } else {
                    $part = $part . $key . "='" . $value . "',";
                }
            } else {
                if (stripos($value, 'GeomFromText') !== false) {
                    $part = $part . $key . "=" . $value . ",";
                } else {
                    $part = $part . $key . "='" . $value . "',";
                }
            }
        }
    }
    if (strlen($part) > 0) {
        $part = substr($part, 0, -1);
    } else {
        return "error";
    }

    if ($wheredata != null && $wheredata != "") {
        $wherecond = makeWhereSql($wheredata);
        $sql = $sql . $part . $wherecond;
    } else {
        $sql = $sql . $part;
    }
    return $sql;
}

function makeWhereInSql($datas, $fieldname) {
    $sql = "";
    if (count($datas) > 0) {
        $sql = " where " . $fieldname . " in (";
        foreach ($datas as $key => $value) {
            $sql = $sql . " '" . $value . "',";
        }
        $sql = substr($sql, 0, -1);
        $sql = $sql . ")";
    } else {
        $sql = "where 1";
    }
    return $sql;
}


function makeObject($input, $output, $tableinfo) {
    if (!is_array($output))
        $output = array();
    foreach ($tableinfo as $value) {
        if (isset($input[$value])) {
            $output[$value] = $input[$value];
        }
    }
    return $output;
}

function extractAsArray($input, $returnfields) {
    $datas = array();
    $pattern = array('/\'/i');
    $replace = array('\\\'');
    foreach ($returnfields as $param) {
        if (isset($input[$param])) {
            $tmp = trim($input[$param]);
            if (strlen($tmp) > 0) {
                //$ret = mysql_real_escape_string($tmp);
                $ret = false;

                if (is_bool($ret) && !$ret) {
                    //$datas[$param] = $tmp;
                    $datas[$param] = preg_replace($pattern, $replace, $tmp);
                } else {
                    $datas[$param] = $ret;
                }
            }
        }
    }
    return $datas;
}

function extractAsArrayWithNoCheck($input, $returnfields) {
    $datas = array();
    foreach ($returnfields as $param) {
        if (isset($input[$param])) {
            $datas[$param] = $input[$param];
        }
    }
    return $datas;
}

function convertDatetime($param, $mode = 1) {
    switch ($mode) {
        case 1:// insert mode
        default:
            $date = DateTime::createFromFormat('j M Y - H:i', $_REQUEST[$param]);
            if (isset($_REQUEST['timezonediff'])) {
                $p = intval($_REQUEST['timezonediff']);
                $tmp = "PT" . abs($p) . "M";
                if ($p > 0)
                    $date->add(new DateInterval($tmp));
                else
                    $date->sub(new DateInterval($tmp));
            }
            return $date->format('Y-m-d H:i:s');
        case 2://output mode
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $param);
            if (isset($_REQUEST['timezonediff'])) {
                $p = intval($_REQUEST['timezonediff']);
                $tmp = "PT" . abs($p) . "M";
                if ($p > 0)
                    $date->sub(new DateInterval($tmp));
                else
                    $date->add(new DateInterval($tmp));
            }
            return $date->format('Y-m-d H:i:s');
            break;
    }
}

function error($e) {
    global $g_env;

    $retjson = array();
    $retjson['error'] = 'error';
    $retjson['message'] = $e;
    if (isset($_REQUEST['hgc_debug']) && $_REQUEST['hgc_debug'] != "") {
        echo $e->xdebug_message;
        var_dump($e);
    }

    
//    $file = '/var/www/html/adminuser/assets/uploads/log_server.txt';
//    if ($g_env == 0) {
//        $file = 'log_local.txt';
//    }
//    //$file = 'log_local.txt';
//// The new person to add to the file
//    //ob_start();
//    //var_dump($_REQUEST);
//    //$result = ob_get_clean();
//    $result = json_encode($_REQUEST);
//
//    $time = "-----------------------" . date('Y-m-d h:i:s') . "----------------------\r\n";
//    $result = $time . $result;
//// Write the contents to the file, 
//// using the FILE_APPEND flag to append the content to the end of the file
//// and the LOCK_EX flag to prevent anyone else writing to the file at the same time
//    file_put_contents($file, $result, FILE_APPEND | LOCK_EX);
//
//    echo json_encode($retjson);
}

function logrequest() {
    global $g_env;

    $file = '/var/www/html/adminuser/assets/uploads/log_server.txt';
    if ($g_env == 0) {
        $file = 'log_local.txt';
    }
    $result = json_encode($_REQUEST);
    $addr = "";
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $addr = $_SERVER['REMOTE_ADDR'];
    }
    $time = "-----------------------" . date('Y-m-d h:i:s') . " from $addr ----------------------\r\n";
    $result = $time . $result;
    file_put_contents($file, $result, FILE_APPEND | LOCK_EX);
}

function logaction($e) {
    global $g_env;
    $retjson = array();
    $retjson['error'] = 'error';

    $file = '/var/www/html/adminuser/assets/uploads/log_server.txt';
    if ($g_env == 0) {
        $file = 'log_local.txt';
    }
    $result = $e;
    file_put_contents($file, $result, FILE_APPEND | LOCK_EX);
}

function getLnt($zip) {
    $url = "http://maps.googleapis.com/maps/api/geocode/json?address=
" . urlencode($zip) . "&sensor=false";
    $result_string = file_get_contents($url);
    $result = json_decode($result_string, true);
    $result1[] = $result['results'][0];
    $result2[] = $result1[0]['geometry'];
    $result3[] = $result2[0]['location'];
    return $result3[0];
}

function getLntGeo($zip) {
    $url = "http://maps.googleapis.com/maps/api/geocode/json?address=
" . urlencode($zip) . "&sensor=false";
    $result_string = file_get_contents($url);
    $result = json_decode($result_string, true);
    $result1[] = $result['results'][0];
    $result2[] = $result1[0]['geometry'];
    $array = array();
    $array['addr'] = $result1[0]['formatted_address'];
    $array['loc'] = $result2[0]['location'];
    return $array;
}

function outputresult($data) {
    if (isset($_REQUEST['printmode']) && $_REQUEST['printmode'] == 1) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo json_encode($data);
    }
}

function makeIdsStringForSql($data, $key = false) {
    if (!is_array($data)) {
        return "";
    }
    $str = "";
    for ($i = 0; $i < count($data); $i++) {
        if ($key == false) {
            $id = $data[$i];
        } else {
            $id = $data[$i][$key];
        }

        $temp = $id . ",";
        $str = $str . $temp;
    }
    if (strlen($str) > 0) {
        $str = substr($str, 0, -1);
        $str = "(" . $str . ")";
    }
    return $str;
}

function getStoryInfoForItin($story) {
    global $g_trip_type;
    global $g_tbl_photos;
    global $g_tbl_itin_day;
    global $g_tbl_itin_rest;
    global $g_tbl_itin_transport;
    global $g_tbl_bucket_list;
    global $g_tbl_bucket_content;
    global $g_tablenames;

    $ret = [];
    if (isset($story['tp_id'])) {
        $ret['tablename'] = $g_tablenames['tbl_photos'];
        $ret['type'] = "a";
    } else if (isset($story['tir_id'])) {
        $ret['tablename'] = $g_tablenames['tbl_itin_rest'];
        $ret['type'] = "c";
    } else if (isset($story['tit_id'])) {
        $ret['tablename'] = $g_tablenames['tbl_itin_transport'];
        $ret['type'] = "b";
    }
    return $ret;
}

function getStoryInfoForTrip($story) {
    global $g_trip_type;
    global $g_tbl_photos;
    global $g_tbl_itin_day;
    global $g_tbl_itin_rest;
    global $g_tbl_itin_transport;
    global $g_tbl_bucket_list;
    global $g_tbl_bucket_content;
    global $g_tablenames;

    $ret = [];
    if (isset($story['tp_id'])) {
        $ret['tablename'] = $g_tablenames['tbl_trip_photos'];
        $ret['type'] = "a";
    } else if (isset($story['tir_id'])) {
        $ret['tablename'] = $g_tablenames['tbl_trip_rest'];
        $ret['type'] = "c";
    } else if (isset($story['tit_id'])) {
        $ret['tablename'] = $g_tablenames['tbl_trip_transport'];
        $ret['type'] = "b";
    }
    return $ret;
}

function getStoryKeySeries($list_story) {
    $sss = "";
    for ($i = 0; $i < count($list_story); $i++) {
        $story = $list_story[$i];
        if (isset($story['tp_id'])) {
            $sss = $sss . "a" . $story['tp_id'] . "-";
        } else if (isset($story['tir_id'])) {
            $sss = $sss . "c" . $story['tir_id'] . "-";
        } else if (isset($story['tit_id'])) {
            $sss = $sss . "b" . $story['tit_id'] . "-";
        }
    }
    if (strlen($sss) > 0) {
        $sss = substr($sss, 0, strlen($sss) - 1);
    }
    return $sss;
}

function getDayKeySeries($list_day) {
    $sss = "";
    for ($i = 0; $i < count($list_day); $i++) {
        $day = $list_day[$i];
        $sss = $sss . $day['tid_id'] . ";";
    }
    if (strlen($sss) > 0) {
        $sss = substr($sss, 0, strlen($sss) - 1);
    }
    return $sss;
}

function get7daysagoDate() {
    $time_first = strtotime("-3 day", time());
    $first = date("Y-m-d", $time_first);
    $first = $first . " 00:00:00";
    return $first;
}

function addid2ids($string, $id, $action, $literal) {
    if ($literal == ",") {
        $ret = "";
        if ($string == null || $string == "") {
            $ret = $friendid;
        } else {
            $pieces = explode($literal, $string);
            if ($action == "add") {
                $pieces[] = $id;
            } else {
                $pieces = array_diff($pieces, array($id));
            }

            $pieces = array_unique($pieces);
            asort($pieces);
            $val = "";
            foreach ($pieces as $value) {
                $val = $val . $value . $literal;
            }
            if (strlen($val) > 0) {
                $ret = substr($val, 0, strlen($val) - 1);
            }
        }
        return $ret;
    }
    return "";
}

function increasePointForPost($pdo, $photoid, $point) {
    $sql = "select tu_id from tbl_photos where tp_id = $photoid ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    $ret = array("response" => 400, "sql" => "");
    if ($message) {
        $tuid = $message['tu_id'];
        $ret = increasePointForUser($pdo, $tuid, $point);
    }
    return $ret;
}

function increasePointForUser($pdo, $userid, $point) {
    $sign = "+";
    if ($point >= 0) {
        $sign = "+";
    } else {
        $sign = "";
    }
    $ret = array("response" => 400, "sql" => "");
    try {

        $sql = "update tbl_user set tu_likesrecv = tu_likesrecv $sign $point where tu_id = $userid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $ret['response'] = 200;
        $ret['sql'] = $sql;
    } catch (Exception $ex) {
        $ret['response'] = 400;
        $ret['sql'] = $sql;
    }
    return $ret;
}

function echoPrint($param) {
    if (isset($_REQUEST['printmode']) && $_REQUEST['printmode'] == 1) {
        outputresult($param);
    }
}

function getPersonAch($pdo, $id) {
    global $g_table_status;
    $sql = "select * from tbl_user where tu_id = $id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    $achievements = array();
    $ach_ids = array();
    if ($message) {
        $likes_recv = $message['tu_likesrecv'];
        $tba_list = $message['tba_list'];
        $wheresql = "where 1";
        if (strlen($tba_list) > 0) {
            //$pieces = explode(",", $tba_list);    
            $wheresql = "where tba_id not in ($tba_list)";
        }


        $userid = $id;
        //photouploads
        $upload_photo = "0";
        $sql = "select count(*) as count1 from tbl_photos where tu_id = $userid and tp_status =" . $g_table_status['published'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $conqinfo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($conqinfo) {
            $upload_photo = $conqinfo['count1'];
        }
        //get tbl_base_ach
        $sql = "select * from tbl_base_ach $wheresql";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $infoAchs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        try {
            for ($i = 0; $i < count($infoAchs); $i++) {
                $infoach = $infoAchs[$i];
                $type = $infoach['tba_type'];
                $param1 = $infoach['tba_param1'];
                $param2 = $infoach['tba_param2'];

                switch ($type) {
                    case 1: {
                            // upload xx pictures
                            if ($upload_photo >= $param1) {
                                $achievements[] = $infoach;

                                $ach_ids[] = $infoach['tba_id'];
                            }
                            break;
                        }
                    case 2: {
                            //at least 1 picture exceeds $param1 likes
                            $sql = "select * from"
                                    . " ( select count(*) as count1,A.tp_id,A.tu_id from tbl_photos as A"
                                    . " inner join tbl_likes as B on A.tp_id = B.tp_id  group by A.tp_id)"
                                    . " as TABLE1 where TABLE1.count1 >= $param1 and TABLE1.tu_id = $userid";
                            //echo $sql;
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $tempinfo = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($tempinfo) {
                                $achievements[] = $infoach;
                                $ach_ids[] = $infoach['tba_id'];
                            }

                            break;
                        }
                    case 3: {
                            //been to x $param1 countries
                            $sql = "select count(*) as count1 from "
                                    . "(select B.* from countries as B"
                                    . " join tbl_photos as A on B.countryID = A.tp_countryid"
                                    . " where A.tu_id = $userid"
                                    . " group by A.tp_countryid ) as TABLE1";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $conqinfo = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($conqinfo['count1'] >= $param1) {
                                $achievements[] = $infoach;
                                $ach_ids[] = $infoach['tba_id'];
                            }

                            break;
                        }
                    case 4: {
                            $sql = "select count(*) as count1 from tbl_likes as A inner join tbl_photos as B"
                                    . " on A.tp_id = B.tp_id"
                                    . " where B.tu_id != $userid and A.tu_id = $userid";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $conqinfo = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($conqinfo['count1'] >= $param1) {
                                $achievements[] = $infoach;
                                $ach_ids[] = $infoach['tba_id'];
                            }

                            break;
                        }
                    case 5: {
                            $sql = " select count(*) as count1 from "
                                    . "( select A.tu_id from tbl_likes as A "
                                    . " inner join tbl_photos as B on A.tp_id = B.tp_id"
                                    . " where B.tu_id = $userid "
                                    . " group by A.tu_id )"
                                    . " as TABLE1";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $conqinfo = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($conqinfo['count1'] >= $param1) {
                                $achievements[] = $infoach;
                                $ach_ids[] = $infoach['tba_id'];
                            }
                            //echo $sql; echo "   ".$conqinfo['count1']."  $param1 <br/>";
                            break;
                        }
                    case 6: {
                            $pieces = explode(",", $param1);
                            $region_name = $pieces[0];
                            $xxx = $pieces[1];
                            $region_field = "continent";
                            if ($param2 == 0) {
                                $region_field = "region";
                            } else if ($param2 == 1) {
                                $region_field = "continent";
                            }


                            $expert_eastern_asia = "0";
                            $sql = "select count(*) as count1 from"
                                    . " (select count(B.tp_id) as count1 from countries as A"
                                    . " left join tbl_photos as B on A.countryID = B.tp_countryid and B.tu_id = $userid"
                                    . " where $region_field = '$region_name' group by A.countryID) as TABLE1"
                                    . " where TABLE1.count1 = 0";
//                            if (isset($_REQUEST['printmode']) && $_REQUEST['printmode'] == 1) {
//                                echo $sql;
//                                echo "<br/>";
//                            }
                            echoPrint($sql);
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $conqinfo = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($conqinfo) {
                                $count = $conqinfo['count1'];
                                if ($count > 0) {
                                    //have been to all East Asian Countries
                                    $expert_eastern_asia = "0";
                                } else {
                                    $count = "0";
                                    $sql = "select sum(count1) as count1 from "
                                            . "(select count(B.tp_id) as count1 from countries as A left join tbl_photos as B on A.countryID = B.tp_countryid and B.tu_id = $userid"
                                            . " where $region_field = '$region_name' group by A.countryID) as TABLE1";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();
                                    $conqinfo = $stmt->fetch(PDO::FETCH_ASSOC);

                                    echoPrint($sql);
                                    if ($conqinfo) {
                                        $count = $conqinfo['count1'];
                                        if ($count >= $xxx) {
                                            $expert_eastern_asia = "1";

                                            $achievements[] = $infoach;
                                            $ach_ids[] = $infoach['tba_id'];
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    case 7: {
                            $pieces = explode(",", $param1);
                            $match_cnt = 0;
                            for ($j = 0; $j < count($pieces); $j++) {
                                $parts = explode(":", $pieces[$j]);
                                $number = $parts[0];
                                $countryid = $parts[1];
                                $sql = "select count(*) as count1 from tbl_photos where tp_countryid = '$countryid' and tu_id = $userid";


                                echoPrint($sql);
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                                $conqinfo = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($conqinfo && $conqinfo['count1'] >= $number) {
                                    $match_cnt++;
                                } else {
                                    break;
                                }
                            }
                            if ($match_cnt == count($pieces) && $match_cnt > 0) {
                                $achievements[] = $infoach;
                                $ach_ids[] = $infoach['tba_id'];
                            }
                            break;
                        }
                }
            }
            $ret = array("row" => $message, "achids" => $ach_ids, "achlist" => $achievements);
        } catch (Exception $ex) {
            echoPrint("error");
            echoPrint($sql);
            $ret = false;
        }
    } else {
        $ret = false;
    }

    return $ret;
}

function updatePersonAch($pdo, $userid) {
    $info_liker = getPersonAch($pdo, $userid);
    if ($info_liker) {
        $achids = $info_liker['achids'];
        $achlist = $info_liker['achlist'];
        $row_liker = $info_liker['row'];
        if (count($achids) > 0) {
            //compare with existing
            $tba_list = $row_liker['tba_list'];
            $pieces = explode(",", $tba_list);
            $diff = array_diff($achids, $pieces);
            if (count($diff) > 0) {
                // new achievements
                $tba_list = $row_liker['tba_list'];
                if (strlen($tba_list) > 0) {
                    $tba_list = $tba_list . ",";
                }
                foreach ($achids as $achid) {
                    $tba_list = $tba_list . $achid . ",";
                }
                $tba_list = substr($tba_list, 0, -1);
                $tempdata = array("tba_list" => $tba_list);
                $sql = makeUpdateSql($tempdata, "tbl_user", array("tu_id" => $row_liker['tu_id']));
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                $newachlist = array();
                foreach ($diff as $key => $value) {
                    $newachlist[] = $achlist[$key];
                }

                $pushret = pushAchs($newachlist, $row_liker);
                $ret['push'] = $pushret;
                $ret['newachlist'] = $newachlist;
                $ret['response'] = 200;
            }
        } else {
            $ret['error'] = "no achids";
        }
    }
}

function pushAchs($achlist, $userrow) {
    // send push notification

    $rduser_list = array($userrow);

    $gcm_ret = "undefined";
    $pushret = "undefined";
    $gcmids = array();
    $iosids = array();
    $idtypes_gcm = array();
    $idtypes_ios = array();
    $langs = array();

    foreach ($rduser_list as $row) {
        if (strlen($row['tu_gcmid']) > 10) {
            $gcmids[] = $row['tu_gcmid'];
        }
        if (strlen($row['tu_apnid']) > 10) {
            $iosids[] = $row['tu_apnid'];
        }
    }

    foreach ($achlist as $ach) {
        $data = array('time' => "time", 'type' => 2, 'from1' => "from", 'rdata' => $ach);
        if (count($gcmids) > 0) {
            $apiKey = "AIzaSyDmWoxXCPJ0fOy-FCTfutU7yN3x6DCHWBA";
            $devices = $gcmids;
            $message = "The message to send";
            $gcpm = new GCMPushMessage($apiKey);
            $gcpm->setDevices($devices);
            $gcm_ret = $gcpm->send($message, $data);
        }
        //send to ios
        if (count($iosids) > 0) {
            $obj = new APNS_Push($this->config, $this->pdo);
            $pushret = $obj->sendCalendarMessage($iosids, $data);
        }
    }
    return array("gcmret" => $gcm_ret, "apnret" => $pushret);
}

function date_compare_trip_asc($a, $b) {
    $t1 = strtotime($a['ti_start_date']);
    $t2 = strtotime($b['ti_start_date']);
    return $t1 - $t2;
}

function date_compare_trip_desc($a, $b) {
    $t1 = strtotime($a['create_datetime']);
    $t2 = strtotime($b['create_datetime']);
    return $t2 - $t1;
}

function makeSeries($data, $fname = "") {
    $ret = "";
    if ($fname == "") {
        $ids = "";
        for ($i = 0; $i < count($data); $i++) {
            $ids = $ids . $data[$i] . ",";
        }
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
            $ret = $ids;
        }
    }
    return $ret;
}
