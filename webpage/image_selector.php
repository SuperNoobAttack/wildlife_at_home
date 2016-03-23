<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

function get_count($table_name, $where_clause) {
    $results = query_wildlife_video_db("SELECT count(*) FROM $table_name WHERE $where_clause");
    $row = $results->fetch_assoc();

    return $row['count(*)'];
}

print_header("Wildlife@Home: Image Selection", $additional_scripts);
print_navbar("Review Images", "Wildlife@Home", "..");


echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>
                <div class='well'>
                <p>Select the project you'd like to review images for, and click the review images button to get started. You will have to <a href='../create_account_form.php'>create an account</a> first if you do not have one. Please take a look at the interface instructions and training images for each species first.
                </div>
            </div>
        </div>
    </div>
";

$thumbnails = array('thumbnail_list' => array(
                        array(
                            'thumbnail_image' => './images/thumbnail_sharptailed_grouse.png',
                            'species_name' => 'Sharp-Tailed Grouse',
                            'species_id' => '1',
                            'training_webpage' => './sharptailed_grouse_training.php',
                            'info_webpage' => 'sharptailed_grouse_info.php',
                            'species_latin_name' => 'Tympanuchus phasianellus',
                            'project_description' => '<p>Sharp-tailed grouse are an important ground-nesting bird and a species that can serve as an indicator of grassland health. Cameras were placed in areas with different degrees of gas and oil development.</p> <p>Active projects include: <ul><li>Rebecca Eckroad - <a href="becca_grouse_project.php">Nest Cameras and Citizen Science: Implications for evaluating Sharp-tailed Grouse Nesting Ecology</a></li><li>Paul Burr - <a href="paul_project.php">Sharp-tailed Grouse Nest Predation Relative to Gas and Oil Development in North Dakota</a></li></li><li>Kyle Goehner - <a href="kyle_project.php">Automated Wildlife Detection in Uncontrolled Environments</a></li></ul></p>',
                            'site' => array(
                                array (
                                    'enabled' => ($grouse_belden_available > 0),
                                    'site_name' => 'Belden, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'grouse_belden_progress',
                                    'site_description' => 'Cameras were placed at grouse nests in areas of intense gas and oil development.',
                                    'site_id' => '1',
                                    'validated_percentage' => $grouse_belden_validated,
                                    'available_percentage' => $grouse_belden_available - $grouse_belden_validated
                                ), 

                                array (
                                    'enabled' => ($grouse_blaisdell_available > 0),
                                    'site_name' => 'Blaisdell, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'grouse_blaisdell_progress',
                                    'site_description' => 'Cameras were placed at grouse nests in areas of low intensity of gas and oil development.',
                                    'site_id' => '2',
                                    'validated_percentage' => $grouse_blaisdell_validated,
                                    'available_percentage' => $grouse_blaisdell_available - $grouse_blaisdell_validated
                                ), 

                                array (
                                    'enabled' => ($grouse_lostwood_available > 0),
                                    'site_name' => 'Lostwood Wildlife Refuge, ND',
                                    'year' => '2012',
                                    'progress_id' => 'grouse_lostwood_progress',
                                    'site_description' => 'Cameras were placed at grouse nests in this National Wildlife Refuge, representing a historic grassland.',
                                    'site_id' => '3',
                                    'validated_percentage' => $grouse_lostwood_validated,
                                    'available_percentage' => $grouse_lostwood_available - $grouse_lostwood_validated
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => './images/thumbnail_least_tern.png',
                            'species_name' => 'Interior Least Tern',
                            'species_id' => '2',
                            'species_latin_name' => 'Sternula antillarum',
                            'info_webpage' => 'least_tern_info.php',
                            'project_description' => '<p>Interior least terns are federally listed as an endangered species. They nest on sandbars and islands along the Missouri River in western North Dakota.</p><p>Active projects include: <ul><li>Alicia Andes - <a href="alicia_project.php">Refined Monitoring Techniques to Understand Least Tern and Piping Plover Nest Dynamics</a></li><li>Kyle Goehner - <a href="kyle_project.php">Automated Wildlife Detection in Uncontrolled Environments</a></li></ul></p>',
                            'site' => array(
                                array (
                                    'enabled' => ($least_tern_available > 0),
                                    'site_name' => 'Missouri River, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'least_tern_progress',
                                    'site_description' => 'Cameras were placed at least tern nests along the Missouri River in western North Dakota.',
                                    'site_id' => '4',
                                    'validated_percentage' => $least_tern_validated,
                                    'available_percentage' => $least_tern_available - $least_tern_validated
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => './images/thumbnail_piping_plover.png',
                            'species_name' => 'Piping Plover',
                            'species_id' => '3',
                            'species_latin_name' => 'Charadrius melodus',
                            'info_webpage' => 'piping_plover_info.php',
                            'project_description' => '<p>Northern great plains piping plovers are federally listed as threatened species. They nest on sandbars and islands along the Missouri River and Alkali lakes in North Dakota.</p><p>Active projects include: <ul><li>Alicia Andes - <a href="alicia_project.php">Refined Monitoring Techniques to Understand Least Tern and Piping Plover Nest Dynamics</a></li></li><li>Kyle Goehner - <a href="kyle_project.php">Automated Wildlife Detection in Uncontrolled Environments</a></li></ul></p>',
                            'site' => array(
                                array (
                                    'enabled' => ($piping_plover_available > 0),
                                    'site_name' => 'Missouri River, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'piping_plover_progress',
                                    'site_description' => 'Cameras were placed at piping plover nests along the Missouri River in western North Dakota.',
                                    'site_id' => '4',
                                    'validated_percentage' => $piping_plover_validated,
                                    'available_percentage' => $piping_plover_available - $piping_plover_validated
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => './images/blue_winged_teal.png',
                            'species_name' => 'Blue Winged Teal',
                            'species_id' => '4',
                            'species_latin_name' => 'Anas discors',
                            'info_webpage' => '',
                            'project_description' => '<p>Blue-winged teal are small ducks that nest in the grasslands of the plains.  They are one of the most common ducks nesting in North Dakota.</p><p>Active projects include: <ul><li>John Palarski and Nickolas Conrad - <a href="ducks_unlimited_project.php">Predation and Parental Care at Blue-Winged Teal Nests in North Dakota</a></li></ul></p>',
                            'site' => array(
                                array (
                                    'enabled' => ($blue_winged_teal_available > 0),
                                    'site_name' => 'Coteau Ranch, ND',
                                    'year' => '2015',
                                    'progress_id' => 'blue_winged_teal_progress',
                                    'site_description' => 'Cameras were placed at blue winged teal nests along at the Coteau Ranch in western North Dakota.',
                                    'site_id' => '7',
                                    'validated_percentage' => $blue_winged_teal_validated,
                                    'available_percentage' => $blue_winged_teal_available - $blue_winged_teal_validated
                                )
                            )
                        )

                    )
                );

shuffle($thumbnails['thumbnail_list']);

$projects_template = file_get_contents($cwd[__FILE__] . "/templates/projects_template.html");

error_log( "projects_template: " . $cwd[__FILE__] . "/templates/projects_template.html");

$m = new Mustache_Engine;
echo $m->render($projects_template, $thumbnails);

print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "
</body>
</html>
";

?>
