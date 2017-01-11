<?PHP

//  TDM Endpoint
$termEndpoint = "65.48.99.10";

$db = new mysqli("65.48.98.238", "freeswitch", "fr33sw1tch", "fsconference");

function getConferenceRoomInfo($confroom)
{
    global $db;
    libxml_use_internal_errors(true);
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $isXML = 0;
    $obj = new stdClass();

    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom xml_list");
        if ($xml = simplexml_load_string($e->getBody())) {
            $xmlattr = $xml->conference[0]->attributes();
            $rn = 0;
            /*if (strstr($e->getBody(), "recording_node") === false){
            $rn = 0;
            error_log("recording_node not present");
            }else{
            $rn = 1;
            error_log("recording_node present");
            }*/
            if ($xmlattr['member-count'] > 0) {
                $z = 0;
                error_log("Member Count: {$xmlattr['member-count']}");
                for ($y = 0; $y < ($xmlattr['member-count'] + $rn); $y++) {
                    $node = $xml->conference->members->member[$y]->attributes();
                    error_log("Node type[$y]: {$node['type']}");
                    if ($node['type'] == "caller") {
                        $flags = $xml->conference->members->member[$y]->flags;
                        $obj->members[$x]->member[$z]->id = changeArray($xml->conference->members->
                            member[$y]->id);
                        $obj->members[$x]->member[$z]->hear = changeArray($flags->can_hear);
                        $obj->members[$x]->member[$z]->speak = changeArray($flags->can_speak);
                        $obj->members[$x]->member[$z]->talking = changeArray($flags->talking);
                        $obj->members[$x]->member[$z]->is_moderator = changeArray($flags->is_moderator);
                        $obj->members[$x]->member[$z]->uuid = changeArray($xml->conference->members->
                            member[$y]->uuid);
                        $obj->members[$x]->member[$z]->clid_name = changeArray($xml->conference->
                            members->member[$y]->caller_id_name);
                        $obj->members[$x]->member[$z]->clid_num = changeArray($xml->conference->members->
                            member[$y]->caller_id_number);
                        $obj->members[$x]->member[$z]->join_time = changeArray($xml->conference->
                            members->member[$y]->join_time);
                        $obj->members[$x]->member[$z]->last_spoke = changeArray($xml->conference->
                            members->member[$y]->last_talking);
                    } elseif ($node['type'] == "recording_node") {
                        $z--;
                        $rn++;
                    }
                    //error_log(print_r($obj, true));
                    $z++;
                }

            }
            /*else {
            error_log("No Loop...................");
            $loop = false;
            $i=0;
            $j=0;
            while (!$loop){
            $node = $xml->conference->members->member[$i]->attributes();
            if ($node['type'] == "caller"){
            $obj->members[$x]->member[$i]->id = changeArray($xml->conference->members->member[$i]->id);
            $obj->members[$x]->member[$i]->hear = changeArray($xml->conference->members->member[$i]->flags->can_hear);
            $obj->members[$x]->member[$i]->speak = changeArray($xml->conference->members->member[$i]->flags->can_speak);
            $obj->members[$x]->member[$i]->talking = changeArray($xml->conference->members->member->flags->talking);
            $obj->members[$x]->member[$i]->is_moderator = changeArray($xml->conference->members->member[$i]->flags->is_moderator);
            $obj->members[$x]->member[$i]->uuid = changeArray($xml->conference->members->member[$i]->uuid);
            $obj->members[$x]->member[$i]->clid_name = changeArray($xml->conference->members->member[$i]->caller_id_name);
            $obj->members[$x]->member[$i]->clid_num = changeArray($xml->conference->members->member[$i]->caller_id_number);
            $obj->members[$x]->member[$i]->join_time = changeArray($xml->conference->members->member[$i]->join_time);
            $obj->members[$x]->member[$i]->last_spoke = changeArray($xml->conference->members->member[$i]->last_talking);
            $loop = true;
            }elseif ($node['type'] == "recording_node"){
            $j--;
            }
            $i++;
            $j++;
            }
            }*/
            $isXML = 1;
        } else {
            //$x--;
        }

    }
    if ($isXML) {
        $ret = $obj;


        return $ret;
    } else {
        return array("result" => false);
    }

}

