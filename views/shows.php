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

<p><a href="https://www.thebroadwaterla.com/">Broadwater Theater</a> is at 6322 Santa Monica Blvd. Street parking. $10 optional donation. Our shows are usually in the Black Box Theatre though sometimes on the Second Stage. They're right next to each other.</p>

<a id='classshows'></a>
<h4>Broadwater Tuesdays 2023 Schedule</h4>
<p>Starting January 2023, we're moving to a new schedule with two shows a night. A class show at 7:30pm, and WGIS teams at 9pm. Team schedule will be announced in mid-December. Here's the class show schedule.</p>
<ul>7:30pm schedule
<li>Jan 10 - Play by Play </li>
<li>Jan 17 - Julie Level 5 </li>
<li>Jan 24 - Joel Level 5 / Jim Level 4</li>
<li>Jan 31 - Bitness Class Shows </li>
<li>Feb 7 - Will Level 2 </li>
<li>Feb 14 - Julie level 5 / Joel Level 5</li>
<li>Feb 21 - Will Level 2  / Jim Level 4</li>
<li>Feb 28 - Bitness Class Shows</li>
<li>Mar 7  - Jim Level 4 Class Show</li>
<li>Mar 14 - Will Level 2 / Will Level 4 Class Shows</li>
<li>Mar 21 - Joel Level 5 Show</li>
<li>Mar 28 - Bitness Class Shows</li>
<li>Apr 4 - Jim Level 4 Class Show</li>
<li>Apr 11 - Will Level 2 / Will Level 4 Class Shows</li>
<li>Apr 18 - Joel Level 5 Show</li>
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
<h2 class='mt-3'>Spring 2023 Teams Schedule</h2>
<p>Shows are twice a week: On Broadwater Tuesdays (Tuesdays 9pm) and Clubhouse Fridays (Fridays 7pm)</p>

<?php
	
$t = array(
	'Toretto', 
	'Bread Crew', 
	'St. Bernard', 
	'Shaw', 
	'Ghost Train', 
	'Letty', 
	'Ludacrisp', 
	'More Cow Bones');

$schedule = array(
	array( 'broad' => array ('January 10, 2023', $t[0], $t[1]), 
		   'club' => array ('January 13, 2023', $t[3], $t[2])),
		
	array( 'broad' => array ('January 17, 2023', $t[4], $t[5]), 
   		   'club' => array ('January 20, 2023', $t[6], $t[7])),		

	array(	'broad' => array ('January 24, 2023', $t[6], $t[2]), 
			'club' => array ('January 27, 2023', $t[4], $t[0])),		

	array(	'broad' => array ('January 31, 2023', $t[3], $t[7]), 
			'club' => array ('Feb 3, 2023', $t[5], $t[1])),		

	array(	'broad' => array ('Feb 7, 2023', $t[1], $t[6]), 
			'club' => array ('Feb 10, 2023', $t[0], $t[7])),		

	array(	'broad' => array ('Feb 14, 2023', $t[2], $t[4]), 
			'club' => array ('Feb 17, 2023', $t[3], $t[5])),		

	array(	'broad' => array ('Feb 21, 2023', $t[7], $t[3]), 
			'club' => array ('Feb 24, 2023', $t[1], $t[2])),		

	array(	'broad' => array ('Feb 28 , 2023', $t[5], $t[0]), 
			'club' => array ('Mar 3, 2023', $t[4], $t[6])),		

	array(	'broad' => array ('Mar 7, 2023', $t[3], $t[7]), 
			'club' => array ('Mar 10, 2023', $t[2], $t[4])),		

	array(	'broad' => array ('Mar 14, 2023', $t[0], $t[6]), 
			'club' => array ('Mar 17, 2023', $t[5], $t[1])),		

	array(	'broad' => array ('Mar 21, 2023', $t[1], $t[5]), 
			'club' => array ('Mar 24, 2023', $t[6], $t[0])),		

	array(	'broad' => array ('Mar 28, 2023', $t[4], $t[2]), 
			'club' => array ('Mar 31, 2023', $t[3], $t[7])),		

	array(	'broad' => array ('Apr 4, 2023', $t[2], $t[3]), 
			'club' => array ('Apr 7, 2023', $t[7], $t[6])),		

	array(	'broad' => array ('Apr 11, 2023', $t[5], $t[1]), 
			'club' => array ('Apr 14, 2023', $t[0], $t[4])),		

	array(	'broad' => array ('Apr 18, 2023', $t[7], $t[0]), 
			'club' => array ('Apr 21, 2023', $t[1], $t[2])),		

	array(	'broad' => array ('Apr 25, 2023', $t[6], $t[4]), 
			'club' => array ('Apr 28, 2023', $t[3], $t[5])),		

	array(	'broad' => array ('May 2, 2023', $t[7], $t[6]), 
			'club' => array ('May 5, 2023', $t[3], $t[2])),		

	array(	'broad' => array ('May 9, 2023', $t[4], $t[5]), 
			'club' => array ('May 12, 2023', $t[1], $t[0])),		

	array(	'broad' => array ('May 16, 2023', $t[3], $t[1]), 
			'club' => array ('May 19, 2023', $t[5], $t[4])),		

	array(	'broad' => array ('May 23, 2023', $t[0], $t[2]), 
			'club' => array ('May 26, 2023', $t[6], $t[7])),		


);
	
