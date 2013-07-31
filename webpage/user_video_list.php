<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Watched Videos</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript' src='user_video_list.js'></script>

    <style>
    body {
        padding-top: 60px;
    }

    @media (max-width: 979px) {
        body {
            padding-top: 0px;
        }
    }

        .well {
           position: relative;
           margin: 15px 5px;
           padding: 39px 19px 14px;
           *padding-top: 19px;
           border: 1px solid #ddd;
           -webkit-border-radius: 4px;
           -moz-border-radius: 4px;
           border-radius: 4px; 
        }

        .tab {
           position: absolute;
           top: -1px;
           left: -1px;
           padding: 3px 7px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 0 4px 0;
           -moz-border-radius: 4px 0 4px 0;
           border-radius: 4px 0 4px 0;
        }

        .title {
            text-align: center;
           position: absolute;
           top: -1px;
           left: -1px;
           width: 100%;
           padding: 3px 0px 0px 0px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 4px 0px 0px;
           -moz-border-radius: 4px 4px 0px 0px;
           border-radius: 4px 4px 0px 0px;
        }

        .label {
            cursor: pointer;
        }

.bottom-up {top: auto; bottom: 100%; }
.dropdown-menu.bottom-up:before { border-bottom: 0px solid transparent !important; border-top: 7px solid rgba(0, 0, 0, 0.2); top: auto !important; bottom: -7px; }
.dropdown-menu.bottom-up:after  { border-bottom: 0px solid transparent !important; border-top: 6px solid white;              top: auto !important; bottom: -6px; }
    </style>
";

$user = get_logged_in_user();
$user_id = $user->id;

echo "<script type='text/javascript'>
    var user_id = $user_id; 
</script>";



echo "
</head>
<body>";


$active_items = array(
                    'home' => '',
                    'watch_video' => '',
                    'message_boards' => '',
                    'preferences' => 'active',
                    'about_wildlife' => '',
                    'community' => ''
                );

print_navbar($active_items);

echo "
    <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 5px'> 
        <div class='row-fluid'>
            <div class='container'>
                <div class='span12'>
                    <div class='btn-group pull-right' style='margin-bottom:10px'>
                        <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='species-button'>
                        Any Species <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu'>
                            <li><a href='#' id='display-any-species-dropdown'>Any Species</a></li>
                            <li><a href='#' id='display-grouse-dropdown'>Sharp-tailed Grouse</a></li>
                            <li><a href='#' id='display-tern-dropdown'>Interior Least Tern</a></li>
                            <li><a href='#' id='display-plover-dropdown'>Piping Plover</a></li>
                        </ul>
                    </div>

                    <div class='btn-group pull-right'>
                        <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='location-button'>
                        Any Location <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu'>
                            <li><a href='#' id='display-any-location-dropdown'>Any Location</a></li>
                            <li><a href='#' id='display-belden-dropdown'>Belden, ND</a></li>
                            <li><a href='#' id='display-blaisdell-dropdown'>Blaisdell, ND</a></li>
                            <li><a href='#' id='display-lostwood-dropdown'>Lostwood Wildlife Refuge, ND</a></li>
                            <li><a href='#' id='display-missouri-river-dropdown'>Missouri River, ND</a></li>
                        </ul>
                    </div>

                    <span style='margin-top:5px' class='label nav-li' id='invalid-nav-pill'>Invalid</span>
                    <span class='label nav-li' id='interesting-nav-pill'>Interesting</span>
                    <span class='label nav-li' id='bird-presence-nav-pill'>Bird Presence</span>
                    <span class='label nav-li' id='bird-absence-nav-pill'>Bird Absence</span>
                    <span class='label nav-li' id='chick-presence-nav-pill'>Chick Presence</span>
                    <span class='label nav-li' id='predator-presence-nav-pill'>Predator Presence</span>
                    <br>
                    <span style='margin-bottom:15px' class='label nav-li' id='nest-defense-nav-pill'>Nest Defense</span>
                    <span class='label nav-li' id='nest-success-nav-pill'>Nest Success</span>
                    <span class='label nav-li' id='bird-leave-nav-pill'>Bird Leave</span>
                    <span class='label nav-li' id='bird-return-nav-pill'>Bird Return</span>
                    <span class='label nav-li' id='too-dark-nav-pill'>Too Dark</span>
                    <span class='label nav-li' id='corrupt-nav-pill'>Corrupt</span>
                </div>
            </div>
        </div>
    </div>";

echo "<div id='videos-placeholder'></div>";
echo "<div id='videos-nav-placeholder'></div>";

print_footer();

echo "
</body>
</html>
";

?>