function changeArray($arr)
{
    $tmp = array((string )$arr);
    return $tmp[0];
}

function getBridges($custid)
{
    global $db;
    $sql = "select a.dnis, a.confroom, b.confpass, b.confowner, b.confadminpin, b.maxuser, b.spinuser, b.spinmod ";
    $sql .= "from dnis2conf as a left join conference as b on a.confroom = b.confroom where b.confowner = '$custid'";
    $res = mysqli_query($db, $sql);
    if (!$res) {
        return array("result" => "false", "why" => mysqli_error($db));
    } else {
        while ($rows[] = mysqli_fetch_assoc($res))
            ;
        array_pop($rows);
        return $rows;
    }
}

function getConferencesbyDNIS($dnis)
{
    global $db;
    $sql = "select a.dnis, a.confroom, b.confpass, b.confowner, b.confadminpin, b.maxuser, b.spinuser, b.spinmod ";
    $sql .= "from dnis2conf as a left join conference as b on a.confroom = b.confroom where a.dnis = '$dnis'";
    $res = mysqli_query($db, $sql);
    if (!$res) {
        return array("result" => "false", "why" => mysqli_error($db));
    } else {
        while ($rows[] = mysqli_fetch_assoc($res))
            ;
        array_pop($rows);
        return $rows;
    }
}

function getConferencesbyRoom($room)
{
    global $db;

    $sql = "select * from dnis2conf where confroom=$room";
    $res = mysqli_query($db, $sql);

    if (mysqli_num_rows($res) == 1) {
        $sql = "select a.dnis, a.confroom, b.confpass, b.confowner, b.confadminpin, b.maxuser, b.spinuser, b.spinmod, b.parent, b.confexpired ";
        $sql .= "from dnis2conf as a left join conference as b on a.confroom = b.confroom where a.confroom = '$room'";
    } else {
        $sql = "select \"null\" as dnis, b.confroom, b.confpass, b.confowner, b.confadminpin, b.maxuser, b.spinuser, b.spinmod, b.parent, b.confexpired";
        $sql .= " from conference as b where b.confroom = '$room'";
    }

    error_log($sql);

    $res = mysqli_query($db, $sql);
    if (!$res) {
        return array("result" => "false", "why" => mysqli_error($db));
    } else {
        while ($rows[] = mysqli_fetch_assoc($res))
            ;
        array_pop($rows);
        return $rows;
    }
}

function getAllConferenceRooms($custid)
{
    global $db;
    $sql = "select b.confroom, b.confpass, b.confowner, b.confadminpin, b.maxuser, b.spinuser, b.spinmod ";
    $sql .= "from conference as b where b.confowner = '$custid'";
    $res = mysqli_query($db, $sql);
    if (!$res) {
        return array("result" => "false", "why" => mysqli_error($db));
    } else {
        while ($rows[] = mysqli_fetch_assoc($res))
            ;
        array_pop($rows);
        return $rows;
    }
}

function getUserConferences($userid)
{
    global $db;
    $sql = "select conf from user2conf where userid='$userid'";

    $res = mysqli_query($db, $sql);
    if (mysqli_num_rows($res) == 0) {
        return array("result" => false, "why" =>
                "need to assign some rooms to this guy...");
    } else {
        while ($rows[] = mysqli_fetch_assoc($res))
            ;
        array_pop($rows);

        return array("result" => true, "conf_rooms" => $rows);
    }

}

function userLogin($user, $pass)
{
    global $db;
    error_log("PassworD: $pass");
    $dpass = trim(decryptPassword($pass));
    error_log("Decrypted Pass: $dpass");
    $sql = "select id from users where user='$user' and pass='$dpass'";
    error_log($sql);
    $res = mysqli_query($db, $sql);
    if (mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        return array("result" => true, "userid" => "{$row['id']}");
    } else {
        return array("result" => false, "why" =>
                "I dont know.  I didnt code any debug here...");
    }

}

