<h1 class="page-title mb-5">Teachers</h1>
	<div class="row justify-content-center">
		<div class="col-md-10 col-12">

<?php
foreach ($faculty as $f) {
	if (isset($tid) && $tid && $tid != $f['id']) {
		continue; // if we got passed an ID, only show that one
	}	
echo "<div class=\"row border-top p-3 m-3\">\n";
echo "<div class=\"col-sm-2 p-2\">".\Teachers\teacher_photo($f['user_id'])."</div>\n";
echo "	<div class=\"col-sm-10 p-2\">\n";
echo "		<h2>{$f['nice_name']}</h2>\n";
echo "<p>".preg_replace('/\R/', "<br>", $f['bio'])."</p>\n";


if (count($f['classes']) > 0) {
	echo "<p>Upcoming classes for {$f['nice_name']}:<ul>\n";
	foreach ($f['classes'] as $c) {
		$c['when'] = \XtraSessions\add_sessions_to_when($c['when'], $c['sessions']);
		echo "	<li><a href=\"workshop.php?wid={$c['id']}\">{$c['title']}</a>, {$c['capacity']} people max, \${$c['cost']} USD<br><div class='mx-4'>{$c['when']}</div></li>\n";
	}
	echo "</ul></p>\n";
}

echo "	</div>\n"; // end of col
echo "</div>\n"; // end of row
}