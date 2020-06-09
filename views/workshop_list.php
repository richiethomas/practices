<p><?php echo $links; ?></p>
				
<?php				
		foreach ( $rows as $row ) {
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
			
			echo "<div class='col-md-6'>".($row['soldout'] == 1 ? 'SOLD OUT: ' : '')."<a href='workshop.php?wid={$row['id']}'>{$row['title']}</a></span>".
				($row['notes'] ? "<p class='small text-muted'>{$row['notes']}</p>" : '').
					"</div>"; // title cell
				
			echo "<div class='col-md-6'>\n"; // start of big crammed info cell wrapper
				echo "<p><b>Teacher:{$row['teacher_name']}</b></p>\n";
			
				echo "{$row['when']} (".TIMEZONE.")<br><br>\n"; // when col	
				echo "{$row['costdisplay']}<br>\n"; // cost cell
				echo number_format($row['enrolled'], 0)." of ".number_format($row['capacity'], 0)." filled,  ".number_format($row['waiting']+$row['invited'])." waiting<br>\n"; // enrollments
				
				echo "<p><a class='btn btn-primary btn-sm' href=\"workshop.php?wid={$row['id']}\"><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> Go to Sign Up Page</a>";
				if ($row['soldout'] == 1) {
					echo " to join waiting list";
				}
				echo "</p>";
				
			echo "</div>\n"; // end of col
			echo "</div>\n"; // end of row
								

		}

?>
<p><?php echo $links; ?></p>