<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/generate_count_nav.php');

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

$user = get_logged_in_user();
$user_id = $user->id;

$filters = array();
if (array_key_exists('filters', $_POST)) {
    $filters = $_POST['filters'];
}

$filter = '';

$reported_filter = '';

foreach ($filters as $key => $value) {
//    error_log("    '$key' => '$value'");

    if ($key == 'report_status') {
        $reported_filter .= " vs2.report_status = '" . mysql_real_escape_string($value) . "' AND ";
    } else {
        if ($value == 'VALID or CANONICAL') {
            $filter .= " AND (observations." . mysql_real_escape_string($key) . " = 'VALID' OR observations." . mysql_real_escape_string($key) . " = 'CANONICAL') ";
        } else if (!is_numeric($value)) {
            $filter .= " AND observations." . mysql_real_escape_string($key) . " = '" . mysql_real_escape_string($value) . "' ";
        } else {
            $filter .= " AND observations." . mysql_real_escape_string($key) . " = " . mysql_real_escape_string($value) . " ";
        }
    }
}

$display_nav_numbers = true;
if (empty($filters)) {
    $display_nav_numbers = false;
    $max_items = 0;
} else {
    //error_log("the filter is: " . $filter);

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    $query = "";
    if (array_key_exists('all_users', $_POST)) {
        $reported_filter = substr($reported_filter, 0, -4);
        $query = "SELECT count(id) FROM video_segment_2 vs2 WHERE $reported_filter";
    } else {
        $query = "SELECT count(id) FROM video_segment_2 vs2 WHERE $reported_filter EXISTS (SELECT id FROM observations WHERE user_id = $user_id $filter AND observations.video_segment_id = vs2.id)";
    }

    //echo "<!-- $query -->\n";


    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

    $row = mysql_fetch_assoc($result);

    $max_items = $row['count(id)'];
}

generate_count_nav($max_items, $video_min, $video_count, $display_nav_numbers);

?>