function lockConferenceRoom($confroom)
{
    global $db;
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $isXML = 0;
    $obj = new stdClass();
    $out = "";
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom lock");
        $out .= $e->getBody();
    }

    if (strpos($out, "OK $confroom locked") === false) {
        return array("result" => false);
    } else {
        return array("result" => true);
    }
}

function greetingPlayback($confroom)
{
    if (file_exists("/mnt/nfs/sounds/$confroom.ulaw")) {
        $contents = file_get_contents("/mnt/nfs/sounds/$confroom.ulaw");
        $base64 = base64_encode($contents);
        return array("result" => true, "filedata" => array("filename" => "$confroom.ulaw",
                    "data" => $base64));
    } else {
        return array("result" => false);
    }
}

function greetingRecord($confroom, $dnis)
{
    $endpoint = "65.48.99.10";
    $asm = new AGI_AsteriskManager();
    $asm->connect();
    $ret = $asm->Originate("SIP/$dnis@$endpoint", "$confroom", "doGreetRecord", "1");
    $asm->disconnect();
    if ($ret['Response'] != "Success") {
        return array("result" => "true", "AstResult" => $ret);
    } else {
        return array("result" => "true", "AstResult" => $ret);
    }
}

function unlockConferenceRoom($confroom)
{
    global $db;
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $isXML = 0;
    $obj = new stdClass();
    $out = "";
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom unlock");
        $out .= $e->getBody();
    }

    if (strpos($out, "OK $confroom unlocked") === false) {
        return array("result" => false);
    } else {
        return array("result" => true);
    }
}

function muteConferenceRoom($confroom)
{
    global $db;
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $isXML = 0;
    $obj = new stdClass();
    $out = "";
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom mute non_moderator");
        $out .= $e->getBody();
    }

    if (strpos($out, "OK mute") === false)
        return array("result" => false);
    else
        return array("result" => true);
}

function unmuteConferenceRoom($confroom)
{
    global $db;
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $isXML = 0;
    $obj = new stdClass();
    $out = "";
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom unmute non_moderator");
        $out .= $e->getBody();
    }
    if (strpos($out, "OK unmute") === false)
        return array("result" => false);
    else
        return array("result" => true);
}

function deafConferenceRoom($confroom)
{
    global $db;
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $isXML = 0;
    $obj = new stdClass();
    $out = "";
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom deaf non_moderator");
        $out .= $e->getBody();
    }
    if (strpos($out, "OK deaf") === false) {
        $ret = false;
    } else {
        $ret = true;
    }
    return array("result" => $ret);
}

function undeafConferenceRoom($confroom)
{
    global $db;
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $isXML = 0;
    $obj = new stdClass();
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom undeaf non_moderator");
        $out .= $e->getBody();
    }
    if (strpos($out, "OK undeaf") === false) {
        $ret = false;
    } else {
        $ret = true;
    }
    return array("result" => $ret);
}

function deafConferenceUser($confroom, $uuid)
{
    $user = getUserIDbyUUID($confroom, $uuid);

    $esl = new eslConnection($user['ip'], '8021', 'ClueCon');
    $e = $esl->api("conference $confroom deaf {$user['id']}");

    if (strpos($e->getBody(), "OK deaf") === false) {
        $ret = false;
    } else {
        $ret = true;
    }
    return array("result" => $ret);
}

function undeafConferenceUser($confroom, $uuid)
{
    $user = getUserIDbyUUID($confroom, $uuid);

    $esl = new eslConnection($user['ip'], '8021', 'ClueCon');
    $e = $esl->api("conference $confroom undeaf {$user['id']}");

    if (strpos($e->getBody(), "OK undeaf") === false) {
        $ret = false;
    } else {
        $ret = true;
    }
    return array("result" => $ret);
}

function toggleMuteConferenceUser($confroom, $uuid)
{

    $user = getUserIDbyUUID($confroom, $uuid);

    $esl = new eslConnection($user['ip'], '8021', 'ClueCon');
    $e = $esl->api("conference $confroom tmute {$user['id']}");

    if (strpos($e->getBody(), "OK") === false) {
        $ret = false;
    } else {
        $ret = true;
    }
    return array("result" => $ret, "what" => $e->getBody());
}

