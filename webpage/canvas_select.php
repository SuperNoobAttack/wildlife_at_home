<?php
$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$result= array();
$project_id= 1;
if (isset($_POST['p'])) $project_id = $_POST['p'];

/*
if ($project_id == 2) {
    $res = query_uas_db("SELECT name, speciesId FROM tblSpecies");

    while ($row = $res->fetch_assoc()) {
        $phase = query_uas_db("SELECT name, phaseId FROM tblPhases");

        if ($prow = $phase->fetch_assoc()) {
            do {
                $results[] = array(
                    "name" => $row['name'] . ' - '.$prow['name'],
                    "id" => $row['speciesId'],
                    "phaseId" => $prow['phaseId']
                );
            } while ($prow = $phase->fetch_assoc());
        } else {
            $results[] = array(
                "name" => $row['name'],
                "id" => $row['speciesId'],
                "phaseId" => 0
            );
        }
    }
}*/
if ($project_id == 1) {
    $res = query_wildlife_video_db("select species, species_id from species_lookup where species_id = any (select species_id from species_project_lookup where project_id=$project_id)");

    while ($row = $res->fetch_assoc()) {
        $result[$row['species']] = $row['species_id'];
    }
}
echo json_encode($result);
?>
