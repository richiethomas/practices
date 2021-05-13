<?php
$show_all = 1;
$hd = "Teachers";
if (isset($tid) && $tid) {
	$show_all = 0;
	foreach ($faculty as $f) {
		if ($tid == $f['id']) {
			$hd .= ': '.$f['nice_name'];
		}
	}
}	
?>

<h1 class="page-title mb-4"><?php echo $hd; ?></h1>
	<div class="row justify-content-between  align-items-stretch  align-content-stretch">

<?php
foreach ($faculty as $f) {
	if (!$show_all && $tid != $f['id']) {
		continue; // if we got passed an ID, only show that one
	}	
?>	
	<div class="col-sm-<?php echo ($show_all ? '6' : '12'); ?> teachers_listings-teacher align-self-stretch">
	    <div class="teacher-info mb-4">
				 <img class="teacher-image align-self-center" src="<?php echo \Teachers\get_teacher_photo_src($f['user_id']);?>" alt="Teacher Name">
				   <h5 class="mt-0 mb-0 teacher-name"><a href="teachers.php?tid=<?php echo $f['id'];?>"><?php echo $f['nice_name'];?></a></h5>
				   <p class="p-3 teacher-bio pt-0"><?php echo preg_replace('/\R/', "<br>", $f['bio']);?></p>
					   
<?php
	if (count($f['classes']) > 0) {
		echo "<p class='teacher-bio px-3'>Upcoming classes for {$f['nice_name']}:<ul>\n";
		foreach ($f['classes'] as $c) {
			echo "	<li class='teacher-bio'><a href=\"workshop.php?wid={$c['id']}\">{$c['title']}</a>, {$c['capacity']} people max, \${$c['costdisplay']} USD<br><div class='mx-4'>{$c['full_when']}</div></li>\n";
		}
		echo "</ul></p>\n";
	}					   
?>			   
		   </div>
	</div><!-- teacher -->	
	
<?php
}
?>

</div>