function dial($confroom, $dnis, $ani)
{
    $serverip = getLowConferenceCount($confroom);
    if ($serverip == false) {
        return array("result" => false, "dialresult" =>
                "Couldnt find active conference.");
    }
    $esl = new eslConnection($serverip, '8021', 'ClueCon');
    $e = $esl->api("originate sofia/external/$dnis@65.48.99.10 '&lua(confadd.lua $confroom)'");
    //$e = $esl->api("conference $confroom dial sofia/external/$dnis@65.48.99.10 $ani Conference-Dial");

    return array("result" => true, "dialresult" => $e->getBody());
}

function doRecording($method, $confroom)
{
    global $db;

    $ip = getLowConferenceCount($confroom);
    $esl = new eslConnection($ip, '8021', 'ClueCon');

    switch (strtoupper($method)) {
        case 'START':
            $e = $esl->api("conference $confroom xml_list");
            if (strstr($e->getBody(), "recording_node") === false) {
                $uuid = getConfUUID($confroom);
                $timestamp = date("mdY");
                $recfile = "/mnt/recordings/$uuid";
                $recfile .= "_$timestamp.mp3";

                $e = $esl->api("conference $confroom recording start $recfile");

                $query = "insert into recordings (confroom, epoch, uuid, filelocation) values ($confroom, '" .
                    time() . "', '$uuid', '$recfile')";
                mysqli_query($db, $query);

                return array("result" => true, "why" => "Started " . $e->getBody());
            } else {
                return array("result" => false, "why" => "Recording already started...");
            }
            break;

        case 'PAUSE':
            $uuid = getConfUUID($confroom);
            $query = "select * from recordings where uuid='$uuid'";
            $res = mysqli_query($db, $query);
            $row = mysqli_fetch_assoc($res);
            $e = $esl->api("conference $confroom recording pause {$row['filelocation']}");

            return array("result" => true, "why" => "Paused " . $e->getBody());

            break;
        default:
            $uuid = getConfUUID($confroom);
            $query = "select * from recordings where uuid='$uuid'";
            $res = mysqli_query($db, $query);
            $row = mysqli_fetch_assoc($res);
            $e = $esl->api("conference $confroom recording $method {$row['filelocation']}");

            return array("result" => true, "why" => $e->getBody());

            break;
    }

    return array("result" => false, "why" => "Method not found...");
}

function getConfUUID($room)
{
    $ip = getLowConferenceCount($confroom);
    $esl = new eslConnection($ip, '8021', 'ClueCon');
    $e = $esl->api("conference $confroom xml_list");
    $xml = simplexml_load_string($e->getBody());
    $xmlattr = $xml->conference[0]->attributes();
    return $xmlattr->uuid;
}

function getRecording($uuid) // MK
{
    global $db;
    $sql = "select filelocation from recordings where uuid='$uuid'";
    error_log("ERROR GetRecording: $sql");
    $res = mysqli_query($db, $sql);
    if (!$res) {
        error_log("ERROR GetRecording: $sql");
        return array("result" => false, "why" => mysqli_error($db));
    }
    $row = mysqli_fetch_assoc($res);
    $file = $row['filelocation'];
    $filename = str_replace("/mnt/recordings/", "", $file);
    if (file_exists($file)) {
        $contents = file_get_contents($file);
        $base64 = base64_encode($contents);
        return array("result" => true, "filedata" => array("filename" => $filename,
                    "data" => $base64));
    } else {
        return array("result" => false);
    }

}


function getRecordings($room)
{
    global $db;
    $sql = "select confroom, uuid, FROM_UNIXTIME(epoch) as record_time, filelocation from recordings where confroom=$room";
    $res = mysqli_query($db, $sql);
    if (!$res)
        return array(
            "result" => false,
            "why" => mysqli_error($db),
            "sql" => $sql);

    while ($rows[] = mysqli_fetch_assoc($res))
        ;
    array_pop($rows);

    return array("result" => true, "data" => $rows);
}

