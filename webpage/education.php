<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/news.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/csg_uotd.php");


$css_header = "<link rel='stylesheet' type='text/css' href='wildlife_css/education_style.css'/>";

print_header("Wildlife@Home: Teaching & Learning", "$css_header", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
<div class='container'>
	<div class='row'>
		<div class='col-sm-12'><!--column allows all rows to match up in size--> 
				<img src='images/900px_leaninghouse.jpg' class='img-rounded' >				
		</div>
	</div>
	<div class='row row-centered'>
		<div class='col-sm-12'>	
			<p align='center'>
				Test
			</p>			
		</div>
	</div>
	<div class='row row-centered'>
		<div class='col-sm-4'>
			<div class='well' id='well1'>
				<p>K-5 Module</p>
					<a href=\"publications/Grade-5-Food-Web-Module.docx\">(download.docx)</a>
					<p></p>
					<img src='images/900x324_susan.jpg'>
				<p></p>
				<p> Isso é pra practicar. Precisava practicar portugues, enao vou aqui. Os fotos precisam aparacer lindos e capturar a atencao de todos que visitam o site </p>				
			</div><!--class-->
		</div><!--col-->
		<div class='col-sm-4'>
			<div class='well'>
				<p> 6-9 Module </p>
					<a href=\"publications/MS-Module.docx\">(download.doc)</a>
					<p></p>
				<img src='images/900x293_hatch.jpg'>
			</div><!--well-->
		</div><!--col-->
		<div class='col-sm-4'>
			<div class='well'>
				<p>10-12 Module </p>
					<a href=\"publications/HS-Module.docx\">(download.dc)</a>
					<p></p>
				<img src='images/900x276_oil.jpg'>
			</div>
		</div><!--col-->
	</div><!--row-->
	<div class='row'>
		<div class='col-sm-12'>
			<div class='well'>
				test yoo
			</div>
		</div><!--col-->		
	</div><!--row-->
</div><!--container-->
";

/*echo "
<style type=\"text/css\">
	.modlink {margin-left: 5px;}
		.modlink textarea {height: 100px; width: 300px; margin-top: 5px; margin-bottom: 10px;}
		.linksmall {font-weight: underline; margin-left: 15px;}
			.linksmall img {margin-right: 5px;}
		.modhead {font-size: 15px;}
	.modtable {margin-top: 5px;}
	.modtable td {padding: 15px; border: 1px solid #000000;}
</style>
";
echo"
<div class=\"container\">
	<div class=\"row\">
		<h2>Wildlife@Home Education Modules</h2>
	</div>
	<div id=\"elementary\" class=\"well\">
		<h3>K-5 Modules</h3>
		<div class=\"modulecon\">
			<span class=\"modhead\">Grade 5 Module: Food Web</span> <span class=\"linksmall\"><a href=\"publications/Grade-5-Food-Web-Module.docx\"><img src=\"images/download_icon.png\" />(download .docx)</a></span>
			<table class=\"modtable\">
				<tr>
					<td>Primary Standard</td>
					<td>5-LS2 Ecosystems: Interactions, energy, and dynamics</td>
				</tr>
				<tr>
					<td>Core Ideas</td>
					<td>LS2.A: Interdependent Relationships in Ecosystems</td>
				</tr>
				<tr>
					<td>Supporting Standards</td>
					<td>5-LS2-1: Develop a model to describe the movement of matter among plants, animals, decomposers, and the environment</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>Students will be asked to evaluate the flow of energy and nutrients within an ecosystem by first examining a food chain, and then evaluating a more complex food web.</td>
				</tr>
			</table>
		</div>
	</div>
	<div id=\"middlesc\" class=\"well\">
		<h3>6-8 Modules</h3>
		<div class=\"modulecon\">
			<span class=\"modhead\">Middle School Module: Impacts of Gas and Oil on Nest Success of Sharp-Tailed Grouse in Western North Dakota</span> <span class=\"linksmall\"><a href=\"publications/MS-Module.docx\"><img src=\"images/download_icon.png\" />(download .docx)</a></span>
			<table class=\"modtable\">
				<tr>
					<td>Primary Standard</td>
					<td>MS-LS2-4: Construct an argument supported by empirical evidence that changes to physical or biological components of an ecosystem affect populations.</td>
				</tr>
				<tr>
					<td>Core Ideas</td>
					<td>LS2.A: Interdependent Relationships in Ecosystems</td>
				</tr>
				<tr>
					<td>Supporting Standards</td>
					<td>
						MS-LS1-6: Scientific Knowledge is Based on Empirical Evidence
						<br />
						MS-LS1-8: Obtaining, Evaluation, and Communicating Information
					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>The UND Wildlife at Home Education Middle School Module entitled <i>Impacts of Gas and Oil on Nest Success of Sharp-tailed Grouse in Western North Dakota</i> was developed to address MS-LS2-4 and LS2.A.  It asks students to construct an argument supported by empirical evidence that changes to the physical or biological components of an ecosystem affect populations by presenting students with real-world data from three study sites in Western North Dakota.  These three sites represent areas of high, medium and low intensity development of gas and oil extraction.  Students use real data collected as part of a study by UND, North Dakota Game and Fish, and Brigham Young University to identify predators captured on camera.  Students then classify their data and use it to calculate nest success rates, predation rates and types, and chick survival to make inferences about the effects of gas and oil development on the population of Sharp-tailed Grouse in Western North Dakota.  MS-LS1-6 and MS-LS1-8 are also supported because the theme of obtaining, analyzing, and communicating empirical evidence is emphasized throughout the module.</td>
				</tr>
			</table>
		</div>
	</div>
	<div id=\"highsc\" class=\"well\">
		<h3>9-12 Modules</h3>
		<div class=\"modulecon\">
			<span class=\"modhead\">High School Module: Impacts of Gas and Oil on Nest Success of Sharp-Tailed Grouse in Western North Dakota, and the Implications for Population Trends</span> <span class=\"linksmall\"><a href=\"publications/HS-Module.docx\"><br /><img src=\"images/download_icon.png\" />(download .docx)</a></span>
			<table class=\"modtable\">
				<tr>
					<td>Primary Standard</td>
					<td>HS-LS2-2: Use mathematical representations to support and revise explanations based on evidence about factors affecting biodiversity and populations in ecosystems of different scales.</td>
				</tr>
				<tr>
					<td>Core Ideas</td>
					<td>LS2.A: Interdependent Relationships in Ecosystems</td>
				</tr>
				<tr>
					<td>Supporting Standards</td>
					<td>
						Engaging in Argument from Evidence; HS-LS2-6: Evaluate the claims, evidence, and reasoning behind currently accepted explanations or solutions to determine the merits of arguments.
						<br />
						Connections to Nature of Science; Scientific Knowledge is Open to Revision in Light of New Evidence; HS-LS2-2 & HS-LS2-3: Most scientific knowledge is quite durable, but is, in principle, subject to change based on new evidence and/or reinterpretation of existing evidence; HS-LS2-6 & HS-LS2-8: Scientific argumentation is a mode of logical discourse used to clarify the strength of relationships between ideas and evidence that may result in revision of an explanation.
					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>The UND Wildlife at Home Education High School Module entitled <i>Impacts of Gas and Oil on Nest Success of Sharp-tailed Grouse in Western North Dakota</i> was developed to address HS-LS2-2 and LS2.A.  It asks students to construct an argument supported by empirical evidence that changes to the physical or biological components of an ecosystem affect populations by presenting students with real-world data from three study sites in Western North Dakota.  These three sites represent areas of high, medium and low intensity development of gas and oil extraction.  Students use real data collected as part of a study by UND, North Dakota Game and Fish, and Brigham Young University to identify predators captured on camera.  Students then classify their data and use it to calculate nest success rates, predation rates and types, and chick survival to make inferences about the effects of gas and oil development on the population of Sharp-tailed Grouse in Western North Dakota.  HS-LS2-6 and HS-LS2-8 are also supported because the theme of obtaining, analyzing, and communicating empirical evidence is emphasized throughout the module.</td>
				</tr>
			</table>
		</div>
	</div>
	<div id=\"suggest\" class=\"well\">
		<h3>Module Suggestions</h3>
		<div class=\"modulecon\">
			If you have any suggestions for or questions about the modules, feel free to drop by in the <a href=\"http://volunteer.cs.und.edu/csg/forum_forum.php?id=3\">Education and Learning forum</a>.
		</div>
	</div>
</div>";
*/

print_footer('Travis Desell, Susan Ellis-Felege, Lindsey Wingate and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "</body></html>";

?>
