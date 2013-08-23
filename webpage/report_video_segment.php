<?php

require_once('/home/tdesell/wildlife_at_home/webpage/award_credit.inc');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/projects/wildlife/html/inc/util.inc');
require_once('/projects/wildlife/html/inc/bossa_impl.inc');

$user = get_logged_in_user();
$reporter_id = $user->id;
$reporter_name = $user->name;

$report_comments = mysql_real_escape_string($_POST['report_comments']);
$video_segment_id = mysql_real_escape_string($_POST['video_segment_id']);

/**
 * Grab the other observations from the database.
 */

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

//echo "WILDLIFE_USER: $wildlife_user\n";
//echo "WILDLIFE_PASSWD: $wildlife_passwd\n";

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "REPLACE INTO reported_video SET video_segment_id = $video_segment_id, reporter_id = $reporter_id, reporter_name='$reporter_name', report_comments='$report_comments'";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$query = "UPDATE video_segment_2 SET report_status = IF(report_status = 'UNREPORTED', 'REPORTED', report_status) WHERE id = $video_segment_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
error_log("query: $query");

?>
