<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');

$user = csg_get_user();
$user_id = $user['id'];

print_header("Wildlife@Home: Image Viewer",  "<link href='./wildlife_css/review_image.css' rel='stylesheet'>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

$image_id = -1;
$project_id = 1;
$species_id = 0;
if (isset($_GET['p'])) {
    $project_id = $boinc_db->real_escape_string($_GET['p']);
}
if (isset($_GET['s'])) {
    $species_id = $boinc_db->real_escape_string($_GET['s']);
}

$result = NULL;
if (array_key_exists('image_id', $_GET)) {
    $image_id = $boinc_db->real_escape_string($_GET['image_id']);
    $result = query_wildlife_video_db("SELECT id, watermarked_filename, watermarked, species, year FROM images WHERE id = $image_id");
} else {
    /*$temp_result = query_wildlife_video_db("select max(id), min(id) from images");
    $row = $temp_result->fetch_assoc();
    $max_int = $row['max(id)'];
    $min_int = $row['min(id)'];

    do {
        $temp_id = mt_rand($min_int, $max_int);
        $temp_result = query_wildlife_video_db("select id, watermarked_filename, watermarked, species, year from images where watermarked=1 and views < needed_views and project_id=$project_id and id not in (select image_id from image_observations where user_id=$user_id) and id=$temp_id");
    } while ($temp_result->num_rows < 1);

    $result = $temp_result;*/

    $species = '';
    if ($species_id > 0)
        $species = "and species=$species_id";

    $query = "select id, watermarked_filename, species, year from images where watermarked=1 and views < needed_views and project_id=$project_id $species and id != any (select image_id from image_observations where user_id=$user_id) order by rand() limit 1";
    $result = query_wildlife_video_db($query);
}

if ($result->num_rows < 1) {
    echo "
    <div class='container-fluid'>
    <div class='row'>
        <div class='col-sm-12'>
            <div class='alert alert-error' role='alert' id='ajaxalert'>
                <strong>Error!</strong> Unable to find an available image for project_id=$project_id $species
            </div>
        </div>
    </div>
    ";
} else {

$row = $result->fetch_assoc();

$image_id = $row['id'];
$image_watermarked = $row['watermarked'];
$image = $row['watermarked_filename'];
$year = $row['year'];

$alert_class = 'hidden';

echo "
<div class='container-fluid'>
<div class='row'>
    <div class='col-sm-12'>
        <div class='alert $alert_class' role='alert' id='ajaxalert'>
            <strong>Success!</strong> Data submited to the database.
        </div>
    </div>
</div>
<div class='row'>
    <div class='col-sm-4'>
        <div class='container-fluid'>
            <div class='row'>
            <div id='selection-information'>
                    <div class='btn-group btn-group-sm' role='group'>
                        <button type='button' class='btn disabled' disabled><strong>Image #: $image_id</strong></button>
                        <button type='button' id='discuss-button' class='btn btn-primary' data-toggle='tooltip' title='Discuss this image on the forum'>&nbsp;<span class='glyphicon glyphicon-comment'> </span></button>
                    </div>
                    <!-- You are looking at image: $image_id and it is watermarked? $image_watermarked. <br>Year: $year. <br> $image Image ID: $image_id -->
                    <div class='btn-group btn-group-sm pull-right' role='group'>
                        <button type='button' class='btn btn-info' data-toggle='modal' data-target='#helpModal'>Species <span class='glyphicon glyphicon-question-sign'> </span></button>
                        <button type='button' class='btn btn-info' data-toggle='modal' data-target='#interfaceModal'>Interface <span class='glyphicon glyphicon-question-sign'> </span></button>
                    </div>
                    <br><br>
                 </div>
                 </div>
            <div class='row'>
                <textarea class='form-control' rows='3' placeholder='Comments' name='comment-area' id='comment-area'></textarea>
                <br>
                <div class='text-center'>
                    <div class='btn-group btn-group-lg'>
                        <button class='btn btn-primary' id='skip-button' data-toggle='tooltip' title='Skip this image'>Skip</button>
                        <button class='btn nothing btn-danger' id='nothing-here-button' data-toggle='tooltip' title='No animals in this image'>There's Nothing Here</button>
                        <button class='btn btn-primary disabled' id='submit-selections-button' data-toggle='tooltip' title='Submit species to the database' disabled>Submit</button>
                     </div>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-8' id='canvasContainer'>
        <canvas id='canvas' width='600' height='400'>
        </canvas>
    </div>
    </div>
</div>";

}
	

print_footer();

echo "<div id='submitModal' class='modal fade' data-backdrop='static'>
		<div class='modal-dialog modal-sm' role='dialog'>
			<div class='modal-content'>
				<div class='modal-header'>
					<h4 class='modal-title''>Submission Complete</h4>
				</div>
					<div class='modal-body'>
						<p>Thank you!</p>
					</div>
					<div class='modal-footer'>
						<button id='modalSubButton' type='button' class='btn btn-primary' data-dismiss='modal'>Close</button>
					</div>
			</div>
		</div>
	</div>
	<div id='interfaceModal' class='modal fade' style='height: 80%'>
		<div class='modal-dialog modal-lg' role='dialog'>
			<div class='modal-content'>
                <div class='modal-header'>
				    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>X</button>
					<h4 class='modal-title''>Interface Help</h4>
                </div>
                <div class='modal-body' style='overflow-y: scroll'>
                    <table class='table table-striped'>
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Action</th>
                        <th>Result</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><span class='glyphicon glyphicon-hand-up'></span> <strong>(x2)</strong></td>
                        <td>Double-tap / Double-click</td>
                        <td>Creates a new box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-hand-up'></span> <strong>(x3)</strong></td>
                        <td>Triple-tap / Triple-click</td>
                        <td>Deletes a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-full'></span></td>
                        <td>Zoom / Scroll Up</td>
                        <td>Zooms the image in</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-small'></span></td>
                        <td>Zoom / Scroll Down</td>
                        <td>Zooms the image out</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-move'></span></td>
                        <td>Tap / Click and Drag</td>
                        <td>Moves a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-vertical'></span></td>
                        <td>Tap / Click on Top or Bottom and Drag</td>
                        <td>Adjusts the height of a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-horizontal'></span></td>
                        <td>Tap / Click on Side and Drag</td>
                        <td>Adjust the width of a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-fullscreen'></span></td>
                        <td>Tap / Click on Corner and Drag </td>
                        <td>Adjust the height and width of a box</td>
                    </tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<div id='helpModal' class='modal fade' style='height: 80%'>
		<div class='modal-dialog modal-lg' role='dialog'>
			<div class='modal-content'>
				<div class='modal-header'>
					 <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>X</button>
					<h4 class='modal-title''>Species Help</h4>
				</div>
                <div class='modal-body' style='overflow-y: scroll'>
                <table class='table'>
                    <thead>
                        <tr>
                            <th style='width:40%'>Image</th>
                            <th style='width:20%'>Species</th>
                            <th style='width:40%'>Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><img src='images/marshall_arctic_fox.png' class='img-responsive'></td>
                            <td>Arctic Fox</td>
                            <td>Small fox</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_canada_geese.png' class='img-responsive'></td>
                            <td>Canada Goose</td>
                            <td>Goose</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_caribou.png' class='img-responsive'></td>
                            <td>Caribou</td>
                            <td>Caribou</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_common_eider.png' class='img-responsive'></td>
                            <td>Common Eider</td>
                            <td>Male in the top right. Female in the bottom left.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_common_eider_nest.png' class='img-responsive'></td>
                            <td>Common Eider on Nest</td>
                            <td>Female common eider on a nest.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_crow.png' class='img-responsive'></td>
                            <td>Crow</td>
                            <td>Common scavenger in the area.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_grizzly_bear.png' class='img-responsive'></td>
                            <td>Grizzly Bear</td>
                            <td>Large brown bear.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_gull.png' class='img-responsive'></td>
                            <td>Gull</td>
                            <td>Gull.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_polar_bear.png' class='img-responsive'></td>
                            <td>Polar Bear</td>
                            <td>Large white bear.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_sandhill_crane.png' class='img-responsive'></td>
                            <td>Sandhill Crane</td>
                            <td>Crane.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_snow_goose.png' class='img-responsive'></td>
                            <td>Snow Goose</td>
                            <td>How is this different from the Canada Goose?</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_snow_goose_blue.png' class='img-responsive'></td>
                            <td>Snow Goose, Blue Phase</td>
                            <td>Along with the white Snow Goose, there is a blue phase to the Snow Goose. This should still be categorized as a Snow Goose.</td>
                        </tr>
                        <tr>
                            <td><img src='images/marshall_wolverine.png' class='img-responsive'></td>
                            <td>Wolverine</td>
                            <td>Wolverine.</td>
                        </tr>
                    </tbody>
                </table> 
                </div>
			</div>
		</div>
        </div>";

echo "
<form class='hidden' action='' method='POST' id='submitForm'>
<input type='hidden' id='submitStart' name='submitStart' value='".time()."'/>
<input type='hidden' id='submitEnd' name='submitEnd' value='0'/>
<input type='hidden' id='image_id' name='image_id' value='$image_id'/>
</form>
    
<form id='forumPost' class='hidden' action='//csgrid.org/csg/forum_post.php?id=8' method='post' target='_blank'>
    <input type='hidden' id='forumContent' name='content' value=''>
</form>";

echo "<script src='./js/jquery.mousewheel.min.js'></script>
<script src='./js/hammer.min.js'></script>
<script src='./js/canvas_selector.js'></script>
<script>
    var imgsrc = 'http://wildlife.und.edu/$image';
    var species_id = $species_id;
</script>
<script src='./js/review_image.js'></script>";

?>