function provisionConference($method, array $data)
{
    global $db;
    switch (strtoupper($method)) {
        case "CREATE":
            $room = rand(1000, 9999);
            $user = rand(1000, 9999);
            $modp = rand(1000, 9999);
            $super = rand(10000000, 99999999);
            $supermod = rand(10000000, 99999999);
            if ($data != null) {
                $json = $data; //    json_decode($data);
                $sql = "select * from conference where confowner='{$json['compid']}'";
                $res = mysqli_query($db, $sql);
                error_log(print_r($res,true));
//		if (mysqli_num_rows($db, $res) == 0)
                if (mysqli_num_rows($res) == 0) {
                    $doSuperMod = true;
                }
                if (array_key_exists("maxusers", $json) || array_key_exists("confoptions", $json) ||
                    array_key_exists("compid", $json) || array_key_exists("userid", $json)) {
                    if (array_key_exists("parent", $json))
                        $jsonParent = $json['parent'];
                    else
                        $jsonParent = "-1";

                    if (array_key_exists("expires", $json))
                        $jsonExpires = $json['expires'];
                    else
                        $jsonExpires = "-1";

                    $query = "insert into conference (confroom, confpass, confowner, confcreated, confmodified, confexpired, confadminpin, maxuser, confoptions, spinuser, spinmod, parent) values ";
                    $query .= "($room, $user, '{$json['compid']}', UNIX_TIMESTAMP(now()), UNIX_TIMESTAMP(now()), '$jsonExpires',  $modp, {$json['maxusers']}, '{$json['confoptions']}', $super, $supermod, $jsonParent)";
                    $userSql = "insert into user2conf (userid, conf) values ({$json['userid']}, '$room')";
                    $res = mysqli_query($db, $query);
                    if ($res)
                        mysqli_query($db, $userSql);
                    error_log("Provision Create: " . mysqli_error($db));
                    $accowner = false;
                    if (mysqli_affected_rows($db) == 1) {
                        if ($doSuperMod) {
                            $sql = "insert into supermod (moderatorpin, confroom) values ($modp, $room)";
                            mysqli_query($db, $sql);
                            $accowner = true;
                        }
                        if (array_key_exists("bridgeid", $json)) {
                            $dnis = $json['bridgeid'];
                            $query = "select confroom from dnis2conf where dnis=\"$dnis\" limit 1";
                            $res = $db->query($query);
                            if (mysqli_num_rows($res) == 1) {
                            } else {
                                $query = "insert into dnis2conf (dnis, confroom) values (\"$dnis\", \"$room\")";
                                if (!mysqli_query($db, $query)) {
                                    error_log("Insert Bridge ID: " . mysqli_error());
                                    return array(
                                        "result" => "false",
                                        "confroom" => $room,
                                        "userpin" => $user,
                                        "modpin" => $modp,
                                        "superpin" => $super,
                                        "supermod" => $supermod,
                                        "Account_Owner" => $accowner,
                                        "DNIS" => false);
                                } else {
                                    return array(
                                        "result" => "true",
                                        "confroom" => $room,
                                        "userpin" => $user,
                                        "modpin" => $modp,
                                        "superpin" => $super,
                                        "supermod" => $supermod,
                                        "Account_Owner" => $accowner,
                                        "DNIS" => true);
                                }
                            }
                        } else {
                            return array(
                                "result" => "true",
                                "confroom" => $room,
                                "userpin" => $user,
                                "modpin" => $modp,
                                "superpin" => $super,
                                "supermod" => $supermod,
                                "Account_Owner" => $accowner);
                        }
                    } else {
                        error_log("Conference Query: $query");
                        error_log("SuperMod Query: $sql");
                        return array("result" => false, "why" =>
                                "Failure on insert.  Please try again. " . mysqli_error($db));
                    }

                } else {
                    return array("result" => false, "why" => "Conference \$data elements missing.");
                }

            } else {
                return array("result" => false, "why" => "Conference \$data JSON string not present.");
            }
            break;
        case "UPDATE":
            $json = $data;

            if (array_key_exists("confroom", $json))
                $room = $json['confroom'];
            else
                return array("result" => false, "why" =>
                        "Conference JSON element confroom is missing...");

            if (array_key_exists("bridgeid", $json) && !empty($json['bridgeid']) && ($json['bridgeid'] !=
                "null")) {

                $query = "select * from dnis2conf where confroom=$room";
                $res = $db->query($query);
                if (mysqli_num_rows($res) == 1)
                    $query = "update dnis2conf set dnis=\"{$json['bridgeid']}\" where confroom=$room";
                else
                    $query = "insert into dnis2conf (dnis, confroom) values (\"{$json['bridgeid']}\", $room)";

                error_log("Update BridgeID Query: $query");
                if (!mysqli_query($db, $query))
                    return array("result" => false, "why" => mysqli_error());
            }

            $query = "update conference set ";

            if (array_key_exists("maxusers", $json))
                $query .= "maxuser=\"{$json['maxusers']}\" , ";
            if (array_key_exists("confoptions", $json))
                $query .= "confoptions=\"{$json['confoptions']}\" , ";
            if (array_key_exists("confpass", $json))
                $query .= "confpass=\"{$json['confpass']}\" , ";
            if (array_key_exists("confadminpin", $json))
                $query .= "confadminpin=\"{$json['confadminpin']}\" , ";
            if (array_key_exists("confexpired", $json))
                $query .= "confexpired=\"{$json['confexpired']}\" , ";

            $query = rtrim($query, ", ");

            $query .= " where confroom=$room";

            error_log("Update Conference Query: $query");
            if (mysqli_query($db, $query))
                return array("result" => true);
            else
                return array("result" => false, "why" => mysqli_error($db));


            break;
        default:
            return array("result" => "Method Not Found.");
    }

}

