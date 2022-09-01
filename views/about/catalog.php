<h1 class="page-title mb-5">Course Catalog</h1>
	<div class="row justify-content-center">
		<div class="col-md-10 col-12">

<small>Updated August 2022</small>

<h2>Levels</h2>
<p>Starting August 2022, we are offering four levels. There are in person only right now, though we will at some point offer them online too.</p>

<ul>
	<li>Level 1: Intro to Improv</li>
	<li>Level 2: Game of the Scene</li>
	<li>Level 3: Harold Structure</li>
	<li>Level 4: Advanced Harold</li>
</ul>

<p>See the <a href="/classes">list of classes</a> to see which of these are being offered soon.</p>

<h2>All Past Courses</h2>
<p>We've done a lot of different classes and workshops since we started in March 2020. Below is a list of everything in reverse chronological order. If two courses had the exact same title, only the most recent is shown.</p>

<p><a href='#inperson'>In person courses</a> | <a href='#online'>online courses</a></p>

<?php
	
$ip = "";
$ol = "";
$titles = array();
foreach ($classes as $id => $c) {

	if (in_array($c['title'], $titles)) {
		continue;
	}
	$titles[] = $c['title'];
	
	$tname = get_teacher_name($c);
	
	$html_row = "<div class='row mt-1'>
		<div class='col'><a href='/workshop/view/{$id}'>{$c['title']}</a></div>
		<div class='col'><small>$tname</small></div>
		<div class='col'>".\Wbhkit\figure_year_minutes(strtotime($c['start']))."</div>
		</div>\n";
	
	if (strpos($c['tags'], 'inperson') === false) {
		$ol .= $html_row;
	} else {
		$ip .= $html_row;
	}
}	

?>

<a id='inperson'></a>
<h3>In Person Courses</h3>
<?php echo $ip; ?>

<a id='online'></a>
<h3>Online Courses</h3>
<?php echo $ol; ?>

</div></div>

<?php
	
function get_teacher_name($row) {
	$row['teacher_info'] = \Teachers\get_teacher_by_id($row['teacher_id']);
	$row['co_teacher_info'] = \Teachers\get_teacher_by_id($row['co_teacher_id']);
	
	$tname =  teacher_link($row['teacher_info']);
	if ($row['co_teacher_info']['id']) { $tname .= ", ".teacher_link($row['co_teacher_info']); }
	return $tname;
}

function teacher_link($tinfo) {
	if ($tinfo['id']) {
		return $tinfo['nice_name'];
	}
	return null;
}
?>

