<h1 class="page-title">Shows</h1>
<p>Click to jump down to the <a href='#teams'>list of Harold team shows</a></p>
 
<!--img class="float-end mx-3" src='images/teams.png' alt='WGIS teams'-->

<table class='table'>
	<caption>art by Nikki Rodriguez</caption>
	<tr>
		<td><a href='#fridays'><img src='images/wgis_harold.jpg'></a></td>
		<td><a href='#tuesdays'><img src='images/broadwater_small.jpg'></a></td>
	</tr>
	<tr>
		<td><img src="images/live_aj2.jpg" alt='Adam Jilt'></td>
		<td><img src='images/live_playbyplay.jpg' alt='Play by Play'></td>
	</tr>
</table>

<!--
-->

<p>WGIS current has two nights of in-person shows in sunny Los Angeles, California. On Tuesdays we have Broadwater Tuesdays featuring teams, tourneys and jams. And on Fridays we do Harolds with our house teams as well as a teacher set.</p>

<a id="tuesdays"></a>
<h3>Broadwater Tuesdays</h3>

<p>Starting in Novemeber 2022, WGIS is doing shows at Broadwater Theater on Tuesday Nights. We have three shows every week:</p>

<ul>
	<li>7pm - Two teams do a set. Featuring UCB teams, WE teams and WGIS mash-up teams</li>
	<li>8:30pm - We're currently doing a 3prov tournament in this spot. But we have plans to do other fun formats including: Play by Play (improv w/ sports commentary), all-wigs nights (WGIS WIGS), Boxing Day sets (bad British accents) and more</li>
	<li>10pm - Open Jam. Hosted by Jim Woods, Sarah Claspell and Will Hines. Throw your name in the hat and jam.</li>
</ul>

<p><a href="https://www.thebroadwaterla.com/">Broadwater Theater</a> is at 6322 Santa Monica Blvd. Street parking. $10 optional donation. Our shows are usually in the Black Box Theatre though sometimes on the Second Stage. They're right next to each other.</p>

<a id='classshows'></a>
<h4>Broadwater Tuesdays 2023 Schedule</h4>
<p>Starting January 2023, we're moving to a new schedule with two shows a night. A class show at 7:30pm, and WGIS teams at 9pm. Team schedule will be announced in mid-December. Here's the class show schedule.</p>
<ul>7:30pm schedule
<li>Jan 3 - play by play</li>
<li>Jan 10 - Level 5 </li>
<li>Jan 17 - Level 5</li>
<li>Jan 24 - Sarah Level 3 / Jim Level 4</li>
<li>Jan 31 - Julie Level 1 / Will Level 2 </li>
<li>Feb 7 - Level 5</li>
<li>Feb 14 - Julie level 1 / Sarah Level 3</li>
<li>Feb 21 - Will Level 2  / Jim Level 4</li>
</ul>

<a id="fridays"></a>
<h3>Clubhouse Fridays</h3>

<table class='table'>
	<tr>
		<td><img src='images/live_cf_jump.jpg' alt='Clubhouse Fridays jump'></td>
		<td>	
			<img src="images/clubhouselocation.png" class="figure-img img-fluid rounded" alt="Clubhouse Location">
			<p class='text-muted small'>7pm showtime, door code 0279#</p></td>

	</tr>
</table>


<p>WGIS has four in-person house teams. Every Friday 7pm at the Clubhouse, two house teams do a Harold, followed by a set from Jim Woods, Will Hines and Sarah Claspell (and sometimes guests). $10 optional donation.</p>

<p>The Clubhouse is a community improv black box theater. It's at 1607 N. Vermont Ave in a shopping center. There's a door code required which is 0279#. See above photo for the kinda hidden entrance! We're in the downstairs (main) stage.</p>

<a id='teams'></a>
<h2>Fall/Winter 2022 Teams Schedule</h2>


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
	array ('October 7, 2022', "$t4 / $t3"),
	array ('October 14, 2022', "$t1 / $t2"),
	array ('October 21, 2022', "$t3 / $t4"),
	array ('October 28, 2022', "Bitness Class Shows"),
	array ('November 4, 2022', "$t1 / $t2"),
	array ('November 11, 2022', "$t4 / $t3"),
	array ('November 18, 2022', "Bitness Class Shows"),
	array ('November 25, 2022', "Thanksgiving week - TBA"),
	*/
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


<h2>Fall/Winter 2022 Roster</h2>

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