echo "
	<div class='row m-1 p-1 fw-bold bg-light'>
		<div class='col'><b>Broadwater Tuesdays 9pm</b></div>\n
		<div class='col'><b>Clubhouse Fridays 7pm</b></div>\n
	</div>\n";

foreach ($schedule as $s) {
	echo "
		<div class='row m-1 p-1'>
			<div class='col'><b>".date("M j", strtotime($s['broad'][0]))."</b>: {$s['broad'][1]} / {$s['broad'][2]}</div>\n
			<div class='col'><b>".date("M j", strtotime($s['club'][0]))."</b>: {$s['club'][1]} / {$s['club'][2]}</div>\n
		</div>\n";

}

	
?>

<h2 class='mt-3'>Spring 2023 Roster</h2>

<p><b><?php echo $t[0]; ?></b>: Alex Maystrik,
Andrew Coppola,
Andrew Sproge,
Eli Lloyd,
Erin Smith,
Isabella Escalante,
James Werner,
Jessica Sproge,
Pablo Hernandez</p>

<p><b><?php echo $t[1]; ?></b>: Aaron Singer,
Anja Boltz,
Brandon Waters,
David Luong,
Emily Ralph,
Erik Kistel,
Jeremy Sender,
Sean Smith</p>

<p><b><?php echo $t[2]; ?></b>: Amanda Bonar,
Artin Sarkisyan,
Cara Popecki,
Duncan Young,
John Bryant,
Justin Liu,
Lars Midthun, 
Rocky Strobel</p>

<p><b><?php echo $t[3]; ?></b>: Cassie Grilley,
Derek Polka,
Isabel Galbraith,
Ittai Geiger,
James Jelin,
Oleg Trofimo,
Sahil Desai,
Sebastian Davis</p>

<p><b><?php echo $t[4]; ?></b>: Bob Hsiao
Jason Van Glass,
Laney Serface,
Meredith Haspel Elliott,
Mickey Woo,
Nolan Purvis,
Phil Gould,
Ted Asbaghi</p>

<p><b><?php echo $t[5]; ?></b>: Anna Bezahler,
DarylJim Diaz,
Jeff Taylor,
Sparky Shelton,
Nick Luciano,
Ray Lewis,
Rosie Grant,
Russell Carter</p>


<p><b><?php echo $t[6]; ?></b>: Benedikt Sebastian,
BJ Schwartz,
Hermie Castillo,
Jack De Sanz,
Jonathan Carr,
Joy Regullano,
Sam Di,
Sara Keller</p>


<p><b><?php echo $t[7]; ?></b>: 
Brent Mukai,
Christine Stemmer,
DJ Rouse,
Elena Martinez,
Ezra Parter,
Ian Vens,
Judith Friedman,
Katherine Stiegemeyer</p>


