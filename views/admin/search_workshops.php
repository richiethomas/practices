<?php echo "<p>$links</p>"; ?>
			
<div class='row py-3 border-top'>
<div class='col-md-6'>Title</div>
<div class='col-md-6'>paid / enrolled / capacity</div>

</div>
				
<?php				

foreach ( $rows as $row ) {
	$public = '';
	if (strtotime($row->fields['when_public']) > time()) { // if when_public is in the future, show it
		$public = "<br><small>Public: ".date('D M j - g:ia', strtotime($row->fields['when_public']))."</small>\n";
	}	
						
	$cl = '';
	if (date('z', strtotime($row->fields['start'])) == date('z')) { // today
		$cl .= 'info'; 
	} elseif ($row->fields['soldout'] == 1) {
		$cl .= 'danger';
	} elseif (strtotime($row->fields['start'] > strtotime("now"))) {
		$cl .= 'success';
	} else { // past workshops
		$cl .= 'light';
	}

	echo "<div class='row workshop-$cl py-3 border-top'>\n"; // workshop row start
	
	echo "<div class='col-md-6'>".($row->fields['soldout'] == 1 ? 'SOLD OUT: ' : '')."<a href='/admin-workshop/view/{$row->fields['id']}'>{$row->fields['title']}</a><br><small><a href=\"/admin-archives/clone/{$row->fields['id']}#addworkshop\">(Clone)</a></small></div>"; // title cell
	
	echo "<div class='col-md-6'>\n"; // start of second column
	
	echo "{$row->fields['when']} {$public}<br>
		".number_format($row->fields['total_class_sessions'], 0)." class".\Wbhkit\plural($row->fields['total_class_sessions'], '', 'es').", ".number_format($row->fields['total_show_sessions'], 0)." show".\Wbhkit\plural($row->fields['total_show_sessions'])."<br>\n";
			echo "Instructor: {$row->fields['teacher_name']}<br>\n";	
			echo number_format($row->fields['paid'])." / "
			.number_format($row->fields['enrolled'], 0)." /  ".number_format($row->fields['capacity'], 0)."\n"; // enrollments
		
		echo "</div>\n"; // end of big info cell wrapper
	echo "</div>\n"; // end of row
					
}
?>
<?php echo "<p>$links</p>"; ?>
