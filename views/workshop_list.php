<p><?php echo $links; ?></p>
				
<?php				
		foreach ( $rows as $row ) {
			
			// temporary
			//if ($row['soldout'] == 1) {
			//	continue;
			//}
			
			$row['when'] = \XtraSessions\add_sessions_to_when($row['when'], $row['sessions']);
					
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
		
			echo "<div class='row workshop-row workshop-$cl my-3 py-3 border-top'>\n"; // workshop row start
			
			echo "<div class='col-sm-9'>".($row['soldout'] == 1 ? 'SOLD OUT: ' : '')."<a href='workshop.php?wid={$row['id']}'>{$row['title']}</a></span>".
				($row['notes'] ? "<p class='small text-muted'>{$row['notes']}</p>" : '');

			
			echo "{$row['when']} (".TIMEZONE.")<br><br>\n"; // when col	
			echo "{$row['costdisplay']}<br>\n"; // cost cell
			echo number_format($row['enrolled'], 0)." of ".number_format($row['capacity'], 0)." filled,  ".number_format($row['waiting']+$row['invited'])." waiting<br>\n"; // enrollments
			
			echo "<p><a class='btn btn-primary btn-sm' href=\"workshop.php?wid={$row['id']}\"><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> Go to Sign Up Page</a>";
			if ($row['soldout'] == 1) {
				echo " to join waiting list";
			}
			echo "</p>";
			echo "</div>"; // title cell
				
			echo "<div class='col-sm-3'>\n"; // start of big crammed info cell wrapper
			if ($src = \Teachers\get_teacher_photo_src($row['teacher_user_id'])) {
				echo "<a href='teachers.php?tid={$row['teacher_id']}'><img class='img-fluid border' src='$src'></a>";
			}
			echo "<p><b>Teacher: <a href='teachers.php?tid={$row['teacher_id']}'>{$row['teacher_name']} (bio)</a></b></p>\n";
			
			echo "</div>\n"; // end of col
			
			echo "</div>\n"; // end of row
								

		}

?>
<p><?php echo $links; ?></p>