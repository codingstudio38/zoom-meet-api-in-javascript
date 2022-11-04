<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
define('API_KEY',$_POST['API_KEY']);
define('API_SECRET',$_POST['API_SECRET']);
define('EMAIL_ID',$_POST['EMAIL_ID']);

require_once 'php-jwt-master/src/BeforeValidException.php';
require_once 'php-jwt-master/src/ExpiredException.php';
require_once 'php-jwt-master/src/SignatureInvalidException.php';
require_once 'php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;

function createMeeting($data = array())
{
    $start_time = $data['start_date'];
    $createMeetingArr = array();
    if (!empty($data['alternative_host_ids']))
    {
        if (count($data['alternative_host_ids']) > 1)
        {
            $alternative_host_ids = implode(",", $data['alternative_host_ids']);
        }
        else
        {
            $alternative_host_ids = $data['alternative_host_ids'][0];
        }
    }

    $createMeetingArr['topic'] = $data['topic'];
    $createMeetingArr['agenda'] = !empty($data['agenda']) ? $data['agenda'] : "";
    $createMeetingArr['type'] = !empty($data['type']) ? $data['type'] : 2;
    $createMeetingArr['start_time'] = $start_time;
    $createMeetingArr['timezone'] = $data['timezone'];
    $createMeetingArr['password'] = !empty($data['password']) ? $data['password'] : "";
    $createMeetingArr['duration'] = !empty($data['duration']) ? $data['duration'] : 60;
    $createMeetingArr['settings'] = array(
        'join_before_host' => !empty($data['join_before_host']) ? true : false,
        'host_video' => !empty($data['option_host_video']) ? true : false,
        'participant_video' => !empty($data['option_participants_video']) ? true : false,
        'mute_upon_entry' => !empty($data['option_mute_participants']) ? true : false,
        'enforce_login' => !empty($data['option_enforce_login']) ? true : false,
        'auto_recording' => !empty($data['option_auto_recording']) ? $data['option_auto_recording'] : "none",
        'alternative_hosts' => isset($alternative_host_ids) ? $alternative_host_ids : ""
    );

    $request_url = "https://api.zoom.us/v2/users/" . EMAIL_ID . "/meetings";
    $token = array(
        "iss" => API_KEY,
        "exp" => time() + 3600 //60 seconds as suggested
        
    );
    $getJWTKey = JWT::encode($token, API_SECRET);
    $headers = array(
        "authorization: Bearer " . $getJWTKey,
        "content-type: application/json",
        "Accept: application/json",
    );

    $fieldsArr = json_encode($createMeetingArr);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $fieldsArr,
        CURLOPT_HTTPHEADER => $headers,
    ));

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if (!$result)
    {
        return $err;
    }
    return json_decode($result);
}



function meeting_list() {
    $request_url = "https://api.zoom.us/v2/users/me/meetings";
    $token = array(
        "iss" => API_KEY,
        "exp" => time() + 3600 //60 seconds as suggested
        
    );
    $getJWTKey = JWT::encode($token, API_SECRET);
    $headers = array(
        "authorization: Bearer " . $getJWTKey,
        "content-type: application/json",
        "Accept: application/json",
    );


    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => $headers,
    ));

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if (!$result)
    {
        return $err;
    }
    return json_decode($result);
}

function delete_meeting($id = null) {
    if($id==null){
        return 0;
    }
    $request_url = "https://api.zoom.us/v2/meetings/$id";
    $token = array(
        "iss" => API_KEY,
        "exp" => time() + 3600 //60 seconds as suggested
        
    );
    $getJWTKey = JWT::encode($token, API_SECRET);
    $headers = array(
        "authorization: Bearer " . $getJWTKey,
        "content-type: application/json",
        "Accept: application/json",
    );


    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => $headers,
    ));

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if (!$result)
    {
        return $err;
    }
    return json_decode($result);
}

define('TIMEZONE',$_POST['TIMEZONE']);
date_default_timezone_set(TIMEZONE);

if(isset($_POST['view_data'])){
    $data=meeting_list();
    echo json_encode($data);
}

if(isset($_POST['add_event'])){
$start_date = $_POST['start_date'];
$start_time = $_POST['start_time'];
$arr['topic']=$_POST['topic'];
$arr['start_date']=$start_date."T".$start_time.":00";
$arr['duration']=$_POST['duration'];
$arr['password']=$_POST['password'];
$arr['type']='2';
$arr['agenda']=$_POST['agenda'];
$arr['timezone']=TIMEZONE;
$result=createMeeting($arr);
echo json_encode($result);
}

if(isset($_POST['delete_event'])){
	$delete = delete_meeting($_POST['delete_id']);
    echo json_encode($delete);
}

if(isset($_POST['modify_date'])){
    if($_POST['start_time']==""){
        $start_time ="";
    } else{
        $start_time = date("Y/m/d h:i:s A", strtotime($_POST['start_time']));
    }
    if($_POST['created_at']==""){
        $created_at ="";
    } else{
        $created_at = date("Y/m/d h:i:s A", strtotime($_POST['created_at']));
    }
    echo json_encode(array('created' => $created_at, 'start' => $start_time));
}

?>
