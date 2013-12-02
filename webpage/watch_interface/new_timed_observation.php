<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/user.php');
require_once('/home/tdesell/wildlife_at_home/webpage/watch_interface/observation_table.php');

$user = get_user();
$user_id = $user['id'];
$is_special_user = is_special_user__fixme($user, true);

error_log("is special user: '" . $is_special_user . "', user id: " . $user_id);

$video_id = mysql_real_escape_string($_POST['video_id']);
$event_id  = mysql_real_escape_string($_POST['event_id']);
$start_time = mysql_real_escape_string($_POST['start_time']);
$end_time = mysql_real_escape_string($_POST['end_time']);
$comments = mysql_real_escape_string($_POST['comments']);
$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);


ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

$user_query = "UPDATE user SET total_events = total_events + 1 WHERE id = $user_id";
$user_result = attempt_query_with_ping($user_query, $boinc_db);
if (!$user_result) {
    error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
    die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
}



$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "INSERT INTO timed_observations SET user_id = $user_id, start_time = '$start_time', end_time = '$end_time', event_id ='$event_id', comments = '$comments', video_id = '$video_id', species_id = $species_id, location_id = $location_id, expert = $is_special_user";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

$observation_id = mysql_insert_id($wildlife_db);

$response['observation_id'] = $observation_id;
$response['html'] = get_timed_observation_row($observation_id, $species_id, 0);

echo json_encode($response);
?>