function addUser($json)
{

    global $db;
    if (array_key_exists("user", $json) || array_key_exists("pass", $json)) {
        $query = "insert into users (user, pass) values ('{$json['user']}', '{$json['pass']}')";
        if (mysqli_query($db, $query)) {
            return array("result" => "Success", "userid" => mysqli_insert_id($db));
        } else {
            return array("result" => "Failed to Insert User...", "why" => mysqli_error());
        }
    } else {
        return array("result" => "Failed to add user.", "why" =>
                "JSON data elements missing...");
    }

}

function resetUserPassword($json)
{
    global $db;

    if (array_key_exists("userid", $json) || array_key_exists("newpass", $json)) {
        $sql = "update users set pass='{$json['newpass']}' where id={$json['userid']}";
        //error_log("resetPassword: $sql");
        if (mysqli_query($db, $sql)) {
            return array("result" => true);
        } else {
            error_log("Reset Password SQL: $sql | SQL Error: " . mysqli_error($db));
            return array("result" => false, "why" => mysqli_error());
        }

    }
}

function checkUser($user)
{
    global $db;
    $sql = "select id from users where user='$user'";

    $res = mysqli_query($db, $sql);
    if (mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        return array("result" => true, "userid" => "{$row['id']}");
    } else {
        return array("result" => false, "why" =>
                "I dont know.  I didnt code any debug here...");
    }

}

function assignUserToConf($json)
{

    global $db;
    if (array_key_exists("room", $json) || array_key_exists("user", $json)) {
        $query = "select * from user2conf where userid='{$json['user']}' and conf='{$json['room']}'";
        $res = mysqli_query($db, $query);
        if (mysqli_num_rows($res) > 0) {
            return array("result" => false, "why" => "User Already Assigned.");
        } else {
            $query = "insert into user2conf (userid, conf) values ({$json['user']}, '{$json['room']}')";
            if (mysqli_query($db, $query)) {
                if (mysqli_affected_rows($db) == 1) {
                    return array("result" => true);
                } else {
                    return array("result" => false, "why" =>
                            "Got me? Percona told me I affected more than 1 row...");
                }
            } else {
                return array("result" => false, "why" => mysqli_error());
            }
        }

    } else {
        return array("result" => false, "why" => "JSON elements missing...");
    }
    return array("result" => false, "why" => "General Failure.");
}


