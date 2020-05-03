<?php echo $links; ?>
				
<?php				
		foreach ( $rows as $row ) {
			$public = '';
			if ($admin && $row['when_public']) {
				$public = "<br><small>Public: ".date('D M j - g:ia', strtotime($row['when_public']))."</small>\n";
			}	
			
			$sessions = '';
			if (!empty($row['sessions'])) {
				$sessions .= "{$row['when']}";
				foreach ($row['sessions'] as $s) {
					$sessions .= "<br>\n{$s['friendly_when']}";
				}
				$row['when'] = $sessions; // replace the when variable 
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
		
			echo "<div class='row workshop-row workshop-$cl my-3 py-3 border-top'>\n"; // workshop row start
			
			echo "<div class='col-md-6'>".($row['soldout'] == 1 ? 'SOLD OUT: ' : '')."<a href='".($admin ? 'admin_edit.php' : 'workshop.php')."?wid={$row['id']}'>{$row['title']}</a></span>".($row['notes'] ? "<p class='small text-muted'>{$row['notes']}</p>" : '')."</div>"; // title cell
				
			echo "<div class='col-md-6'>\n"; // start of big crammed info cell wrapper
			echo "<div class='row row-cols-1'>\n"; // row within info cell
			
				echo "<div class='col my-2'>{$row['when']} (".TIMEZONE.") {$public}</div>\n"; // when col	
				if ($admin) { echo "<div class='col my-2'>{$row['place']}</div>\n"; } // where col
				echo "<div class='col my-2'>{$row['costdisplay']}</div>\n"; // cost cell
				echo "<div class='col my-2'>".number_format($row['enrolled'], 0)." of ".number_format($row['capacity'], 0)." filled,<br> ".number_format($row['waiting']+$row['invited'])." waiting</div>\n"; // enrollments
				
				echo "<div class='col my-2'>\n";
				if ($admin) {
					echo "<a href=\"admin_listall.php?wid={$row['id']}#addworkshop\">Clone</a>\n";
				} else {
					echo "<a class='btn btn-primary btn-sm' href=\"workshop.php?wid={$row['id']}\"><span class=\"oi oi-info\" title=\"info\" aria-hidden=\"true\"></span> Go to Sign Up Page</a>";
					if ($row['soldout'] == 1) {
						echo " to join waiting list";
					}
				}					
				echo "</div>\n"; // end of action col cel
				echo "</div>\n"; // end of big info cel row
				echo "</div>\n"; // end of big info cell wrapper
			echo "</div>\n"; // end of row
								

		}

?>
</tbody></table>
<?php echo $links; ?>