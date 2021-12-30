<?php echo "<p>$links</p>"; ?>
			
<div class='row py-3 border-top'>
<div class='col-md-6'>Title</div>
<div class='col-md-6'>paid / enrolled / capacity</div>

</div>
				
<?php				

foreach ( $rows as $row ) {
	$public = '';
	if (strtotime($row['when_public']) > time()) { // if when_public is in the future, show it
		$public = "<br><small>Public: ".date('D M j - g:ia', strtotime($row['when_public']))."</small>\n";
	}	
						
	$cl = '';
	if (date('z', strtotime($row['start'])) == date('z')) { // today
		$cl .= 'info'; 
	} elseif ($row['soldout'] == 1) {
		$cl .= 'danger';
	} elseif ($row['upcoming'] == 1) {
		$cl .= 'success';
	} else { // past workshops
		$cl .= 'light';
	}

	echo "<div class='row workshop-$cl py-3 border-top'>\n"; // workshop row start
	
	echo "<div class='col-md-6'>".($row['soldout'] == 1 ? 'SOLD OUT: ' : '')."<a href='/admin-workshop/view/{$row['id']}'>{$row['title']}</a><br><small><a href=\"/admin-archives/clone/{$row['id']}#addworkshop\">(Clone)</a></small></div>"; // title cell
	
	echo "<div class='col-md-6'>\n"; // start of second column
	
	echo "{$row['when']} {$public}<br>
		".number_format($row['total_class_sessions'], 0)." class".\Wbhkit\plural($row['total_class_sessions'], '', 'es').", ".number_format($row['total_show_sessions'], 0)." show".\Wbhkit\plural($row['total_show_sessions'])."<br>\n";
			echo "Instructor: {$row['teacher_info']['nice_name']}";
			if ($row['co_teacher_id']) {
				echo ", {$row['co_teacher_info']['nice_name']}";
			}
			echo "<br>\n";	
			echo number_format($row['paid'])." / "
			.number_format($row['enrolled'], 0)." /  ".number_format($row['capacity'], 0)."\n"; // enrollments
		
		echo "</div>\n"; // end of big info cell wrapper
	echo "</div>\n"; // end of row
					
}
?>
<?php echo "<p>$links</p>"; ?>
