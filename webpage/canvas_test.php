<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");


print_header("Wildlife@Home: Image Viewer",  "<link href='./wildlife_css/canvas_test.css' rel='stylesheet'> <script type='text/javascript' src='./js/canvas_test.js'></script>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

function container_start() {
    echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>
                <div class='well'>";

}

function container_end() {
    echo "
                </div> <!-- well -->
            </div> <!-- col-sm-12 -->
        </div> <!-- row -->
    </div> <!-- /container -->";
}

/*
container_start();

echo "
<div class='row'>
    <div class='col-sm-8'>
        <div id='canvas'>
            <img class='img-responsive' src='./images/nd_pred_coyote.jpg'></img>
        </div>
    </div>

    <div class='col-sm-4'>
        <div id='selection-information'>
        </div>
        <button class='btn btn-primary' id='submit-selections-button'>Submit</button>

    </div>
</div>";

container_end();
*/


$image_id = -1;
$project_id = $_GET[(p)];
if(!$project_id) $project_id=1;

$result = NULL;
//TODO Update query so user doesn't see verified image - BCC
if (array_key_exists('image_id', $_GET)) {
    $image_id = mysql_real_escape_string($_GET['image_id']);
    $result = query_wildlife_video_db("SELECT id, watermarked_filename, watermarked, species, year FROM images WHERE id = $image_id");

} else {
    $result = query_wildlife_video_db("SELECT id, watermarked_filename, watermarked, species, year FROM images WHERE watermarked=1 and views<needed_views and project_id=$project_id ORDER BY RAND() LIMIT 1");
    //$row = $result->fetch_assoc();

    //from citizen_science_grid/my_query.php
    //this is supposedly much faster, as per: http://akinas.com/pages/en/blog/mysql_random_row/
    //$offset_result = query_wildlife_video_db( " SELECT FLOOR(RAND() * COUNT(*)) AS `offset` FROM images");
    //$offset_row = $offset_result->fetch_assoc();
    //$image_id = $offset_row['offset'];

    //$result = query_wildlife_video_db("SELECT id, watermarked_filename, watermarked FROM images LIMIT $image_id, 1 ");
}

$row = $result->fetch_assoc();

$image_id = $row['id'];
$image_watermarked = $row['watermarked'];
$image = $row['watermarked_filename'];
$species_id = $row['species'];
$year = $row['year'];
if($species_id == 3)
{ $species = "predator";}
else if($species_id == 2)
{ $species = "lesser snow goose";}
else if($species_id == 1)
{ $species = "common eider";}
else
{ $species = "other";}

//TODO make 'nothing here' button work -Jaeden

echo "
<div class='row'>
    <div class='col-sm-4'>
        <div id='selection-information'>
            <!-- You are looking at image: $image_id and it is watermarked? $image_watermarked. <br>Species: $species. Year: $year. <br> $image --> Image ID: $image_id
        </div>
	<textarea class='nothing-here-box' type='text' size='34' maxlength='512' value ='' id='comments' placeholder='comments' row='1'></textarea><br>
	<button class='btn nothing btn-danger' id='nothing-here-button' >There's Nothing Here</button>
        <button class='btn btn-primary' id='submit-selections-button'>Submit</button>
    </div>
    <div class='col-sm-8' onselectstart='return false' ondragstart='return false'>
        <div id='canvas'>
            <img class='img-responsive' id='$image_id'  src='http://wildlife.und.edu/$image'></img>

        </div>
    </div>
</div>";
	

print_footer();

echo "<div id='submitModal' class='modal fade' data-backdrop='static'>
		<div class='modal-dialog' role='dialog'>
			<div class='modal-content'>
				<div class='modal-header'>
					<h4 class='modal-title''>Submission Complete</h4>
					<div class='modal-body'>
						<p>Thx!</p>
					</div>
					<div class='modal-footer'>
						<button id='modalSubButton' type='button' class='btn btn-primary' data-dismiss='modal'>Close</button>
					</div>
				</div>
			</div>
		</div>
	<div>";



?>
