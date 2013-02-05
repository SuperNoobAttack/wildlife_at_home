<?php

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

echo "<html>
<head>
        <meta charset='utf-8'>
        <title>Wildlife@Home: Video Selection</title>

        <link rel='alternate' type='application/rss+xml' title='Wildlife@Home RSS 2.0' href='http://volunteer.cs.und.edu/wildlife/rss_main.php'>
        <link rel='icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
        <link rel='shortcut icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>

        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta name='description' content=''>
        <meta name='author' content=''>

        <!-- Le styles -->
        <link href='assets/css/bootstrap.css' rel='stylesheet'>
        <link href='assets/css/bootstrap-responsive.css' rel='stylesheet'>

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src='http://html5shim.googlecode.com/svn/trunk/html5.js'></script>
        <![endif]-->

        <style>
        .carousel { height:450px; }
        .item {
            height:450px;
        }   

        hr.news_line {
            border: 0;
            border-bottom: 1px solid rgb(200, 200, 200);
        }

        td.news {
            background-color: #dff0ff;
            border-color: #add8e6;
        }

        span.news_title {
            font-weight: bold;
        }

        span.news_date {
            color: rgb(100,100,100);
            font-size: 0.9em;
            float: right;
        }

        </style>

        <!-- Fav and touch icons -->
        <link rel='apple-touch-icon-precomposed' sizes='144x144' href='assets/ico/apple-touch-icon-144-precomposed.png'>
        <link rel='apple-touch-icon-precomposed' sizes='114x114' href='assets/ico/apple-touch-icon-114-precomposed.png'>
        <link rel='apple-touch-icon-precomposed' sizes='72x72' href='assets/ico/apple-touch-icon-72-precomposed.png'>
        <link rel='apple-touch-icon-precomposed' href='assets/ico/apple-touch-icon-57-precomposed.png'>
        <link rel='shortcut icon' href='assets/ico/favicon.png'>

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src='assets/js/jquery.js'></script>
        <script src='assets/js/bootstrap-transition.js'></script>
        <script src='assets/js/bootstrap-alert.js'></script>
        <script src='assets/js/bootstrap-modal.js'></script>
        <script src='assets/js/bootstrap-dropdown.js'></script>
        <script src='assets/js/bootstrap-scrollspy.js'></script>
        <script src='assets/js/bootstrap-tab.js'></script>
        <script src='assets/js/bootstrap-tooltip.js'></script>
        <script src='assets/js/bootstrap-popover.js'></script>
        <script src='assets/js/bootstrap-button.js'></script>
        <script src='assets/js/bootstrap-collapse.js'></script>
        <script src='assets/js/bootstrap-carousel.js'></script>
        <script src='assets/js/bootstrap-typeahead.js'></script>
        <script>
          !function ($) {
            $(function(){
              // carousel demo
              $('.item').eq(Math.floor((Math.random() * $('.item').length))).addClass('active');
              $('#myCarousel').carousel({ interval: false })
            })
          }(window.jQuery)
    </script>

</head>
<body>";

