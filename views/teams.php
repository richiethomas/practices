<h1 class="page-title mb-5">Teams</h1>
 
<!--img class="float-end mx-3" src='images/teams.png' alt='WGIS teams'-->

<table class='table'>
	<tr>
		<td><img src='images/wgis_cf.png'></td>
		<td><img src='images/clubhouselocation.png'><p class='m-2 text-center'>Clubhouse Fridays<br>7pm showtime<br>Door code #0279</p></td>
	</tr>
</table>

<p>WGIS has four in-person house teams. Every Friday, two house teams do a Harold, followed by a set from Jim Woods and Will Hines and guests. The show where they perform is Clubhouse Fridays, which happens every Friday 7:15pm in Los Angeles at a black box theater called The Clubhouse. Rosters and schedule below.</p>

<p>

<h2>August - October 2022 Schedule</h2>


<?php
	
$t1 = 'Adam Jilt';
$t2 = 'Dearly Beloved';
$t3 = 'Party Horses Say Hay';
$t4 = 'The Funnies';
$schedule = array(
	/*
	array ('July 22, 2022', "$t1"),
	array ('July 29, 2022', "Bitness Class Shows"),
	array ('August 5, 2022', "$t2 / $t1"),
	array ('August 12, 2022', "$t4 / $t2"),
	array ('August 19, 2022', "$t3 / $t1"),
	array ('August 26, 2022', "Bitness Class Shows"),
	array ('September 2, 2022', "$t2 / $t4"),
	array ('September 9, 2022', "$t4 / $t1"),
	array ('September 16, 2022', "$t3 / $t2"),
	array ('September 23, 2022', "$t1 / $t3"),
	array ('September 30, 2022', "Bitness Class Shows"),
	*/
	array ('October 7, 2022', "$t4 / $t3"),
	array ('October 14, 2022', "$t1 / $t2"),
	array ('October 21, 2022', "$t3 / $t4"),
	array ('October 28, 2022', "Bitness Class Shows"),
	array ('November 4, 2022', "$t1 / $t2"),
	array ('November 11, 2022', "$t4 / $t3"),
	array ('November 18, 2022', "Bitness Class Shows"),
	array ('November 25, 2022', "Thanksgiving week - TBA"),
	array ('December 2, 2022', "$t4 / $t1"),
	array ('December 9, 2022', "$t3 / $t2"),
	array ('December 16, 2022', "Bitness Class Shows"),
	array ('December 23, 2022', "Holiday week - TBA"),
	array ('December 30, 2022', "Holiday week - TBA")
	
);
	
foreach ($schedule as $s) {
	echo "<p><b>".date("M j", strtotime($s[0]))."</b>: {$s[1]}</p>\n";
}

	
?>


<h2>August - December 2022 Roster</h2>

<p><b><?php echo $t1; ?></b>: Anna Bezahler, 
Artin Sarkisyan, 
DarylJim Diaz,
Isabella Escalante, 
Jessica Dahlgren,
Lars Midthun, 
Meredith Haspel-Elliott,
Ted Asbaghi <br>
COACH: Jim Woods</p>

<p><b><?php echo $t2; ?></b>: Andrew Sproge,
Anja Boltz,
Bob Hsiao,
Cassie Grilley,
Judith Friedman,
Matt Rubano,
Nolan Purvis,
Russell Carter<br>
COACH: Will Hines</p>

<p><b><?php echo $t4; ?></b>: Benedikt Sebastian,
Isabel Galbraith,
Jessica Sproge,
Justin Liu,
Kelly Hannah,
Nick Luciano,
Spencer Kruse,
Rocky Strobel<br>
COACH: Jim Woods</p>

<p><b><?php echo $t3; ?></b>: Amanda Bonar,
Cara Popecki,
BJ Schwartz,
Erik Kestel,
Harrison Merkt,
Phil Gould,
Sara Keller,
Sebastian Davis<br>
COACH: Sarah Claspell</p>