function delUser($email)
{

    global $db;
    $usrArray = checkUser($email);
    $uid = $usrArray['userid'];
    $sql = "delete from users where user='$email'";
    if (mysqli_query($db, $sql)) {
        $sql = "delete from user2conf where userid='$uid'";
        if (mysqli_query($db, $sql)) {
            return array("result" => true);
        } else {
            return array("result" => false, "why" => "Broke deleting user links..");
        }
    } else {
        return array("result" => false, "why" => "broke deleting user...");
    }
}

function delRecording($uuid)
{

    global $db;
    $sql = "select filelocation from recordings where uuid='$uuid'";
    $res = mysqli_query($db, $sql);
    $row = mysqli_fetch_assoc($res);

    $sql = "delete from recordings where uuid='$uuid'";
    if (mysqli_query($db, $sql)) {
        unlink($row['filelocation']);
        return array("result" => true);
    } else {
        return array("result" => false, "why" => "Failure...");
    }
}

function delConference($confid)
{

    global $db;
    $sql = "delete from conference where confroom=$confid";
    if (mysqli_query($db, $sql)) {
        $sql = "delete from user2conf where conf='$confid'";
        if (mysqli_query($db, $sql)) {
            return array("result" => true);
        } else {
            return array("result" => false, "why" => "Failure..." . mysqli_error());
        }
    } else {
        return array("result" => false, "why" => "Failure..." . mysqli_error());
    }

}


// The functions below support the functions above.

function decryptPassword($pass)
{
    return decryptRJ256("dxaWtlmV4SzNhSvcXQhlLnCKRVVyQ8U4pI6JHTO/JGI=",
        "+CHegjW0c8MfMKuAi4yPc31fqJjd2gqnqpVL8tzOr/8=", $pass);
}

function decryptRJ256($key, $iv, $encrypted)
{
    //PHP strips "+" and replaces with " ", but we need "+" so add it back in...
    $encrypted = str_replace(' ', '+', $encrypted);

    //get all the bits
    $key = base64_decode($key);
    $iv = base64_decode($iv);
    $encrypted = base64_decode($encrypted);

    $rtn = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
    $rtn = unpad($rtn);
    return ($rtn);
}

//removes PKCS7 padding
function unpad($value)
{
    $blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $packing = ord($value[strlen($value) - 1]);
    if ($packing && $packing < $blockSize) {
        for ($P = strlen($value) - 1; $P >= strlen($value) - $packing; $P--) {
            if (ord($value{$P}) != $packing) {
                $packing = 0;
            }
        }
    }

    return substr($value, 0, strlen($value) - $packing);
}


function getUserIDbyUUID($confroom, $uuid)
{
    global $db;
    libxml_use_internal_errors(true);
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        if (trim($row['ip']) != "") {
            $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
            $e = $esl->api("conference $confroom xml_list");
            if ($xml = simplexml_load_string($e->getBody())) {
                $xmlattr = $xml->conference[0]->attributes();
                for ($y = 0; $y < $xmlattr['member-count']; $y++) {
                    if (strtoupper(trim(changeArray($xml->conference->members->member[$y]->uuid))) ==
                        strtoupper($uuid))
                        return array("id" => changeArray($xml->conference->members->member[$y]->id),
                                "ip" => $row['ip']);
                }
            }
        }
    }
    return false;
}

function getLowConferenceCount($confroom)
{
    global $db;
    libxml_use_internal_errors(true);
    $maxCalls = 1000;
    $lastIP = "";
    $sql = "select * from servers";
    $res = mysqli_query($db, $sql);
    $error = 1;
    for ($x = 0; $row = mysqli_fetch_assoc($res); $x++) {
        $esl = new eslConnection($row['ip'], '8021', 'ClueCon');
        $e = $esl->api("conference $confroom xml_list");
        if ($xml = simplexml_load_string($e->getBody())) {
            $xmlattr = $xml->conference[0]->attributes();

            if ($xmlattr['member-count'] < $maxCalls) {
                $maxCalls = $xmlattr['member-count'];
                $lastIP = $row['ip'];
                $error = 0;
            }
        } else {
            error_log("Error getting XML data from FS Server...");
        }
    }
    if ($error == 1) {
        return false;
    } else {
        return $lastIP;
    }
}

?>