echo "
    <!-- NAVBAR
    ================================================== -->
    <div class='navbar navbar-inverse navbar-fixed-top'>
        <div class='navbar-inner'>
            <div class='container-fluid'>
                <!-- Responsive Navbar Part 1: Button for triggering responsive navbar (not covered in tutorial). Include responsive CSS to utilize. -->
                <a class='btn btn-navbar' data-toggle='collapse' data-target='.nav-collapse'>
                  <span class='icon-bar'></span>
                  <span class='icon-bar'></span>
                  <span class='icon-bar'></span>
                </a>

                <!-- Responsive Navbar Part 2: Place all navbar contents you want collapsed withing .navbar-collapse.collapse. -->
                <div class='nav-collapse collapse'>
                    <ul class='nav'>
                        <li class='brand'>Wildlife@Home </li>
                        <li><a href='http://volunteer.cs.und.edu/wildlife/index.php'>Home</a></li>
                        <li><a href='http://volunteer.cs.und.edu/wildlife/forum_index.php'>Message Boards</a></li>

                        <li class='dropdown'>
                          <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Your Account<b class='caret'></b></a>
                          <ul class='dropdown-menu'>
                            <li><a href='home.php'>Your Preferences</a></li>
                            <li><a href='team.php'>Teams</a></li>
                            <li><a href='cert1.php'>Certificate</a></li>
                            <li><a href='apps.php'>Applications</a></li>
                          </ul>
                        </li>

                        <li class='dropdown'>
                          <a href='#' class='dropdown-toggle' data-toggle='dropdown'>About the Wildlife<b class='caret'></b></a>
                          <ul class='dropdown-menu'>
                            <li><a href='sharptailed_grouse_info.php'>Sharptailed Grouse</a></li>
                            <li><a href='#'>Piping Plover (Coming Soon)</a></li>
                            <li><a href='#'>Least Tern (Coming Soon)</a></li>
                          </ul>
                        </li>

                        
                        <li class='dropdown'>
                          <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Community<b class='caret'></b></a>
                          <ul class='dropdown-menu'>
                            <li><a href='profile_menu.php'>Profiles</a></li>
                            <li><a href='user_search.php'>User Search</a></li>
                            <li><a href='language_select.php'>Languages</a></li>
                            <li class='nav-header'>Top Lists</li>
                            <li><a href='top_bossa_users.php'>Top Video Watchers</li>
                            <li><a href='top_users.php'>Top Users</li>
                            <li><a href='top_hosts.php'>Top Hosts</li>
                            <li><a href='top_teams.php'>Top Teams</li>
                            <li><a href='stats.php'>More Statistics</a></li>
                          </ul>
                        </li>

                        <li><a href='http://volunteer.cs.und.edu/wildlife/index.php#contact'>Contact</a></li>
                    </ul>
                </div> <!-- /.nav-collapse-->
            </div>  <!-- container-fluid -->
        </div>  <!-- navbar-inner -->
    </div>  <!-- navbar -->

    <br>";

$thumbnails = array('thumbnail_list' => array(
                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_sharptailed_grouse.png',
                            'species_name' => 'Sharptailed Grouse',
                            'species_id' => '0',
                            'species_latin_name' => 'Tympanuchus phasianellus',
                            'project_description' => 'Species description... <a href=\'http://volunteer.cs.und.edu/wildlife/sharptailed_grouse_info.php\'>Learn more about the sharptailed grouse</a>.',
                            'site' => array(
                                array (
                                    'site_name' => 'Belden, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '0',
                                    'progress_percentage' => '50'
                                ), 
                                array (
                                    'site_name' => 'Blaisdell, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '1',
                                    'progress_percentage' => '10'
                                ), 
                                array (
                                    'site_name' => 'Lostwood Wildlife Refuge, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '2',
                                    'progress_percentage' => '30'
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_least_tern.png',
                            'species_name' => 'Interior Least Tern',
                            'species_id' => '1',
                            'species_latin_name' => 'Sternula antillarum',
                            'project_description' => 'Species description...',
                            'site' => array(
                                array (
                                    'site_name' => 'Missouri River, MN',
                                    'site_description' => 'Site description...',
                                    'site_id' => '2',
                                    'progress_percentage' => '0'
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_piping_plover.png',
                            'species_name' => 'Piping Plover',
                            'species_id' => '2',
                            'species_latin_name' => 'Charadrius melodus',
                            'project_description' => 'Species description...',
                            'site' => array(
                                array (
                                    'site_name' => 'Missouri River, MN',
                                    'site_description' => 'Site description...',
                                    'site_id' => '2',
                                    'progress_percentage' => '0'
                                )
                            )
                        )
                    )
                );

$thumbnail_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/thumbnail_template.html");

$m = new Mustache_Engine;
echo $m->render($thumbnail_template, $thumbnails);

echo "
    <!-- Footer
    ================================================== -->
    <footer class='footer'>
        <div class='container'>
            <center>
            <p>Designed by <a href='http://people.cs.und.edu'>Travis Desell</a> with much help from <a href='http://twitter.github.com/bootstrap/getting-started.html'>Twitter's Bootstrap</a>.</p>
            <p>&copy; Travis Desell, Susan Ellis-Felege and the University of North Dakota 2013</p>
            </center>
        </div>
    </footer>


</body>
</html>
";

?>
