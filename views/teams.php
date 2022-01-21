<h1 class="page-title mb-5">Teams</h1>
 
<img class="float-end mx-3" src='images/teams.png' alt='WGIS teams'>

<p>First of all, we have online shows that are broadcast on our Twitch channel.<br><a href="https://www.twitch.tv/wgimprovschool">https://www.twitch.tv/wgimprovschool</a></p>

<p>WGIS has six house teams. Here's how they work. Every three months, we pick a batch of students, assemble them into teams, assign them a coach and schedule them for some shows. The goal is to educate and develop: help improv nerds meet like minded people and get some practice with a coach.</p>

<p>Thank you for consulting this bare bones, all-business web page. :)</p>

<h2>Shows</h2>

<p>Every <?php
		$ts1 = \Wbhkit\convert_tz("January 3 2022 11am", $u->fields['time_zone'], 'l ga');
		$ts2 = \Wbhkit\convert_tz("January 3 2022 5pm", $u->fields['time_zone'], 'l ga');
		echo "$ts1 and $ts2 ({$u->fields['time_zone_friendly']})";
		?> , two of the house teams play (that's two at each time). These shows stream on our <a href="community.php#twitch">twitch channel</a>.</p>

<h2>January - April 2022 Schedule</h2>


<?php
	
$t1 = 'Voltage';
$t2 = 'The Office Dibbler Quintet';
$t3 = 'Robot';
$t4 = 'Drama Turkey';
$t5 = 'Voice of a Dog';
$t6 = 'Screaming In Supermarkets (Loud)';
$schedule = array(
	array ('January 17 2022', "$t1 / $t2", "$t4 / $t5"),
	array ('January 24 2022', "$t3 / $t1", "$t6 / $t4"),
	array ('January 31 2022', "$t2 / $t3", "$t5 / $t6"),
	array ('February 7 2022', "$t1 / $t2", "$t4 / $t5"),
	array ('February 14 2022', "$t3 / $t1", "$t6 / $t4"),
	array ('February 21 2022', "$t2 / $t3", "$t5 / $t6"),
	array ('February 28 2022', "$t1 / $t2", "$t1 / $t2"),
	array ('March 7 2022', "$t3 / $t1", "$t6 / $t4"),
	array ('March 14 2022', "$t2 / $t3", "$t5 / $t6"),
	array ('March 21 2022', "$t1 / $t2", "$t4 / $t5"),
	array ('March 28 2022', "$t3 / $t1", "$t6 / $t4"),
	array ('April 4 2022', "$t1 / $t3", "$t5 / $t6"),
	array ('April 11 2022', "$t4 / $t5", "$t4 / $t5"),
	array ('April 18 2022', "$t3 / $t1", "$t6 / $t4"),
	array ('April 25 2022', "Blender Show (all teams welcome)", "Blender Show (all teams welcome)"),
	
);
	
foreach ($schedule as $s) {
	echo "<p><b>".\Wbhkit\convert_tz($s[0].' 11am', $u->fields['time_zone'], 'F j')."</b><br>\n";
	echo \Wbhkit\convert_tz($s[0].' 11am', $u->fields['time_zone'], 'ga').": ".$s[1]."<br>\n";
	echo \Wbhkit\convert_tz($s[0].' 5pm', $u->fields['time_zone'], 'ga').": ".$s[2]."</p>\n";	
}

	
?>


<h2>Janaury - April 2022 Roster</h2>

<h4><?php echo "{$ts1} Teams ({$u->fields['time_zone_friendly']})"; ?> Teams</h4>

<p>VOLTAGE: Alice Wu, 
Amanda Bigford, 
Becky Webb, 
Dan Smith, 
Gracie Goodhart, 
Naiema Din, 
Sam Boles.<br>
COACH: Erick Acuna</p>

<p>THE OFFICER DIBBLE QUINTET: 
Brian Cruz,
Don Colliver,
Jon Branch,
Karen Brelsford,
Keith Estrella,
Max Bank,
Taylor Gray<br>
COACH: Christine Simpson</p>

<p>ROBOT: Andrew Hopper,
Bob Hsaio, 
Dmytro Lazar,
Jordana Mishory,
Julia Kelly,
Kristen Drenning,
Todd Sullivan.<br>
COACH: Sarah Claspell</p>

<h4><?php echo "{$ts2} Teams ({$u->fields['time_zone_friendly']})"; ?> Teams</h4>

<p>DRAMA TURKEY: Álvaro Méndez,
Cassie Grilley,
Jessie Catherine Webber,
Kevin DiLucente,
Neville Bharucha,
Rosie Grant,
Tyler Ross.<br>
COACH: Jason Perez</p>

<p>VOICE OF A DOG: Adam Slager,
Andy Morrison,
Jenna Jacobsen,
John Dardenne,
Magda Mukthar,
Michael Brown,
Stan Ferguson,
Victoria Koenitzer.<br>
COACH: Michelle Gilliam</p>

<p>SCREAMING IN SUPERMARKETS (LOUD): Isabel Galbraith,
Levi Meltzer,
Oleg,
Ryan Crowe,
Sahil Desai,
Seth Nathan Green,
Sebastian Hernandez,
Tim Dunk.<br>
COACH: Ethan Smith.</p>




